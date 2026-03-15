@extends('layouts.app.master')

@section('title', 'Sales Reports')

@section('content')
<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">Sales Reports</h4>
            <h6>Daily, weekly, and monthly totals with export</h6>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('reports.sales.export.excel', ['date' => $reportDate]) }}" class="btn btn-success">
            <i class="ti ti-file-spreadsheet me-1"></i>Export Excel
        </a>
        <a href="{{ route('reports.sales.export.pdf', ['date' => $reportDate]) }}" class="btn btn-danger">
            <i class="ti ti-file-type-pdf me-1"></i>Export PDF
        </a>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('reports.sales') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Report Date</label>
                <input type="date" name="date" class="form-control" value="{{ $reportDate }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Apply</button>
            </div>
        </form>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted">Daily Sales</h6>
                <h3 class="mb-0">PKR {{ number_format($dailySales, 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted">Weekly Sales</h6>
                <h3 class="mb-0">PKR {{ number_format($weeklySales, 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted">Monthly Sales</h6>
                <h3 class="mb-0">PKR {{ number_format($monthlySales, 2) }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-md-3">
        <div class="card"><div class="card-body"><p class="mb-1 text-muted">Total Orders</p><h4 class="mb-0">{{ $summary['total_orders'] }}</h4></div></div>
    </div>
    <div class="col-md-3">
        <div class="card"><div class="card-body"><p class="mb-1 text-muted">Items Sold</p><h4 class="mb-0">{{ $summary['total_items_sold'] }}</h4></div></div>
    </div>
    <div class="col-md-3">
        <div class="card"><div class="card-body"><p class="mb-1 text-muted">Total Discount</p><h4 class="mb-0">PKR {{ number_format($summary['total_discount'], 2) }}</h4></div></div>
    </div>
    <div class="col-md-3">
        <div class="card"><div class="card-body"><p class="mb-1 text-muted">Total Earning</p><h4 class="mb-0">PKR {{ number_format($summary['total_earning'], 2) }}</h4></div></div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header">
        <h6 class="mb-0">Daily Sales Details ({{ $reportDate }})</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Payment</th>
                        <th>Items</th>
                        <th>Discount</th>
                        <th>Total</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dailyOrders as $order)
                        <tr>
                            <td>{{ $order->order_number }}</td>
                            <td>{{ $order->customer?->full_name ?: $order->customer_name }}</td>
                            <td>{{ strtoupper($order->payment_method) }}</td>
                            <td>{{ $order->items->sum('quantity') }}</td>
                            <td>PKR {{ number_format($order->items->sum('discount_amount'), 2) }}</td>
                            <td>PKR {{ number_format($order->total, 2) }}</td>
                            <td>{{ $order->created_at->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">No sales found for selected date.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
