@extends('layouts.app.master')

@section('title', $voucher->voucher_no)
@section('css')@include('v2.partials.style')@endsection

@section('content')
<div class="v2-wrap v2-print">
    <div class="page-header no-print"><div class="page-title"><h4 class="fw-bold">{{ $voucher->voucher_no }}</h4></div><div class="v2-actions"><button onclick="window.print()" class="btn btn-primary">Print</button><a href="{{ route('v2.journal.show',$voucher) }}" class="btn btn-secondary">Back</a></div></div>
    <div class="text-center mb-4"><h3 class="fw-bold">{{ config('app.name', 'Company') }}</h3><h5>JOURNAL VOUCHER</h5></div>
    <p><strong>Voucher No:</strong> {{ $voucher->voucher_no }} <span class="ms-4"><strong>Date:</strong> {{ $voucher->voucher_date->format('Y-m-d') }}</span></p>
    <table class="table table-bordered"><thead><tr><th>Account Code</th><th>Account Name</th><th>Particulars</th><th>Post Date</th><th>Debit</th><th>Credit</th></tr></thead><tbody>@foreach($voucher->lines as $line)<tr><td>{{ $line->account_code }}</td><td>{{ $line->account_name }}</td><td>{{ $line->particulars }}</td><td>{{ optional($line->post_date)->format('Y-m-d') }}</td><td>{{ number_format((float)$line->debit,2) }}</td><td>{{ number_format((float)$line->credit,2) }}</td></tr>@endforeach</tbody><tfoot><tr><th colspan="4">Total</th><th>{{ number_format((float)$voucher->lines->sum('debit'),2) }}</th><th>{{ number_format((float)$voucher->lines->sum('credit'),2) }}</th></tr></tfoot></table>
    <div class="row text-center mt-5"><div class="col-4">Prepared By</div><div class="col-4">Checked By</div><div class="col-4">Approved By</div></div>
</div>
@endsection
