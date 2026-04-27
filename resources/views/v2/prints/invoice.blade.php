@extends('layouts.app.master')

@section('title', $invoice->voucher_no)
@section('css')@include('v2.partials.style')@endsection

@section('content')
@php($isGate = $format === 'gate-pass')
@php($isDc = $format === 'dc')
@php($isPurchase = $invoice->type === 'purchase')
<div class="v2-wrap v2-print">
    <div class="page-header no-print"><div class="page-title"><h4 class="fw-bold">{{ $invoice->voucher_no }}</h4></div><div class="v2-actions"><button onclick="window.print()" class="btn btn-primary">Print</button><a href="{{ route($isPurchase ? 'v2.purchase.show' : 'v2.sales.show', $invoice) }}" class="btn btn-secondary">Back</a></div></div>
    <div class="text-center mb-3">
        <h3 class="fw-bold">{{ config('app.name', 'Company') }}</h3>
        <p class="mb-1">{{ $isGate ? 'GATE PASS' : ($isDc ? 'DELIVERY CHALLAN / D.C.' : ($isPurchase ? 'PURCHASE CHALLAN' : 'SALE INVOICE')) }}</p>
    </div>
    <div class="row mb-3">
        <div class="col-6"><strong>{{ $isPurchase ? 'Supplier' : 'Customer' }}:</strong> {{ $invoice->party_name }}</div>
        <div class="col-3"><strong>No:</strong> {{ $invoice->voucher_no }}</div>
        <div class="col-3"><strong>Date/Time:</strong> {{ $invoice->invoice_date->format('Y-m-d') }} {{ $invoice->created_at?->format('H:i') }}</div>
        <div class="col-12"><strong>Remarks:</strong> {{ $invoice->memo }}</div>
    </div>
    <table class="table table-bordered">
        <thead><tr><th>S.No</th><th>Code</th><th>Item Description</th><th>Particulars</th><th>Packing</th><th>Qty</th><th>Packet</th>@unless($isDc || $isGate)<th>Rate</th><th>Amount</th>@endunless</tr></thead>
        <tbody>
            @foreach($invoice->items as $line)
                <tr><td>{{ $loop->iteration }}</td><td>{{ $line->item_code }}</td><td>{{ $line->item_name }}</td><td>{{ $line->item_detail }}</td><td>{{ $line->item?->packing }}</td><td>{{ number_format((float)$line->qty,3) }}</td><td>{{ number_format((float)$line->packet,3) }}</td>@unless($isDc || $isGate)<td>{{ number_format((float)$line->rate,2) }}</td><td>{{ number_format((float)$line->amount,2) }}</td>@endunless</tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr><th colspan="5">Total</th><th>{{ number_format((float)$invoice->items->sum('qty'),3) }}</th><th>{{ number_format((float)$invoice->items->sum('packet'),3) }}</th>@unless($isDc || $isGate)<th>Net</th><th>{{ number_format((float)$invoice->net_amount,2) }}</th>@endunless</tr>
        </tfoot>
    </table>
    @unless($isDc || $isGate)
        <div class="row">
            <div class="col-7"><strong>Amount in Words:</strong> {{ $amountInWords }}</div>
            <div class="col-5">
                <table class="table table-bordered"><tr><th>Gross</th><td>{{ number_format((float)$invoice->gross_amount,2) }}</td></tr><tr><th>Charges</th><td>{{ number_format((float)$invoice->charges,2) }}</td></tr><tr><th>Discount</th><td>{{ number_format((float)$invoice->discount,2) }}</td></tr><tr><th>Net Amount</th><td>{{ number_format((float)$invoice->net_amount,2) }}</td></tr></table>
            </div>
        </div>
    @endunless
    <div class="row text-center mt-5"><div class="col-4">Prepared By</div><div class="col-4">{{ $isDc || $isGate ? 'Delivered By' : 'Receiver Signature' }}</div><div class="col-4">{{ $isDc || $isGate ? 'Received By' : 'Authorized Sign/Stamp' }}</div></div>
</div>
@endsection
