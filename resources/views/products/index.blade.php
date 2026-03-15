@extends('layouts.app.master')

@section('title', 'Products')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Products</h2>
        <a href="{{ route('products.create') }}" class="btn btn-primary">Add Product</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered table-striped align-middle">
        <thead class="table-light">
            <tr>
                <th>Image</th>
                <th>Name</th>
                <th>SKU</th>
                <th>Cost Price</th>
                <th>Selling Price</th>
                <th>Quantity</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        @forelse($products as $product)
            <tr>
                <td>
                    @if($product->image && Storage::disk('public')->exists($product->image))
                        <img src="{{ asset('storage/' . $product->image) }}" alt="Image" width="60" height="60" class="rounded">
                    @else
                        <img src="{{ asset('images/placeholder.png') }}" alt="No Image" width="60" height="60" class="rounded">
                    @endif
                </td>
                <td>{{ $product->name }}</td>
                <td>{{ $product->sku }}</td>
                <td>${{ number_format($product->cost_price, 2) }}</td>
                <td>${{ number_format($product->selling_price, 2) }}</td>
                <td>{{ $product->quantity }}</td>
                <td>
                    <a href="{{ route('products.show', $product) }}" class="btn btn-info btn-sm">View</a>
                    <a href="{{ route('products.edit', $product) }}" class="btn btn-warning btn-sm">Edit</a>
                    <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline"
                        onsubmit="return confirm('Are you sure you want to delete this product?');">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center">No products found.</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    <div>
        {{ $products->links() }}
    </div>
</div>
@endsection
