@extends('layouts.app.master')
@section('title', 'Cash Book')
@section('content')
<div class="page-header"><div class="page-title"><h4 class="fw-bold">Cash Book ({{ $cashAccount->name }})</h4></div></div>
<div class="card mb-3"><div class="card-body"><form method="GET" class="row g-2"><div class="col-md-4"><input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control"></div><div class="col-md-4"><input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control"></div><div class="col-md-2"><button class="btn btn-secondary w-100">Filter</button></div></form></div></div>
<div class="card"><div class="card-body p-0 table-responsive"><table class="table table-bordered mb-0"><thead class="table-light"><tr><th>Date</th><th>Ref</th><th>Description</th><th>Debit</th><th>Credit</th><th>Balance</th></tr></thead><tbody>@forelse($entries as $entry)<tr><td>{{ $entry->entry_date->format('Y-m-d') }}</td><td>{{ $entry->reference_no }}</td><td>{{ $entry->description }}</td><td>PKR {{ number_format($entry->debit,2) }}</td><td>PKR {{ number_format($entry->credit,2) }}</td><td>PKR {{ number_format($entry->running_balance,2) }}</td></tr>@empty<tr><td colspan="6" class="text-center">No cash transactions found.</td></tr>@endforelse</tbody></table></div><div class="p-3">{{ $entries->links() }}</div></div>
@endsection
