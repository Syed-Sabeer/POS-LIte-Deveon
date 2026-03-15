<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index()
    {
        $products = Product::orderBy('name')->paginate(20);

        return view('stock.index', compact('products'));
    }

    public function adjust(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'type' => ['required', 'in:add,remove'],
            'amount' => ['required', 'integer', 'min:1'],
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $current = (int) $product->quantity;
        $amount = (int) $validated['amount'];

        if ($validated['type'] === 'add') {
            $product->quantity = $current + $amount;
        } else {
            if ($amount > $current) {
                return back()->withErrors(['amount' => 'Cannot remove more than current stock.'])->withInput();
            }
            $product->quantity = $current - $amount;
        }

        $product->save();

        return back()->with('success', 'Stock updated successfully.');
    }
}
