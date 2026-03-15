@extends('layouts.app.master')
@section('title', 'Supplier Payment Details')
@section('content')
<div class="page-header"><div class="page-title"><h4 class="fw-bold">Supplier Payment {{ $payment->reference_no }}</h4></div><div class="page-btn d-flex gap-2"><a href="{{ route('supplier-payments.voucher', $payment) }}" class="btn btn-primary">Print Voucher</a><a href="{{ route('supplier-payments.index') }}" class="btn btn-secondary">Back</a></div></div>
<div class="card"><div class="card-body row g-3">
<div class="col-md-4"><strong>Supplier:</strong> {{ $payment->supplier->full_name }}</div>
<div class="col-md-4"><strong>Date:</strong> {{ $payment->payment_date->format('Y-m-d') }}</div>
<div class="col-md-4"><strong>Amount:</strong> PKR {{ number_format($payment->amount,2) }}</div>
<div class="col-md-4"><strong>Invoice:</strong> {{ $payment->purchaseInvoice?->invoice_number ?: 'Advance' }}</div>
<div class="col-md-4"><strong>Method:</strong> {{ strtoupper($payment->payment_method) }}</div>
<div class="col-md-4"><strong>Account:</strong> {{ $payment->account?->name ?: '-' }}</div>
<div class="col-12"><strong>Notes:</strong> {{ $payment->notes ?: '-' }}</div>
</div></div>
@endsection
