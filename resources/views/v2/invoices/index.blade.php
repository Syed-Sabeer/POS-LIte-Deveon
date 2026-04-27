@extends('layouts.app.master')

@section('title', $type === 'purchase' ? 'Purchase Invoices' : 'Sale Invoices')
@section('css')@include('v2.partials.style')@endsection

@section('content')
@php($isPurchase = $type === 'purchase')
<div class="v2-wrap">
    <div class="page-header">
        <div class="page-title v2-title"><h4 class="fw-bold">{{ $isPurchase ? 'Purchase Invoices' : 'Sale Invoices' }}</h4><h6>{{ $isPurchase ? 'Purchase Book' : 'Sale Bill Book' }}</h6></div>
        <div class="v2-actions"><a href="{{ route($isPurchase ? 'v2.purchase.create' : 'v2.sales.create') }}" class="btn btn-primary">New</a><a href="{{ route('v2.dashboard') }}" class="btn btn-secondary">Exit</a></div>
    </div>
    @include('v2.partials.messages')
    <div class="card">
        <div class="card-header"><form class="row g-2"><div class="col-md-4"><input name="search" value="{{ request('search') }}" class="form-control" placeholder="Search voucher or party"></div><div class="col-md-2"><input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control"></div><div class="col-md-2"><input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control"></div><div class="col-md-2"><button class="btn btn-secondary w-100">Search</button></div><div class="col-md-2"><button type="button" onclick="window.print()" class="btn btn-light w-100">Print</button></div></form></div>
        <div class="table-responsive"><table class="table table-bordered mb-0"><thead class="table-light"><tr><th>No</th><th>Date</th><th>Party</th><th>Gross</th><th>Charges</th><th>Discount</th><th>Net</th><th>Action</th></tr></thead><tbody>
            @forelse($invoices as $invoice)
                <tr><td>{{ $invoice->voucher_no }}</td><td>{{ $invoice->invoice_date->format('Y-m-d') }}</td><td>{{ $invoice->party_name }}</td><td>PKR {{ number_format((float)$invoice->gross_amount,2) }}</td><td>PKR {{ number_format((float)$invoice->charges,2) }}</td><td>PKR {{ number_format((float)$invoice->discount,2) }}</td><td>PKR {{ number_format((float)$invoice->net_amount,2) }}</td><td class="v2-actions"><a class="btn btn-sm btn-info" href="{{ route($isPurchase ? 'v2.purchase.show' : 'v2.sales.show', $invoice) }}">View</a>@can('v2 edit')<a class="btn btn-sm btn-warning" href="{{ route($isPurchase ? 'v2.purchase.edit' : 'v2.sales.edit', $invoice) }}">Edit</a>@endcan</td></tr>
            @empty
                <tr><td colspan="8" class="text-center">No invoices found.</td></tr>
            @endforelse
        </tbody></table></div><div class="p-3">{{ $invoices->links() }}</div>
    </div>
</div>
@endsection
