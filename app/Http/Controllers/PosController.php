<?php

namespace App\Http\Controllers;

use App\Exports\DailySalesExport;
use App\Http\Requests\PosCheckoutRequest;
use App\Models\Account;
use App\Models\Customer;
use App\Models\PosOrder;
use App\Models\Product;
use App\Services\Accounting\PostingService;
use App\Support\FinanceNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class PosController extends Controller
{
    public function __construct(private readonly PostingService $postingService)
    {
    }

    public function index()
    {
        $products = Product::where('is_active', true)
            ->where('quantity', '>', 0)
            ->orderBy('name')
            ->get();

        $customers = Customer::orderBy('full_name')->get();

        return view('pos.index', compact('products', 'customers'));
    }

    public function checkout(PosCheckoutRequest $request)
    {
        $validated = $request->validated();

        $order = DB::transaction(function () use ($validated, $request) {
            $grossSubtotal = 0;
            $lineDiscountTotal = 0;
            $lines = [];

            foreach ($validated['items'] as $item) {
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);

                if ($product->quantity < $item['quantity']) {
                    throw ValidationException::withMessages([
                        'items' => 'Insufficient stock for ' . $product->name . '.',
                    ]);
                }

                $grossLine = (float) $product->selling_price * (int) $item['quantity'];
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
                    'unit_price' => (float) $product->selling_price,
                    'discount_amount' => $discount,
                    'line_total' => $lineTotal,
                ];

                $product->decrement('quantity', (int) $item['quantity']);
            }

            $customer = null;
            if (!empty($validated['customer_id'])) {
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
                'order_number' => FinanceNumber::next('SINV', PosOrder::class, 'order_number'),
                'user_id' => $request->user()?->id,
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
            $this->postingService->postSale($order->fresh('items.product'), $cashAccountId ? (int) $cashAccountId : null, $request->user()?->id);

            return $order;
        });

        return redirect()
            ->route('pos.orders.show', $order)
            ->with('success', 'Checkout completed successfully.');
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

    public function reports()
    {
        $reportDate = request('date', now()->toDateString());

        $dailyOrders = PosOrder::with(['customer', 'items'])
            ->whereDate('created_at', $reportDate)
            ->latest()
            ->get();

        $dailySales = $dailyOrders->sum('total');
        $weeklySales = PosOrder::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->sum('total');
        $monthlySales = PosOrder::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('total');

        $summary = [
            'total_orders' => $dailyOrders->count(),
            'total_items_sold' => $dailyOrders->sum(fn ($order) => $order->items->sum('quantity')),
            'total_discount' => $dailyOrders->sum(fn ($order) => $order->items->sum('discount_amount')),
            'total_earning' => $dailySales,
        ];

        return view('reports.sales', compact('dailySales', 'weeklySales', 'monthlySales', 'dailyOrders', 'summary', 'reportDate'));
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

        $dailyOrders = PosOrder::with(['customer', 'items'])
            ->whereDate('created_at', $reportDate)
            ->latest()
            ->get();

        $summary = [
            'total_orders' => $dailyOrders->count(),
            'total_items_sold' => $dailyOrders->sum(fn ($order) => $order->items->sum('quantity')),
            'total_discount' => $dailyOrders->sum(fn ($order) => $order->items->sum('discount_amount')),
            'total_earning' => $dailyOrders->sum('total'),
        ];

        $pdf = Pdf::loadView('reports.sales-pdf', compact('dailyOrders', 'summary', 'reportDate'));

        return $pdf->download('daily-sales-' . $reportDate . '.pdf');
    }
}
