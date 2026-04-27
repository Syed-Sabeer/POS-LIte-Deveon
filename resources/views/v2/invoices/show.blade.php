@extends('layouts.app.master')

@section('title', $invoice->voucher_no)
@section('css')@include('v2.partials.style')@endsection

@section('content')
@php($isPurchase = $invoice->type === 'purchase')
<div class="v2-wrap">
    <div class="page-header"><div class="page-title v2-title"><h4 class="fw-bold">{{ $invoice->voucher_no }}</h4><h6>{{ $invoice->party_name }}</h6></div><div class="v2-actions"><a class="btn btn-primary" href="{{ route($isPurchase ? 'v2.purchase.print' : 'v2.sales.print', [$invoice, $isPurchase ? 'purchase-challan' : 'invoice']) }}">Print</a>@if(!$isPurchase)<a class="btn btn-light" href="{{ route('v2.sales.print', [$invoice, 'dc']) }}">D.C.</a><a class="btn btn-light" href="{{ route('v2.sales.print', [$invoice, 'gate-pass']) }}">Gate Pass</a>@endif<a class="btn btn-secondary" href="{{ route($isPurchase ? 'v2.purchase.index' : 'v2.sales.index') }}">List</a></div></div>
    @include('v2.partials.messages')
    <div class="card mb-3"><div class="card-body row"><div class="col-md-3"><strong>Date:</strong> {{ $invoice->invoice_date->format('Y-m-d') }}</div><div class="col-md-3"><strong>Account:</strong> {{ $invoice->account?->code }}</div><div class="col-md-3"><strong>Gross:</strong> PKR {{ number_format((float)$invoice->gross_amount,2) }}</div><div class="col-md-3"><strong>Net:</strong> PKR {{ number_format((float)$invoice->net_amount,2) }}</div></div></div>
    <div class="card"><div class="table-responsive"><table class="table table-bordered mb-0"><thead class="table-light"><tr><th>S.No</th><th>Code</th><th>Item</th><th>Detail</th><th>Qty</th><th>Packet</th><th>Rate</th><th>Discount</th><th>Amount</th></tr></thead><tbody>@foreach($invoice->items as $line)<tr><td>{{ $loop->iteration }}</td><td>{{ $line->item_code }}</td><td>{{ $line->item_name }}</td><td>{{ $line->item_detail }}</td><td>{{ number_format((float)$line->qty,3) }}</td><td>{{ number_format((float)$line->packet,3) }}</td><td>{{ number_format((float)$line->rate,2) }}</td><td>{{ number_format((float)$line->discount,2) }}</td><td>{{ number_format((float)$line->amount,2) }}</td></tr>@endforeach</tbody></table></div></div>
</div>
@endsection
