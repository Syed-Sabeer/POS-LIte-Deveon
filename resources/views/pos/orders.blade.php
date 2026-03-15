@extends('layouts.app.master')

@section('title', 'POS Orders')

@section('content')
<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">POS Orders</h4>
            <h6>Order history and receipts</h6>
        </div>
    </div>
    <div class="page-btn mt-0">
        <a href="{{ route('pos.index') }}" class="btn btn-primary"><i class="ti ti-device-laptop me-1"></i>Back to POS</a>
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
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Items</th>
                        <th>Paid</th>
                        <th>Due</th>
                        <th>Total</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td>{{ $order->order_number }}</td>
                            <td>{{ $order->customer?->full_name ?: $order->customer_name }}</td>
                            <td>{{ strtoupper($order->payment_method) }}</td>
                            <td><span class="badge bg-{{ $order->payment_status === 'paid' ? 'success' : ($order->payment_status === 'partial' ? 'warning' : 'danger') }}">{{ strtoupper($order->payment_status) }}</span></td>
                            <td>{{ $order->items_count }}</td>
                            <td>PKR {{ number_format($order->paid_amount, 2) }}</td>
                            <td>PKR {{ number_format($order->due_amount, 2) }}</td>
                            <td>PKR {{ number_format($order->total, 2) }}</td>
                            <td>{{ $order->created_at->format('Y-m-d H:i') }}</td>
                            <td>
                                <a href="{{ route('pos.orders.show', $order) }}" class="btn btn-sm btn-info">View Receipt</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center">No orders found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">
            {{ $orders->links() }}
        </div>
    </div>
</div>
@endsection
