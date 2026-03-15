@extends('layouts.app.master')

@section('title', 'Dashboard')

@section('content')
<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
    <div>
        <h1 class="mb-1">Business Dashboard</h1>
        <p class="fw-medium mb-0">Track your sales, stock, and item performance in one place.</p>
    </div>
    <a href="{{ route('reports.sales') }}" class="btn btn-primary"><i class="ti ti-chart-bar me-1"></i>Open Sales Reports</a>
</div>

<div class="row g-3 mb-3">
    <div class="col-xl-3 col-sm-6 d-flex">
        <div class="card bg-primary sale-widget flex-fill">
            <div class="card-body d-flex align-items-center">
                <span class="sale-icon bg-white text-primary"><i class="ti ti-cash fs-24"></i></span>
                <div class="ms-2">
                    <p class="text-white mb-1">Today Sales</p>
                    <h4 class="text-white mb-0">PKR {{ number_format($summary['today_sales'], 2) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6 d-flex">
        <div class="card bg-secondary sale-widget flex-fill">
            <div class="card-body d-flex align-items-center">
                <span class="sale-icon bg-white text-secondary"><i class="ti ti-calendar-stats fs-24"></i></span>
                <div class="ms-2">
                    <p class="text-white mb-1">Month Sales</p>
                    <h4 class="text-white mb-0">PKR {{ number_format($summary['month_sales'], 2) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6 d-flex">
        <div class="card bg-info sale-widget flex-fill">
            <div class="card-body d-flex align-items-center">
                <span class="sale-icon bg-white text-info"><i class="ti ti-shopping-cart fs-24"></i></span>
                <div class="ms-2">
                    <p class="text-white mb-1">Orders (Today / Total)</p>
                    <h4 class="text-white mb-0">{{ $summary['total_orders_today'] }} / {{ $summary['total_orders'] }}</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6 d-flex">
        <div class="card bg-teal sale-widget flex-fill">
            <div class="card-body d-flex align-items-center">
                <span class="sale-icon bg-white text-teal"><i class="ti ti-package fs-24"></i></span>
                <div class="ms-2">
                    <p class="text-white mb-1">Items / Total Stock</p>
                    <h4 class="text-white mb-0">{{ $summary['total_items'] }} / {{ $summary['total_stock'] }}</h4>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-xl-7 d-flex">
        <div class="card flex-fill">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Item Wise Sell</h5>
                <span class="badge bg-light text-dark">Top 10</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Item</th>
                                <th>Sold Qty</th>
                                <th>Sales Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($itemWiseSell as $row)
                                <tr>
                                    <td>{{ $row->product_name }}</td>
                                    <td>{{ (int) $row->sold_qty }}</td>
                                    <td>PKR {{ number_format((float) $row->sold_value, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center">No sales yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-5 d-flex">
        <div class="card flex-fill">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Current Stock (Low to High)</h5>
                <a href="{{ route('stock.index') }}" class="btn btn-sm btn-light">Manage Stock</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Item</th>
                                <th>Current Stock</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($currentStock as $product)
                                <tr>
                                    <td>{{ $product->name }}</td>
                                    <td>{{ $product->quantity }} {{ $product->unit ?? 'pcs' }}</td>
                                    <td>
                                        @if($product->quantity <= 5)
                                            <span class="badge bg-danger">Low</span>
                                        @elseif($product->quantity <= 20)
                                            <span class="badge bg-warning text-dark">Medium</span>
                                        @else
                                            <span class="badge bg-success">Healthy</span>
                                        @endif
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
            </div>
        </div>
    </div>
</div>
@endsection
