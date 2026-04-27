@extends('layouts.app.master')

@section('title', 'Journal Vouchers')
@section('css')@include('v2.partials.style')@endsection

@section('content')
<div class="v2-wrap">
    <div class="page-header"><div class="page-title v2-title"><h4 class="fw-bold">Journal Vouchers</h4></div><div class="v2-actions"><a href="{{ route('v2.journal.create') }}" class="btn btn-primary">New</a><a href="{{ route('v2.dashboard') }}" class="btn btn-secondary">Exit</a></div></div>
    @include('v2.partials.messages')
    <div class="card"><div class="table-responsive"><table class="table table-bordered mb-0"><thead class="table-light"><tr><th>Voucher</th><th>Date</th><th>Remarks</th><th>Amount</th><th>Action</th></tr></thead><tbody>@forelse($vouchers as $voucher)<tr><td>{{ $voucher->voucher_no }}</td><td>{{ $voucher->voucher_date->format('Y-m-d') }}</td><td>{{ $voucher->remarks }}</td><td>PKR {{ number_format((float)$voucher->amount,2) }}</td><td class="v2-actions"><a class="btn btn-sm btn-info" href="{{ route('v2.journal.show',$voucher) }}">View</a><a class="btn btn-sm btn-primary" href="{{ route('v2.journal.print',$voucher) }}">Print</a></td></tr>@empty<tr><td colspan="5" class="text-center">No journal vouchers found.</td></tr>@endforelse</tbody></table></div><div class="p-3">{{ $vouchers->links() }}</div></div>
</div>
@endsection
