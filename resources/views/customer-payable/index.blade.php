@extends('layouts.app.master')

@section('title', 'Customer Payable')

@section('content')
<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">Customer Payable</h4>
            <h6>Manage customer pending amounts</h6>
        </div>
    </div>
    <div class="page-btn mt-0">
        <a href="{{ route('customers.index') }}" class="btn btn-secondary"><i class="ti ti-arrow-left me-1"></i>Back to Customers</a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card">
    <div class="card-header">
        <h6 class="mb-0">Outstanding Balances</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Customer Name</th>
                        <th>Company</th>
                        <th>Phone</th>
                        <th class="text-end">Pending Amount</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                        <tr>
                            <td>{{ $customer->full_name }}</td>
                            <td>{{ $customer->company_name ?: '-' }}</td>
                            <td>{{ $customer->phone ?: '-' }}</td>
                            <td class="text-end">
                                <span class="badge bg-warning">PKR {{ number_format($customer->getPendingAmount(), 2) }}</span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('customer-payable.create', $customer) }}" class="btn btn-sm btn-primary">
                                    <i class="ti ti-cash me-1"></i>Receive Payment
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">No customers with pending amounts.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted mb-2">Total Outstanding</h6>
                <h3 class="text-warning mb-0">PKR {{ number_format($customers->sum(fn ($c) => $c->getPendingAmount()), 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted mb-2">Customers with Due</h6>
                <h3 class="text-info mb-0">{{ $customers->count() }}</h3>
            </div>
        </div>
    </div>
</div>
@endsection
