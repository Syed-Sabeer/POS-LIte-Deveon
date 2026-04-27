@extends('layouts.app.master')

@section('title', $type === 'receipt' ? 'Receipts' : 'Payments')
@section('css')@include('v2.partials.style')@endsection

@section('content')
@php($isReceipt = $type === 'receipt')
<div class="v2-wrap">
    <div class="page-header"><div class="page-title v2-title"><h4 class="fw-bold">{{ $isReceipt ? 'Receipts' : 'Payments' }}</h4><h6>{{ $isReceipt ? 'Receipt Vouchers' : 'Payment Vouchers' }}</h6></div><div class="v2-actions"><a class="btn btn-primary" href="{{ route($isReceipt ? 'v2.receipts.create' : 'v2.payments.create') }}">New</a><a class="btn btn-secondary" href="{{ route('v2.dashboard') }}">Exit</a></div></div>
    @include('v2.partials.messages')
    <div class="card"><div class="card-header"><form class="row g-2"><div class="col-md-5"><input name="search" value="{{ request('search') }}" class="form-control" placeholder="Search voucher or particulars"></div><div class="col-md-2"><button class="btn btn-secondary w-100">Search</button></div><div class="col-md-2"><button type="button" onclick="window.print()" class="btn btn-light w-100">Print</button></div></form></div>
    <div class="table-responsive"><table class="table table-bordered mb-0"><thead class="table-light"><tr><th>Voucher</th><th>Date</th><th>{{ $isReceipt ? 'Receipt From' : 'Paid To' }}</th><th>{{ $isReceipt ? 'Received As' : 'Paid From' }}</th><th>Particulars</th><th>Amount</th><th>Action</th></tr></thead><tbody>
        @forelse($vouchers as $voucher)<tr><td>{{ $voucher->voucher_no }}</td><td>{{ $voucher->voucher_date->format('Y-m-d') }}</td><td>{{ $voucher->account?->name }}</td><td>{{ $voucher->contraAccount?->name }}</td><td>{{ $voucher->particulars }}</td><td>PKR {{ number_format((float)$voucher->amount,2) }}</td><td class="v2-actions"><a class="btn btn-sm btn-info" href="{{ route($isReceipt ? 'v2.receipts.show' : 'v2.payments.show',$voucher) }}">View</a>@can('v2 edit')<a class="btn btn-sm btn-warning" href="{{ route($isReceipt ? 'v2.receipts.edit' : 'v2.payments.edit',$voucher) }}">Edit</a>@endcan</td></tr>@empty<tr><td colspan="7" class="text-center">No vouchers found.</td></tr>@endforelse
    </tbody></table></div><div class="p-3">{{ $vouchers->links() }}</div></div>
</div>
@endsection
