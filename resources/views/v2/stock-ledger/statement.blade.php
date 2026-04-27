@extends('layouts.app.master')

@section('title', $item->description)
@section('css')@include('v2.partials.style')@endsection

@section('content')
<div class="v2-wrap">
    <div class="page-header"><div class="page-title v2-title"><h4 class="fw-bold">{{ $item->description }}</h4><h6>{{ ucwords(str_replace('-', ' ', $report)) }}</h6></div><div class="v2-actions"><button onclick="window.print()" class="btn btn-primary">Print</button><a href="{{ route('v2.stock-ledger.index') }}" class="btn btn-secondary">Back</a></div></div>
    <div class="card"><div class="table-responsive"><table class="table table-bordered mb-0"><thead class="table-light"><tr><th>Date</th><th>Voucher</th><th>Account</th><th>Qty In</th><th>Qty Out</th><th>Rate</th><th>Amount</th><th>Remarks</th></tr></thead><tbody><tr><td>Opening</td><td>B/F</td><td></td><td>{{ number_format((float)$item->bf_qty,3) }}</td><td>0.000</td><td>{{ number_format((float)$item->opening_cost,2) }}</td><td>{{ number_format((float)$item->bf_qty*(float)$item->opening_cost,2) }}</td><td></td></tr>@foreach($movements as $move)<tr><td>{{ $move->movement_date->format('Y-m-d') }}</td><td>{{ $move->voucher_no }}</td><td>{{ $move->account?->name }}</td><td>{{ number_format((float)$move->qty_in,3) }}</td><td>{{ number_format((float)$move->qty_out,3) }}</td><td>{{ number_format((float)$move->rate,2) }}</td><td>{{ number_format((float)$move->amount,2) }}</td><td>{{ $move->remarks }}</td></tr>@endforeach</tbody></table></div></div>
</div>
@endsection
