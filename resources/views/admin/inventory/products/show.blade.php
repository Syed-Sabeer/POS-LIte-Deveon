
@extends('layouts.app.master')

@section('title', 'Product Details')

@section('content')
<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">Product Details</h4>
            <h6>View product information</h6>
        </div>
    </div>
    <div class="page-btn mt-0">
        <a href="{{ route('admin.products.index') }}" class="btn btn-secondary"><i data-feather="arrow-left" class="me-2"></i>Back to Products</a>
    </div>
</div>

<div class="card mt-3">
    <div class="card-body">
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
                <p><strong>Cost Price:</strong> PKR {{ number_format($product->cost_price, 2) }}</p>
                <p><strong>Selling Price:</strong> PKR {{ number_format($product->selling_price, 2) }}</p>
                <p><strong>Stock:</strong> {{ $product->quantity }} {{ $product->unit ?? 'pcs' }}</p>
                <p><strong>Status:</strong> {{ $product->is_active ? 'Active' : 'Inactive' }}</p>
                <p><strong>Created At:</strong> {{ $product->created_at->format('Y-m-d H:i') }}</p>
                <p><strong>Updated At:</strong> {{ $product->updated_at->format('Y-m-d H:i') }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
