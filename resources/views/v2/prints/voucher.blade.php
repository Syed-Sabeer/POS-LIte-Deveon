@extends('layouts.app.master')

@section('title', $voucher->voucher_no)
@section('css')@include('v2.partials.style')@endsection

@section('content')
@php($isReceipt = $voucher->type === 'receipt')
<div class="v2-wrap v2-print">
    <div class="page-header no-print"><div class="page-title"><h4 class="fw-bold">{{ $voucher->voucher_no }}</h4></div><div class="v2-actions"><button onclick="window.print()" class="btn btn-primary">Print</button><a href="{{ route($isReceipt ? 'v2.receipts.show' : 'v2.payments.show',$voucher) }}" class="btn btn-secondary">Back</a></div></div>
    <div class="text-center mb-4"><h3 class="fw-bold">{{ config('app.name', 'Company') }}</h3><h5>{{ $isReceipt ? 'RECEIPT VOUCHER' : 'PAYMENT VOUCHER' }}</h5></div>
    <table class="table table-bordered">
        <tr><th>Voucher No</th><td>{{ $voucher->voucher_no }}</td><th>Date</th><td>{{ $voucher->voucher_date->format('Y-m-d') }}</td></tr>
        <tr><th>{{ $isReceipt ? 'Received From' : 'Paid To' }}</th><td>{{ $voucher->account?->name }}</td><th>{{ $isReceipt ? 'Received As' : 'Paid From' }}</th><td>{{ $voucher->contraAccount?->name }}</td></tr>
        <tr><th>Particulars</th><td colspan="3">{{ $voucher->particulars }}</td></tr>
        <tr><th>Amount</th><td colspan="3">PKR {{ number_format((float)$voucher->amount,2) }}</td></tr>
    </table>
    <div class="row text-center mt-5"><div class="col-4">Manager</div><div class="col-4">Prepared By</div><div class="col-4">{{ $isReceipt ? 'Received By' : 'Paid By' }}</div></div>
</div>
@endsection
