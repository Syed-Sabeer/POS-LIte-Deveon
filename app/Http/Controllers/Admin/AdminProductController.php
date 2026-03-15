<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use Illuminate\Support\Facades\Storage;

class AdminProductController extends Controller
{
	public function index()
	{
		$products = Product::orderByDesc('id')->paginate(10);
		return view('admin.inventory.products.index', compact('products'));
	}

	public function create()
	{
		return view('admin.inventory.products.create');
	}

	public function store(StoreProductRequest $request)
	{
		$data = $request->validated();
		if ($request->hasFile('image')) {
			$data['image'] = $request->file('image')->store('products', 'public');
		}
		Product::create($data);
		return redirect()->route('admin.products.index')->with('success', 'Product created successfully.');
	}

	public function show(Product $product)
	{
		return view('admin.inventory.products.show', compact('product'));
	}

	public function edit(Product $product)
	{
		return view('admin.inventory.products.edit', compact('product'));
	}

	public function update(UpdateProductRequest $request, Product $product)
	{
		$data = $request->validated();
		if ($request->hasFile('image')) {
			if ($product->image && Storage::disk('public')->exists($product->image)) {
				Storage::disk('public')->delete($product->image);
			}
			$data['image'] = $request->file('image')->store('products', 'public');
		}
		$product->update($data);
		return redirect()->route('admin.products.index')->with('success', 'Product updated successfully.');
	}

	public function destroy(Product $product)
	{
		if ($product->image && Storage::disk('public')->exists($product->image)) {
			Storage::disk('public')->delete($product->image);
		}
		$product->delete();
		return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully.');
	}
}
