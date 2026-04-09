<?php

namespace App\Http\Controllers;

use App\Exports\DailySalesExport;
use App\Http\Requests\PosCheckoutRequest;
use App\Models\Account;
use App\Models\Customer;
use App\Models\PosOrder;
use App\Models\Product;
use App\Services\Accounting\PostingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Carbon;

class PosController extends Controller
{
    public function __construct(private readonly PostingService $postingService)
    {
    }

    public function index()
    {
        $products = Product::where('is_active', true)
            ->orderBy('name')
            ->get();

        $customers = Customer::orderBy('full_name')->get();

        $refundOrder = null;
        $refundOrderData = null;
        $refundAdditionalDiscount = 0.0;
        $refundOrderId = request()->integer('refund_order');
        if ($refundOrderId) {
            $refundOrder = PosOrder::with(['items', 'customer'])->findOrFail($refundOrderId);
            $lineDiscountTotal = (float) $refundOrder->items->sum('discount_amount');
            $refundAdditionalDiscount = max(0, (float) $refundOrder->discount_amount - $lineDiscountTotal);

            $refundOrderData = [
                'id' => $refundOrder->id,
                'order_number' => $refundOrder->order_number,
                'customer_id' => $refundOrder->customer_id,
                'customer_name' => $refundOrder->customer_name,
                'payment_method' => $refundOrder->payment_method,
                'invoice_date' => optional($refundOrder->invoice_date)->toDateString(),
                'paid_amount' => (float) ($refundOrder->received_amount ?? $refundOrder->paid_amount),
                'items' => $refundOrder->items->map(function ($item) {
                    return [
                        'product_id' => $item->product_id,
                        'product_name' => $item->product_name,
                        'unit_price' => (float) $item->unit_price,
                        'quantity' => (int) $item->quantity,
                        'discount_amount' => (float) $item->discount_amount,
                    ];
                })->values()->all(),
            ];
        }

        return view('pos.index', compact('products', 'customers', 'refundOrder', 'refundOrderData', 'refundAdditionalDiscount'));
    }

    public function checkout(PosCheckoutRequest $request)
    {
        $validated = $request->validated();
        $order = $this->processCheckout($validated, $request->user()?->id);

        return redirect()
            ->route('pos.orders.show', $order)
            ->with('success', 'Checkout completed successfully.');
    }

    public function checkoutSync(PosCheckoutRequest $request)
    {
        $validated = $request->validated();
        $order = $this->processCheckout($validated, $request->user()?->id);

        return response()->json([
            'ok' => true,
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'message' => 'Checkout completed successfully.',
        ]);
    }

    private function processCheckout(array $validated, ?int $userId): PosOrder
    {
        return DB::transaction(function () use ($validated, $userId) {
            $grossSubtotal = 0;
            $lineDiscountTotal = 0;
            $lines = [];

            foreach ($validated['items'] as $item) {
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);
                $unitPrice = array_key_exists('unit_price', $item)
                    ? max(0, (float) $item['unit_price'])
                    : (float) $product->selling_price;

                $grossLine = $unitPrice * (int) $item['quantity'];
                $discount = isset($item['discount']) ? (float) $item['discount'] : 0;
                if ($discount > $grossLine) {
                    $discount = $grossLine;
                }
                $lineTotal = $grossLine - $discount;
                $grossSubtotal += $grossLine;
                $lineDiscountTotal += $discount;

                $lines[] = [
                    'product' => $product,
                    'quantity' => (int) $item['quantity'],
                    'unit_price' => $unitPrice,
                    'cost_price' => (float) $product->cost_price,
                    'discount_amount' => $discount,
                    'line_total' => $lineTotal,
                ];

                $product->decrement('quantity', (int) $item['quantity']);
            }

            $customer = null;
            if (! empty($validated['customer_id'])) {
                $customer = Customer::find($validated['customer_id']);
            }

            $additionalDiscount = (float) ($validated['additional_discount'] ?? 0);
            $taxAmount = 0;
            $discountAmount = min($grossSubtotal, $lineDiscountTotal + $additionalDiscount);
            $total = max(0, ($grossSubtotal - $discountAmount) + $taxAmount);

            $receivedAmount = max(0, (float) ($validated['paid_amount'] ?? 0));
            $paymentMethod = $validated['payment_method'];

            if (empty($customer) && $paymentMethod === 'pay_later') {
                throw ValidationException::withMessages([
                    'payment_method' => 'Walk in customer cannot use Pay Later.',
                ]);
            }

            if ($paymentMethod === 'pay_later') {
                $receivedAmount = 0;
                $paidAmount = 0;
                $changeAmount = 0;
                $dueAmount = $total;
            } else {
                $paidAmount = min($receivedAmount, $total);
                $changeAmount = max(0, $receivedAmount - $total);
                $dueAmount = max(0, $total - $paidAmount);
            }

            if ($dueAmount > 0 && empty($customer)) {
                throw ValidationException::withMessages([
                    'paid_amount' => 'Walk in customer must pay full amount. Select a customer to keep pending amount.',
                ]);
            }

            $paymentStatus = $dueAmount <= 0
                ? PosOrder::PAYMENT_STATUS_PAID
                : ($paidAmount > 0 ? PosOrder::PAYMENT_STATUS_PARTIAL : PosOrder::PAYMENT_STATUS_UNPAID);

            $order = PosOrder::create([
                'order_number' => $this->generateRandomOrderNumber(),
                'user_id' => $userId,
                'customer_id' => $customer?->id,
                'customer_name' => $customer?->full_name ?: ($validated['customer_name'] ?: 'Walk in Customer'),
                'payment_method' => $validated['payment_method'],
                'invoice_date' => $validated['invoice_date'],
                'subtotal' => $grossSubtotal,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'total_amount' => $total,
                'paid_amount' => $paidAmount,
                'received_amount' => $receivedAmount,
                'change_amount' => $changeAmount,
                'due_amount' => $dueAmount,
                'payment_status' => $paymentStatus,
                'status' => 'completed',
                'posting_status' => 'posted',
                'notes' => null,
            ]);

            foreach ($lines as $line) {
                $order->items()->create([
                    'product_id' => $line['product']->id,
                    'product_name' => $line['product']->name,
                    'unit_price' => $line['unit_price'],
                    'cost_price' => $line['cost_price'],
                    'quantity' => $line['quantity'],
                    'discount_amount' => $line['discount_amount'],
                    'line_total' => $line['line_total'],
                ]);
            }

            $cashAccountId = null;
            if ($paymentMethod === 'cash') {
                $cashAccountId = Account::where('code', Account::CODE_CASH)->value('id');
            } elseif ($paymentMethod === 'cheque') {
                $cashAccountId = Account::where('code', Account::CODE_BANK)->value('id');
            }
            $this->postingService->postSale($order->fresh('items.product'), $cashAccountId ? (int) $cashAccountId : null, $userId);

            return $order;
        });
    }

    public function orders()
    {
        $orders = PosOrder::with('customer')->withCount('items')
            ->latest()
            ->paginate(15);

        return view('pos.orders', compact('orders'));
    }

    public function show(PosOrder $order)
    {
        $order->load('customer', 'items.product');

        return view('pos.receipt', compact('order'));
    }

    public function edit(PosOrder $order)
    {
        $order->load('items.product', 'customer');
        $customers = Customer::orderBy('full_name')->get();

        return view('pos.edit', compact('order', 'customers'));
    }

    public function update(Request $request, PosOrder $order)
    {
        $validated = $request->validate([
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'payment_method' => ['required', 'in:cash,cheque,pay_later'],
            'invoice_date' => ['required', 'date'],
            'additional_discount' => ['nullable', 'numeric', 'min:0'],
            'paid_amount' => ['required', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($validated, $request, $order) {
            $order->load('items');

            foreach ($order->items as $oldItem) {
                Product::whereKey($oldItem->product_id)->increment('quantity', (int) $oldItem->quantity);
            }

            $grossSubtotal = 0;
            $lineDiscountTotal = 0;
            $lines = [];

            foreach ($validated['items'] as $item) {
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);
                $quantity = (int) $item['quantity'];
                $unitPrice = max(0, (float) $item['unit_price']);

                $grossLine = $unitPrice * $quantity;
                $discount = isset($item['discount']) ? (float) $item['discount'] : 0;
                if ($discount > $grossLine) {
                    $discount = $grossLine;
                }

                $lineTotal = $grossLine - $discount;
                $grossSubtotal += $grossLine;
                $lineDiscountTotal += $discount;

                $lines[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'unit_price' => $unitPrice,
                    'cost_price' => (float) $product->cost_price,
                    'quantity' => $quantity,
                    'discount_amount' => $discount,
                    'line_total' => $lineTotal,
                ];

                $product->decrement('quantity', $quantity);
            }

            $customer = null;
            if (! empty($validated['customer_id'])) {
                $customer = Customer::find($validated['customer_id']);
            }

            $additionalDiscount = (float) ($validated['additional_discount'] ?? 0);
            $taxAmount = 0;
            $discountAmount = min($grossSubtotal, $lineDiscountTotal + $additionalDiscount);
            $total = max(0, ($grossSubtotal - $discountAmount) + $taxAmount);

            $receivedAmount = max(0, (float) ($validated['paid_amount'] ?? 0));
            $paymentMethod = $validated['payment_method'];

            if (empty($customer) && $paymentMethod === 'pay_later') {
                throw ValidationException::withMessages([
                    'payment_method' => 'Walk in customer cannot use Pay Later.',
                ]);
            }

            if ($paymentMethod === 'pay_later') {
                $receivedAmount = 0;
                $paidAmount = 0;
                $changeAmount = 0;
                $dueAmount = $total;
            } else {
                $paidAmount = min($receivedAmount, $total);
                $changeAmount = max(0, $receivedAmount - $total);
                $dueAmount = max(0, $total - $paidAmount);
            }

            if ($dueAmount > 0 && empty($customer)) {
                throw ValidationException::withMessages([
                    'paid_amount' => 'Walk in customer must pay full amount. Select a customer to keep pending amount.',
                ]);
            }

            $paymentStatus = $dueAmount <= 0
                ? PosOrder::PAYMENT_STATUS_PAID
                : ($paidAmount > 0 ? PosOrder::PAYMENT_STATUS_PARTIAL : PosOrder::PAYMENT_STATUS_UNPAID);

            $order->update([
                'user_id' => $request->user()?->id,
                'customer_id' => $customer?->id,
                'customer_name' => $customer?->full_name ?: ($validated['customer_name'] ?: 'Walk in Customer'),
                'payment_method' => $paymentMethod,
                'invoice_date' => $validated['invoice_date'],
                'subtotal' => $grossSubtotal,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'total_amount' => $total,
                'paid_amount' => $paidAmount,
                'received_amount' => $receivedAmount,
                'change_amount' => $changeAmount,
                'due_amount' => $dueAmount,
                'payment_status' => $paymentStatus,
                'status' => 'completed',
            ]);

            $order->items()->delete();
            foreach ($lines as $line) {
                $order->items()->create($line);
            }
        });

        return redirect()->route('pos.orders.show', $order)->with('success', 'Sale updated successfully.');
    }

    public function destroy(PosOrder $order)
    {
        DB::transaction(function () use ($order) {
            $order->load('items');

            foreach ($order->items as $item) {
                Product::whereKey($item->product_id)->increment('quantity', (int) $item->quantity);
            }

            $order->delete();
        });

        return redirect()->route('pos.orders')->with('success', 'Sale cancelled and deleted successfully.');
    }

    private function generateRandomOrderNumber(): string
    {
        do {
            $number = (string) random_int(1000000, 9999999);
        } while (PosOrder::where('order_number', $number)->exists());

        return $number;
    }

    public function reports()
    {
        $reportDate = request('date', now()->toDateString());

        $reportData = $this->buildSalesReportData($reportDate);

        return view('reports.sales', $reportData + ['reportDate' => $reportDate]);
    }

    public function exportDailySalesExcel(Request $request)
    {
        $reportDate = $request->input('date', now()->toDateString());
        $filename = 'daily-sales-' . $reportDate . '.xlsx';

        return Excel::download(new DailySalesExport($reportDate), $filename);
    }

    public function exportDailySalesPdf(Request $request)
    {
        $reportDate = $request->input('date', now()->toDateString());

        $reportData = $this->buildSalesReportData($reportDate);

        $pdf = Pdf::loadView('reports.sales-pdf', $reportData + ['reportDate' => $reportDate]);

        return $pdf->download('daily-sales-' . $reportDate . '.pdf');
    }

    private function buildSalesReportData(string $reportDate): array
    {
        $dailyOrders = PosOrder::with(['customer', 'items.product'])
            ->whereDate('invoice_date', $reportDate)
            ->latest()
            ->get();

        $weeklySales = PosOrder::whereBetween('invoice_date', [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()])->sum('total');
        $monthlySales = PosOrder::whereYear('invoice_date', now()->year)
            ->whereMonth('invoice_date', now()->month)
            ->sum('total');

        $periodProfiles = [
            'daily' => $this->buildPeriodProfile(Carbon::parse($reportDate)->startOfDay(), Carbon::parse($reportDate)->endOfDay()),
            'weekly' => $this->buildPeriodProfile(now()->startOfWeek(), now()->endOfWeek()),
            'monthly' => $this->buildPeriodProfile(now()->startOfMonth(), now()->endOfMonth()),
        ];

        $itemWise = [];
        $totals = [
            'sales_amount' => 0.0,
            'received_amount' => 0.0,
            'due_amount' => 0.0,
            'cost_amount' => 0.0,
            'profit_amount' => 0.0,
            'loss_amount' => 0.0,
            'discount_amount' => 0.0,
            'quantity_sold' => 0,
        ];

        foreach ($dailyOrders as $order) {
            $orderTotal = (float) $order->total;
            $orderReceived = min($orderTotal, max(0, (float) $order->paid_amount));
            $orderDue = max(0, (float) $order->due_amount);

            foreach ($order->items as $item) {
                $lineSales = (float) $item->line_total;
                $lineQty = (int) $item->quantity;
                $lineDiscount = (float) $item->discount_amount;
                $unitCost = (float) ($item->cost_price ?? $item->product?->cost_price ?? 0);
                $lineCost = $unitCost * $lineQty;
                $lineProfit = $lineSales - $lineCost;

                $lineDue = $orderTotal > 0 ? ($lineSales / $orderTotal) * $orderDue : 0;
                $lineReceived = $orderTotal > 0 ? ($lineSales / $orderTotal) * $orderReceived : 0;

                $itemKey = ($item->product_id ?: 'name') . '|' . strtolower($item->product_name);
                if (!isset($itemWise[$itemKey])) {
                    $itemWise[$itemKey] = [
                        'product_name' => $item->product_name,
                        'quantity_sold' => 0,
                        'sales_amount' => 0.0,
                        'earning_amount' => 0.0,
                        'due_amount' => 0.0,
                        'cost_amount' => 0.0,
                        'profit_or_loss' => 0.0,
                        'discount_amount' => 0.0,
                    ];
                }

                $itemWise[$itemKey]['quantity_sold'] += $lineQty;
                $itemWise[$itemKey]['sales_amount'] += $lineSales;
                $itemWise[$itemKey]['earning_amount'] += $lineReceived;
                $itemWise[$itemKey]['due_amount'] += $lineDue;
                $itemWise[$itemKey]['cost_amount'] += $lineCost;
                $itemWise[$itemKey]['profit_or_loss'] += $lineProfit;
                $itemWise[$itemKey]['discount_amount'] += $lineDiscount;

                $totals['quantity_sold'] += $lineQty;
                $totals['sales_amount'] += $lineSales;
                $totals['received_amount'] += $lineReceived;
                $totals['due_amount'] += $lineDue;
                $totals['cost_amount'] += $lineCost;
                $totals['discount_amount'] += $lineDiscount;

                if ($lineProfit >= 0) {
                    $totals['profit_amount'] += $lineProfit;
                } else {
                    $totals['loss_amount'] += abs($lineProfit);
                }
            }
        }

        $itemWiseSales = collect(array_values($itemWise))->sortByDesc('sales_amount')->values();

        $summary = [
            'total_orders' => $dailyOrders->count(),
            'total_items_sold' => $totals['quantity_sold'],
            'total_discount' => $totals['discount_amount'],
            'total_sales_amount' => $totals['sales_amount'],
            'total_earning' => $totals['received_amount'],
            'total_due' => $totals['due_amount'],
            'total_cost' => $totals['cost_amount'],
            'total_profit' => $totals['profit_amount'],
            'total_loss' => $totals['loss_amount'],
            'net_profit_loss' => $totals['sales_amount'] - $totals['cost_amount'],
        ];

        return [
            'dailySales' => $summary['total_sales_amount'],
            'weeklySales' => $weeklySales,
            'monthlySales' => $monthlySales,
            'dailyOrders' => $dailyOrders,
            'itemWiseSales' => $itemWiseSales,
            'summary' => $summary,
            'periodProfiles' => $periodProfiles,
        ];
    }

    private function buildPeriodProfile(Carbon $start, Carbon $end): array
    {
        $orders = PosOrder::with('items')
            ->whereBetween('invoice_date', [$start->toDateString(), $end->toDateString()])
            ->get();

        $sales = (float) $orders->sum('total');
        $received = (float) $orders->sum('paid_amount');
        $due = (float) $orders->sum('due_amount');
        $discount = (float) $orders->sum('discount_amount');
        $itemsSold = (int) $orders->sum(fn ($order) => (int) $order->items->sum('quantity'));
        $cost = (float) $orders->sum(function ($order) {
            return $order->items->sum(function ($item) {
                return (float) ($item->cost_price ?? 0) * (int) $item->quantity;
            });
        });

        $net = $sales - $cost;

        return [
            'orders' => $orders->count(),
            'items_sold' => $itemsSold,
            'sales_amount' => $sales,
            'received_amount' => $received,
            'due_amount' => $due,
            'discount_amount' => $discount,
            'cost_amount' => $cost,
            'profit_amount' => $net > 0 ? $net : 0.0,
            'loss_amount' => $net < 0 ? abs($net) : 0.0,
            'net_profit_loss' => $net,
        ];
    }
}
