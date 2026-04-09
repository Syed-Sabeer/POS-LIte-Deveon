@extends('layouts.app.master')

@section('title', 'POS Receipt')

@section('content')
<div class="page-header no-print">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">Receipt</h4>
            <h6>{{ $order->order_number }}</h6>
        </div>
    </div>
    <div class="page-btn mt-0 d-flex gap-2">
        <a href="{{ route('pos.index') }}" class="btn btn-secondary"><i class="ti ti-device-laptop me-1"></i>New Sale</a>
        <a href="{{ route('pos.orders') }}" class="btn btn-info"><i class="ti ti-list me-1"></i>Order History</a>
        <a href="{{ route('pos.orders.edit', $order) }}" class="btn btn-warning"><i class="ti ti-refresh me-1"></i>Refund Sale</a>
        <form action="{{ route('pos.orders.destroy', $order) }}" method="POST" onsubmit="return confirm('Cancel this sale and delete the record?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger"><i class="ti ti-trash me-1"></i>Cancel Sale</button>
        </form>
        <button type="button" onclick="window.print()" class="btn btn-primary"><i class="ti ti-printer me-1"></i>Print</button>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success no-print">{{ session('success') }}</div>
@endif

<div class="card" id="receipt-area">
    <div class="card-body">
        <div class="text-center mb-4">
            <h3 class="mb-1">Sales Receipt</h3>
            <p class="mb-0"><strong>Sale No:</strong> {{ $order->order_number }}</p>
            <p class="mb-0">Order: {{ $order->order_number }}</p>
            <p class="mb-0">Date: {{ $order->created_at->format('Y-m-d H:i:s') }}</p>
        </div>

        <div class="mb-3">
            <p class="mb-1"><strong>Customer:</strong> {{ $order->customer?->full_name ?: $order->customer_name }}</p>
            <p class="mb-0"><strong>Payment Method:</strong> {{ strtoupper($order->payment_method) }}</p>
            <p class="mb-0"><strong>Invoice Date:</strong> {{ optional($order->invoice_date)->format('Y-m-d') }}</p>
            <p class="mb-0"><strong>Payment Status:</strong> {{ strtoupper($order->payment_status) }}</p>
            <p class="mb-0"><strong>Received Amount:</strong> PKR {{ number_format($order->received_amount ?? $order->paid_amount, 2) }}</p>
            <p class="mb-0"><strong>Return Amount:</strong> PKR {{ number_format($order->change_amount ?? 0, 2) }}</p>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Product</th>
                        <th class="text-center">Qty</th>
                        <th class="text-end">Unit Price</th>
                        <th class="text-end">Discount</th>
                        <th class="text-end">Line Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                        <tr>
                            <td>{{ $item->product_name }}</td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td class="text-end">PKR {{ number_format($item->unit_price, 2) }}</td>
                            <td class="text-end">PKR {{ number_format($item->discount_amount, 2) }}</td>
                            <td class="text-end">PKR {{ number_format($item->line_total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="4" class="text-end">Subtotal</th>
                        <th class="text-end">PKR {{ number_format($order->subtotal, 2) }}</th>
                    </tr>
                    <tr>
                        <th colspan="4" class="text-end">Invoice Discount</th>
                        <th class="text-end">PKR {{ number_format($order->discount_amount, 2) }}</th>
                    </tr>
                    <tr>
                        <th colspan="4" class="text-end">Tax</th>
                        <th class="text-end">PKR {{ number_format($order->tax_amount, 2) }}</th>
                    </tr>
                    <tr>
                        <th colspan="4" class="text-end">Total</th>
                        <th class="text-end">PKR {{ number_format($order->total, 2) }}</th>
                    </tr>
                    <tr>
                        <th colspan="4" class="text-end">Paid</th>
                        <th class="text-end">PKR {{ number_format($order->paid_amount, 2) }}</th>
                    </tr>
                    <tr>
                        <th colspan="4" class="text-end">Pending (Due)</th>
                        <th class="text-end">PKR {{ number_format($order->due_amount, 2) }}</th>
                    </tr>
                    <tr>
                        <th colspan="4" class="text-end">Return</th>
                        <th class="text-end">PKR {{ number_format($order->change_amount ?? 0, 2) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>

        <p class="text-center mb-0 mt-4">Thank you for your purchase.</p>
    </div>
</div>
@endsection

@section('css')
<style>
@media print {
    .no-print,
    .sidebar,
    .header,
    .breadcrumb,
    .page-wrapper .content .page-header {
        display: none !important;
    }
    #receipt-area {
        border: 0;
        box-shadow: none;
    }
}
</style>
@endsection
