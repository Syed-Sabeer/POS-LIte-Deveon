@extends('layouts.app.master')
@section('title', 'Journal Entry')
@section('content')
<div class="page-header"><div class="page-title"><h4 class="fw-bold">Journal Entry {{ $entry->reference_no }}</h4></div><div class="page-btn"><a href="{{ route('journals.index') }}" class="btn btn-secondary">Back</a></div></div>
<div class="card mb-3"><div class="card-body row"><div class="col-md-3"><strong>Date:</strong> {{ $entry->entry_date->format('Y-m-d') }}</div><div class="col-md-3"><strong>Voucher:</strong> {{ $entry->voucher_type }}</div><div class="col-md-6"><strong>Description:</strong> {{ $entry->description }}</div></div></div>
<div class="card"><div class="card-body p-0 table-responsive"><table class="table table-bordered mb-0"><thead class="table-light"><tr><th>Account</th><th>Description</th><th>Debit</th><th>Credit</th></tr></thead><tbody>@foreach($entry->lines as $line)<tr><td>{{ $line->account->code }} - {{ $line->account->name }}</td><td>{{ $line->description }}</td><td>PKR {{ number_format($line->debit,2) }}</td><td>PKR {{ number_format($line->credit,2) }}</td></tr>@endforeach</tbody><tfoot><tr><th colspan="2" class="text-end">Total</th><th>PKR {{ number_format($entry->lines->sum('debit'),2) }}</th><th>PKR {{ number_format($entry->lines->sum('credit'),2) }}</th></tr></tfoot></table></div></div>
@endsection
