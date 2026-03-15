@extends('layouts.app.master')

@section('title', 'Suppliers')

@section('content')
<div class="page-header">
    <div class="page-title"><h4 class="fw-bold">Suppliers</h4><h6>Manage supplier master data</h6></div>
    <div class="page-btn"><a href="{{ route('suppliers.create') }}" class="btn btn-primary">Add Supplier</a></div>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2">
            <div class="col-md-4"><input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search supplier"></div>
            <div class="col-md-2"><button class="btn btn-secondary w-100">Filter</button></div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0 table-responsive">
        <table class="table table-bordered mb-0 align-middle">
            <thead class="table-light"><tr><th>Name</th><th>Company</th><th>Phone</th><th>Opening</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
            @forelse($suppliers as $supplier)
                <tr>
                    <td>{{ $supplier->full_name }}</td>
                    <td>{{ $supplier->company_name ?: '-' }}</td>
                    <td>{{ $supplier->phone ?: '-' }}</td>
                    <td>{{ strtoupper($supplier->balance_type) }} PKR {{ number_format($supplier->opening_balance,2) }}</td>
                    <td><span class="badge bg-{{ $supplier->is_active ? 'success' : 'danger' }}">{{ $supplier->is_active ? 'Active' : 'Inactive' }}</span></td>
                    <td>
                        <a href="{{ route('suppliers.show', $supplier) }}" class="btn btn-sm btn-info">View</a>
                        <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}" class="d-inline" onsubmit="return confirm('Delete supplier?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center">No suppliers found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $suppliers->links() }}</div>
</div>
@endsection
