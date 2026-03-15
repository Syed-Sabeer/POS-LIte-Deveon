@extends('layouts.app.master')

@section('title', 'Product Details')

@section('content')
<div class="container mt-4">
    <h2>Product Details</h2>
    <a href="{{ route('products.index') }}" class="btn btn-secondary mb-3">Back to Products</a>

    <div class="card p-4">
        <div class="row">
            <div class="col-md-4">
                @if($product->image && Storage::disk('public')->exists($product->image))
                    <img src="{{ asset('storage/' . $product->image) }}" alt="Image" class="img-fluid rounded">
                @else
                    <img src="{{ asset('images/placeholder.png') }}" alt="No Image" class="img-fluid rounded">
                @endif
            </div>
            <div class="col-md-8">
                <h4>{{ $product->name }}</h4>
                <p><strong>SKU:</strong> {{ $product->sku }}</p>
                <p><strong>Cost Price:</strong> ${{ number_format($product->cost_price, 2) }}</p>
                <p><strong>Selling Price:</strong> ${{ number_format($product->selling_price, 2) }}</p>
                <p><strong>Quantity:</strong> {{ $product->quantity }}</p>
                <p><strong>Status:</strong> {{ $product->is_active ? 'Active' : 'Inactive' }}</p>
                <p><strong>Created At:</strong> {{ $product->created_at->format('Y-m-d H:i') }}</p>
                <p><strong>Updated At:</strong> {{ $product->updated_at->format('Y-m-d H:i') }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
