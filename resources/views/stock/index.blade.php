@extends('layouts.app.master')

@section('title', 'Stock Maintenance')

@section('content')
<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">Stock Maintenance</h4>
            <h6>Increase or decrease product stock</h6>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Product</th>
                        <th>Current Stock</th>
                        <th>Adjustment</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->quantity }} {{ $product->unit ?? 'pcs' }}</td>
                            <td>
                                <form method="POST" action="{{ route('stock.adjust') }}" class="d-flex gap-2 align-items-center flex-wrap">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <select name="type" class="form-control" style="max-width: 120px" required>
                                        <option value="add">Add</option>
                                        <option value="remove">Remove</option>
                                    </select>
                                    <input type="number" name="amount" class="form-control" min="1" step="1" placeholder="Amount" style="max-width: 120px" required>
                                    <button type="submit" class="btn btn-primary btn-sm">Update</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center">No products found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">{{ $products->links() }}</div>
    </div>
</div>
@endsection
