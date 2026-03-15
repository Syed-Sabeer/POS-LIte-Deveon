@extends('layouts.app.master')

@section('title', 'Customers')

@section('content')
<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">Customers</h4>
            <h6>Manage POS customers</h6>
        </div>
    </div>
    <div class="page-btn mt-0">
        <a href="{{ route('pos.index') }}" class="btn btn-secondary"><i class="ti ti-device-laptop me-1"></i>Back to POS</a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card mb-3">
    <div class="card-header">
        <h6 class="mb-0">Add Customer</h6>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('customers.store') }}" class="row g-3">
            @csrf
            <div class="col-md-4">
                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                <input type="text" name="full_name" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Company Name</label>
                <input type="text" name="company_name" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control">
            </div>
            <div class="col-md-8">
                <label class="form-label">Address</label>
                <input type="text" name="address" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">Opening Balance</label>
                <input type="number" step="0.01" min="0" name="opening_balance" class="form-control" value="0">
            </div>
            <div class="col-md-3">
                <label class="form-label">Balance Type</label>
                <select name="balance_type" class="form-control">
                    <option value="dr">DR</option>
                    <option value="cr">CR</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_active" id="customerActive" value="1" checked>
                    <label class="form-check-label" for="customerActive">Active</label>
                </div>
            </div>
            <div class="col-12 text-end">
                <button class="btn btn-primary" type="submit">Save Customer</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Company</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Address</th>
                        <th>Opening</th>
                        <th>Status</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                        <tr>
                            <td>{{ $customer->full_name }}</td>
                            <td>{{ $customer->company_name ?: '-' }}</td>
                            <td>{{ $customer->phone ?: '-' }}</td>
                            <td>{{ $customer->email ?: '-' }}</td>
                            <td>{{ $customer->address ?: '-' }}</td>
                            <td>{{ strtoupper($customer->balance_type) }} PKR {{ number_format($customer->opening_balance ?? 0, 2) }}</td>
                            <td>{{ $customer->is_active ? 'Active' : 'Inactive' }}</td>
                            <td>{{ $customer->created_at->format('Y-m-d') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">No customers found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">{{ $customers->links() }}</div>
    </div>
</div>
@endsection
