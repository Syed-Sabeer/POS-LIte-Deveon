<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\PosOrder;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PosController extends Controller
{
    public function index()
    {
        $products = Product::where('is_active', true)
            ->where('quantity', '>', 0)
            ->orderBy('name')
            ->get();

        $customers = Customer::orderBy('full_name')->get();

        return view('pos.index', compact('products', 'customers'));
    }

    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'payment_method' => ['required', 'in:cash,card,upi'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $order = DB::transaction(function () use ($validated, $request) {
            $subtotal = 0;
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
                $subtotal += $lineTotal;

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

            $order = PosOrder::create([
                'order_number' => 'POS-' . now()->format('YmdHis') . '-' . random_int(100, 999),
                'user_id' => $request->user()?->id,
                'customer_id' => $customer?->id,
                'customer_name' => $customer?->full_name ?: ($validated['customer_name'] ?: 'Walk in Customer'),
                'payment_method' => $validated['payment_method'],
                'subtotal' => $subtotal,
                'total' => $subtotal,
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
        $dailySales = PosOrder::whereDate('created_at', today())->sum('total');
        $weeklySales = PosOrder::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->sum('total');
        $monthlySales = PosOrder::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('total');

        return view('reports.sales', compact('dailySales', 'weeklySales', 'monthlySales'));
    }
}
