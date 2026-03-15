@extends('layouts.app.master')
@section('title', 'Customer Statement')
@section('content')
<div class="page-header no-print"><div class="page-title"><h4 class="fw-bold">Customer Statement - {{ $customer->full_name }}</h4></div><div class="page-btn"><button onclick="window.print()" class="btn btn-primary">Print</button></div></div>
<div class="card"><div class="card-body p-0 table-responsive"><table class="table table-bordered mb-0"><thead class="table-light"><tr><th>Date</th><th>Ref</th><th>Description</th><th>Debit</th><th>Credit</th><th>Balance</th></tr></thead><tbody>@forelse($entries as $entry)<tr><td>{{ $entry->entry_date->format('Y-m-d') }}</td><td>{{ $entry->reference_no }}</td><td>{{ $entry->description }}</td><td>PKR {{ number_format($entry->debit,2) }}</td><td>PKR {{ number_format($entry->credit,2) }}</td><td>PKR {{ number_format($entry->balance,2) }}</td></tr>@empty<tr><td colspan="6" class="text-center">No entries found.</td></tr>@endforelse</tbody></table></div></div>
@endsection
@section('css')<style>@media print{.no-print,.sidebar,.header{display:none!important;}}</style>@endsection
