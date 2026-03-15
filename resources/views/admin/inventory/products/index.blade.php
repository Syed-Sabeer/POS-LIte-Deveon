@extends('layouts.app.master')

@section('title', 'Products')

@section('content')
<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">Product List</h4>
            <h6>Manage your products</h6>
        </div>
    </div>
    <ul class="table-top-head">
        <li>
            <a data-bs-toggle="tooltip" data-bs-placement="top" title="Refresh"><i class="ti ti-refresh"></i></a>
        </li>
        <li>
            <a data-bs-toggle="tooltip" data-bs-placement="top" title="Collapse" id="collapse-header"><i class="ti ti-chevron-up"></i></a>
        </li>
    </ul>
    <div class="page-btn mt-0">
        <a href="{{ route('admin.products.create') }}" class="btn btn-primary"><i class="ti ti-circle-plus me-1"></i>Add Product</a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card mt-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Cost Price</th>
                        <th>Selling Price</th>
                        <th>Stock</th>
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
                        <td>PKR {{ number_format($product->cost_price, 2) }}</td>
                        <td>PKR {{ number_format($product->selling_price, 2) }}</td>
                        <td>{{ $product->quantity }} {{ $product->unit ?? 'pcs' }}</td>
                        <td>
                            <a href="{{ route('admin.products.show', $product) }}" class="btn btn-info btn-sm">View</a>
                            <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-warning btn-sm">Edit</a>
                            <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">No products found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">
            {{ $products->links() }}
        </div>
    </div>
</div>
@endsection
