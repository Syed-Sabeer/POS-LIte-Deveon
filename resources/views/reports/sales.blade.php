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

<div class="card mt-3">
    <div class="card-header">
        <h6 class="mb-0">Sales Dashboard Profile (Sales vs Cost vs Profit)</h6>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-xl-4 col-md-6">
                <div class="border rounded p-3 h-100">
                    <h6 class="fw-bold mb-3">Daily Profile</h6>
                    <p class="mb-1 text-muted">Sales: <strong class="text-dark">PKR {{ number_format($periodProfiles['daily']['sales_amount'], 2) }}</strong></p>
                    <p class="mb-1 text-muted">Received: <strong class="text-dark">PKR {{ number_format($periodProfiles['daily']['received_amount'], 2) }}</strong></p>
                    <p class="mb-1 text-muted">Due: <strong class="text-warning">PKR {{ number_format($periodProfiles['daily']['due_amount'], 2) }}</strong></p>
                    <p class="mb-1 text-muted">Cost: <strong class="text-dark">PKR {{ number_format($periodProfiles['daily']['cost_amount'], 2) }}</strong></p>
                    <p class="mb-0 text-muted">Net P/L: <strong class="{{ $periodProfiles['daily']['net_profit_loss'] >= 0 ? 'text-success' : 'text-danger' }}">PKR {{ number_format($periodProfiles['daily']['net_profit_loss'], 2) }}</strong></p>
                </div>
            </div>
            <div class="col-xl-4 col-md-6">
                <div class="border rounded p-3 h-100">
                    <h6 class="fw-bold mb-3">Weekly Profile</h6>
                    <p class="mb-1 text-muted">Sales: <strong class="text-dark">PKR {{ number_format($periodProfiles['weekly']['sales_amount'], 2) }}</strong></p>
                    <p class="mb-1 text-muted">Received: <strong class="text-dark">PKR {{ number_format($periodProfiles['weekly']['received_amount'], 2) }}</strong></p>
                    <p class="mb-1 text-muted">Due: <strong class="text-warning">PKR {{ number_format($periodProfiles['weekly']['due_amount'], 2) }}</strong></p>
                    <p class="mb-1 text-muted">Cost: <strong class="text-dark">PKR {{ number_format($periodProfiles['weekly']['cost_amount'], 2) }}</strong></p>
                    <p class="mb-0 text-muted">Net P/L: <strong class="{{ $periodProfiles['weekly']['net_profit_loss'] >= 0 ? 'text-success' : 'text-danger' }}">PKR {{ number_format($periodProfiles['weekly']['net_profit_loss'], 2) }}</strong></p>
                </div>
            </div>
            <div class="col-xl-4 col-md-6">
                <div class="border rounded p-3 h-100">
                    <h6 class="fw-bold mb-3">Monthly Profile</h6>
                    <p class="mb-1 text-muted">Sales: <strong class="text-dark">PKR {{ number_format($periodProfiles['monthly']['sales_amount'], 2) }}</strong></p>
                    <p class="mb-1 text-muted">Received: <strong class="text-dark">PKR {{ number_format($periodProfiles['monthly']['received_amount'], 2) }}</strong></p>
                    <p class="mb-1 text-muted">Due: <strong class="text-warning">PKR {{ number_format($periodProfiles['monthly']['due_amount'], 2) }}</strong></p>
                    <p class="mb-1 text-muted">Cost: <strong class="text-dark">PKR {{ number_format($periodProfiles['monthly']['cost_amount'], 2) }}</strong></p>
                    <p class="mb-0 text-muted">Net P/L: <strong class="{{ $periodProfiles['monthly']['net_profit_loss'] >= 0 ? 'text-success' : 'text-danger' }}">PKR {{ number_format($periodProfiles['monthly']['net_profit_loss'], 2) }}</strong></p>
                </div>
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
        <div class="card"><div class="card-body"><p class="mb-1 text-muted">Total Sales Amount</p><h4 class="mb-0">PKR {{ number_format($summary['total_sales_amount'], 2) }}</h4></div></div>
    </div>
    <div class="col-md-3">
        <div class="card"><div class="card-body"><p class="mb-1 text-muted">Total Earning (Received)</p><h4 class="mb-0">PKR {{ number_format($summary['total_earning'], 2) }}</h4></div></div>
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-md-3">
        <div class="card"><div class="card-body"><p class="mb-1 text-muted">Total Due</p><h4 class="mb-0 text-warning">PKR {{ number_format($summary['total_due'], 2) }}</h4></div></div>
    </div>
    <div class="col-md-3">
        <div class="card"><div class="card-body"><p class="mb-1 text-muted">Total Cost</p><h4 class="mb-0">PKR {{ number_format($summary['total_cost'], 2) }}</h4></div></div>
    </div>
    <div class="col-md-3">
        <div class="card"><div class="card-body"><p class="mb-1 text-muted">Total Profit</p><h4 class="mb-0 text-success">PKR {{ number_format($summary['total_profit'], 2) }}</h4></div></div>
    </div>
    <div class="col-md-3">
        <div class="card"><div class="card-body"><p class="mb-1 text-muted">Net Profit/Loss</p><h4 class="mb-0 {{ $summary['net_profit_loss'] >= 0 ? 'text-success' : 'text-danger' }}">PKR {{ number_format($summary['net_profit_loss'], 2) }}</h4></div></div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header">
        <h6 class="mb-0">Item Wise Sales & Profitability ({{ $reportDate }})</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Item Name</th>
                        <th class="text-end">Qty Sold</th>
                        <th class="text-end">Sales Amount</th>
                        <th class="text-end">Earning (Received)</th>
                        <th class="text-end">Due</th>
                        <th class="text-end">Discount</th>
                        <th class="text-end">Cost Amount</th>
                        <th class="text-end">Profit/Loss</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($itemWiseSales as $item)
                        <tr>
                            <td>{{ $item['product_name'] }}</td>
                            <td class="text-end">{{ $item['quantity_sold'] }}</td>
                            <td class="text-end">PKR {{ number_format($item['sales_amount'], 2) }}</td>
                            <td class="text-end">PKR {{ number_format($item['earning_amount'], 2) }}</td>
                            <td class="text-end">PKR {{ number_format($item['due_amount'], 2) }}</td>
                            <td class="text-end">PKR {{ number_format($item['discount_amount'], 2) }}</td>
                            <td class="text-end">PKR {{ number_format($item['cost_amount'], 2) }}</td>
                            <td class="text-end {{ $item['profit_or_loss'] >= 0 ? 'text-success' : 'text-danger' }}">PKR {{ number_format($item['profit_or_loss'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">No sales found for selected date.</td>
                        </tr>
                    @endforelse
                </tbody>
                            @if($itemWiseSales->isNotEmpty())
                            <tfoot class="table-dark fw-bold">
                                <tr>
                                    <td class="text-end">TOTALS</td>
                                    <td class="text-end">{{ $summary['total_items_sold'] }}</td>
                                    <td class="text-end">PKR {{ number_format($summary['total_sales_amount'], 2) }}</td>
                                    <td class="text-end">PKR {{ number_format($summary['total_earning'], 2) }}</td>
                                    <td class="text-end">PKR {{ number_format($summary['total_due'], 2) }}</td>
                                    <td class="text-end">PKR {{ number_format($summary['total_discount'], 2) }}</td>
                                    <td class="text-end">PKR {{ number_format($summary['total_cost'], 2) }}</td>
                                    <td class="text-end">PKR {{ number_format($summary['net_profit_loss'], 2) }}</td>
                                </tr>
                            </tfoot>
                            @endif
            </table>
        </div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header">
        <h6 class="mb-0">Order Wise Detail ({{ $reportDate }})</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Payment</th>
                        <th class="text-end">Sales Amount</th>
                        <th class="text-end">Paid</th>
                        <th class="text-end">Due</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dailyOrders as $order)
                        <tr>
                            <td>{{ $order->order_number }}</td>
                            <td>{{ $order->customer?->full_name ?: $order->customer_name }}</td>
                            <td>{{ strtoupper($order->payment_method) }}</td>
                            <td class="text-end">PKR {{ number_format($order->total, 2) }}</td>
                            <td class="text-end">PKR {{ number_format($order->paid_amount, 2) }}</td>
                            <td class="text-end">PKR {{ number_format($order->due_amount, 2) }}</td>
                            <td>{{ $order->invoice_date?->format('Y-m-d') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">No order records for selected date.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
