@extends('layouts.app.master')

@section('title', 'Purchase Details')

@section('content')
<div class="page-header">
    <div class="page-title"><h4 class="fw-bold">Purchase Invoice {{ $invoice->invoice_number }}</h4><h6>{{ $invoice->supplier->full_name }} | {{ $invoice->invoice_date->format('Y-m-d') }}</h6></div>
    <div class="page-btn d-flex gap-2">
        @if($invoice->status === 'draft')
            <a href="{{ route('purchases.edit', $invoice) }}" class="btn btn-warning">Edit</a>
            <form method="POST" action="{{ route('purchases.post', $invoice) }}">@csrf<button class="btn btn-success">Post Invoice</button></form>
        @endif
        <a href="{{ route('purchases.index') }}" class="btn btn-secondary">Back</a>
    </div>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

<div class="card mb-3"><div class="card-body row g-3">
    <div class="col-md-3"><strong>Status:</strong> {{ strtoupper($invoice->status) }}</div>
    <div class="col-md-3"><strong>Payment:</strong> {{ strtoupper($invoice->payment_status) }}</div>
    <div class="col-md-3"><strong>Total:</strong> PKR {{ number_format($invoice->total,2) }}</div>
    <div class="col-md-3"><strong>Due:</strong> PKR {{ number_format($invoice->due_amount,2) }}</div>
</div></div>

<div class="card"><div class="card-body p-0 table-responsive">
<table class="table table-bordered mb-0">
<thead class="table-light"><tr><th>Product</th><th>Cost</th><th>Qty</th><th>Line Total</th></tr></thead>
<tbody>@foreach($invoice->items as $item)<tr><td>{{ $item->product_name }}</td><td>PKR {{ number_format($item->cost_price,2) }}</td><td>{{ $item->quantity }}</td><td>PKR {{ number_format($item->line_total,2) }}</td></tr>@endforeach</tbody>
<tfoot>
<tr><th colspan="3" class="text-end">Subtotal</th><th>PKR {{ number_format($invoice->subtotal,2) }}</th></tr>
<tr><th colspan="3" class="text-end">Discount</th><th>PKR {{ number_format($invoice->discount_amount,2) }}</th></tr>
<tr><th colspan="3" class="text-end">Tax</th><th>PKR {{ number_format($invoice->tax_amount,2) }}</th></tr>
<tr><th colspan="3" class="text-end">Paid</th><th>PKR {{ number_format($invoice->paid_amount,2) }}</th></tr>
<tr><th colspan="3" class="text-end">Due</th><th>PKR {{ number_format($invoice->due_amount,2) }}</th></tr>
</tfoot>
</table>
</div></div>
@endsection
