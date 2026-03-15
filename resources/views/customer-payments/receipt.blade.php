@extends('layouts.app.master')
@section('title', 'Customer Payment Receipt')
@section('content')
<div class="page-header no-print"><div class="page-title"><h4 class="fw-bold">Customer Payment Receipt</h4></div><div class="page-btn"><button onclick="window.print()" class="btn btn-primary">Print</button></div></div>
<div class="card"><div class="card-body">
<h5>Receipt #: {{ $payment->reference_no }}</h5>
<p>Date: {{ $payment->payment_date->format('Y-m-d') }}</p>
<p>Customer: {{ $payment->customer->full_name }}</p>
<p>Against Invoice: {{ $payment->posOrder?->order_number ?: 'Advance payment' }}</p>
<p>Payment Method: {{ strtoupper($payment->payment_method) }}</p>
<h4>Amount Received: PKR {{ number_format($payment->amount,2) }}</h4>
<p>Notes: {{ $payment->notes ?: '-' }}</p>
</div></div>
@endsection
@section('css')<style>@media print{.no-print,.sidebar,.header{display:none!important;}}</style>@endsection
