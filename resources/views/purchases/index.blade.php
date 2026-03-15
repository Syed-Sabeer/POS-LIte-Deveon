@extends('layouts.app.master')

@section('title', 'Purchase Invoices')

@section('content')
<div class="page-header"><div class="page-title"><h4 class="fw-bold">Purchase Invoices</h4><h6>Manage supplier bills and stock posting</h6></div><div class="page-btn"><a href="{{ route('purchases.create') }}" class="btn btn-primary">Create Purchase</a></div></div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

<div class="card mb-3"><div class="card-body"><form method="GET" class="row g-2">
    <div class="col-md-3"><select name="supplier_id" class="form-control"><option value="">All suppliers</option>@foreach($suppliers as $supplier)<option value="{{ $supplier->id }}" {{ (string)request('supplier_id') === (string)$supplier->id ? 'selected' : '' }}>{{ $supplier->full_name }}</option>@endforeach</select></div>
    <div class="col-md-2"><input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control"></div>
    <div class="col-md-2"><input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control"></div>
    <div class="col-md-2"><select name="status" class="form-control"><option value="">Any status</option><option value="draft" {{ request('status')==='draft'?'selected':'' }}>Draft</option><option value="posted" {{ request('status')==='posted'?'selected':'' }}>Posted</option></select></div>
    <div class="col-md-2"><select name="payment_status" class="form-control"><option value="">Any payment</option><option value="unpaid" {{ request('payment_status')==='unpaid'?'selected':'' }}>Unpaid</option><option value="partial" {{ request('payment_status')==='partial'?'selected':'' }}>Partial</option><option value="paid" {{ request('payment_status')==='paid'?'selected':'' }}>Paid</option></select></div>
    <div class="col-md-1"><button class="btn btn-secondary w-100">Go</button></div>
</form></div></div>

<div class="card"><div class="card-body p-0 table-responsive">
<table class="table table-bordered align-middle mb-0">
<thead class="table-light"><tr><th>Invoice</th><th>Supplier</th><th>Date</th><th>Status</th><th>Payment</th><th>Total</th><th>Due</th><th>Action</th></tr></thead>
<tbody>
@forelse($invoices as $invoice)
<tr>
<td>{{ $invoice->invoice_number }}</td><td>{{ $invoice->supplier->full_name }}</td><td>{{ $invoice->invoice_date->format('Y-m-d') }}</td>
<td><span class="badge bg-{{ $invoice->status === 'posted' ? 'success' : 'warning' }}">{{ strtoupper($invoice->status) }}</span></td>
<td><span class="badge bg-{{ $invoice->payment_status === 'paid' ? 'success' : ($invoice->payment_status==='partial'?'warning':'danger') }}">{{ strtoupper($invoice->payment_status) }}</span></td>
<td>PKR {{ number_format($invoice->total,2) }}</td><td>PKR {{ number_format($invoice->due_amount,2) }}</td>
<td><a href="{{ route('purchases.show', $invoice) }}" class="btn btn-sm btn-info">View</a></td>
</tr>
@empty
<tr><td colspan="8" class="text-center">No purchase invoices found.</td></tr>
@endforelse
</tbody></table>
</div><div class="p-3">{{ $invoices->links() }}</div></div>
@endsection
