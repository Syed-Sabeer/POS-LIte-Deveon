@extends('layouts.app.master')

@section('title', $voucher->voucher_no)
@section('css')@include('v2.partials.style')@endsection

@section('content')
@php($isReceipt = $voucher->type === 'receipt')
<div class="v2-wrap">
    <div class="page-header"><div class="page-title v2-title"><h4 class="fw-bold">{{ $voucher->voucher_no }}</h4><h6>{{ $isReceipt ? 'Receipt Voucher' : 'Payment Voucher' }}</h6></div><div class="v2-actions"><a class="btn btn-primary" href="{{ route($isReceipt ? 'v2.receipts.print' : 'v2.payments.print',$voucher) }}">Print</a><a class="btn btn-secondary" href="{{ route($isReceipt ? 'v2.receipts.index' : 'v2.payments.index') }}">List</a></div></div>
    <div class="card"><div class="card-body row g-3"><div class="col-md-3"><strong>Date:</strong> {{ $voucher->voucher_date->format('Y-m-d') }}</div><div class="col-md-3"><strong>{{ $isReceipt ? 'Receipt From' : 'Paid To' }}:</strong> {{ $voucher->account?->name }}</div><div class="col-md-3"><strong>{{ $isReceipt ? 'Received As' : 'Paid From' }}:</strong> {{ $voucher->contraAccount?->name }}</div><div class="col-md-3"><strong>Amount:</strong> PKR {{ number_format((float)$voucher->amount,2) }}</div><div class="col-md-12"><strong>Particulars:</strong> {{ $voucher->particulars }}</div></div></div>
</div>
@endsection
