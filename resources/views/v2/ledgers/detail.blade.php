@extends('layouts.app.master')

@section('title', $account->name)
@section('css')@include('v2.partials.style')@endsection

@section('content')
<div class="v2-wrap">
    <div class="page-header"><div class="page-title v2-title"><h4 class="fw-bold">{{ $account->name }}</h4><h6>{{ ucwords(str_replace('-', ' ', $mode)) }}</h6></div><div class="v2-actions"><button onclick="window.print()" class="btn btn-primary">Print</button><a href="{{ url()->previous() }}" class="btn btn-secondary">Back</a></div></div>
    <div class="card mb-3"><div class="card-body"><form class="row g-2"><div class="col-md-3"><input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control"></div><div class="col-md-3"><input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control"></div><div class="col-md-2"><button class="btn btn-secondary w-100">Search</button></div></form></div></div>
    <div class="card"><div class="table-responsive"><table class="table table-bordered mb-0"><thead class="table-light"><tr><th>Date</th><th>Voucher</th><th>Particulars</th><th>Debit</th><th>Credit</th><th>Balance</th></tr></thead><tbody><tr><td>{{ optional($account->opening_date)->format('Y-m-d') }}</td><td>Opening</td><td>B/F Amount</td><td>{{ (float)$account->opening_amount >= 0 ? number_format((float)$account->opening_amount,2) : '0.00' }}</td><td>{{ (float)$account->opening_amount < 0 ? number_format(abs((float)$account->opening_amount),2) : '0.00' }}</td><td>{{ number_format((float)$account->opening_amount,2) }}</td></tr>@foreach($entries as $entry)<tr><td>{{ $entry->entry_date->format('Y-m-d') }}</td><td>{{ $entry->voucher_no }}</td><td>{{ $entry->particulars }}</td><td>{{ number_format((float)$entry->debit,2) }}</td><td>{{ number_format((float)$entry->credit,2) }}</td><td>{{ number_format((float)$entry->running_balance + (float)$account->opening_amount,2) }}</td></tr>@endforeach</tbody></table></div></div>
</div>
@endsection
