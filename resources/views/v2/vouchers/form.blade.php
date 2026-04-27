@extends('layouts.app.master')

@php($isReceipt = $type === 'receipt')
@php($voucher = $voucher ?? null)
@section('title', ($voucher ? 'Edit ' : 'New ') . ($isReceipt ? 'Receipt Voucher' : 'Payment Voucher'))
@section('css')@include('v2.partials.style')@endsection

@section('content')
<div class="v2-wrap">
    <div class="page-header"><div class="page-title v2-title"><h4 class="fw-bold">{{ $voucher ? 'Edit' : 'New' }} {{ $isReceipt ? 'Receipt Voucher' : 'Payment Voucher' }}</h4></div><a href="{{ route($isReceipt ? 'v2.receipts.index' : 'v2.payments.index') }}" class="btn btn-secondary">List</a></div>
    @include('v2.partials.messages')
    <div class="card"><div class="card-body">
        <form method="POST" action="{{ $voucher ? route($isReceipt ? 'v2.receipts.update' : 'v2.payments.update',$voucher) : route($isReceipt ? 'v2.receipts.store' : 'v2.payments.store') }}" class="row g-3">
            @csrf @if($voucher) @method('PUT') @endif
            <div class="col-md-3"><label class="form-label">{{ $isReceipt ? 'Entry Date' : 'Payment Date' }} *</label><input type="date" name="voucher_date" value="{{ old('voucher_date', optional($voucher?->voucher_date)->toDateString() ?: now()->toDateString()) }}" class="form-control" required></div>
            <div class="col-md-3"><label class="form-label">Voucher No</label><input name="voucher_no" value="{{ old('voucher_no', $voucher?->voucher_no) }}" class="form-control" placeholder="Auto"></div>
            <div class="col-md-3"><label class="form-label">Post Date</label><input type="date" name="post_date" value="{{ old('post_date', optional($voucher?->post_date)->toDateString() ?: now()->toDateString()) }}" class="form-control"></div>
            <div class="col-md-3"><label class="form-label">Currency Rate</label><input type="number" step="0.0001" name="currency_rate" value="{{ old('currency_rate', $voucher?->currency_rate ?? 1) }}" class="form-control"></div>
            <div class="col-md-4"><label class="form-label">{{ $isReceipt ? 'Receipt From' : 'Paid To' }} *</label><select name="account_id" class="form-control" required>@foreach($accounts as $account)<option value="{{ $account->id }}" @selected(old('account_id', $voucher?->account_id)==$account->id)>{{ $account->code }} - {{ $account->name }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label">{{ $isReceipt ? 'Received As' : 'Paid From' }} *</label><select name="contra_account_id" class="form-control" required>@foreach($cashAccounts as $account)<option value="{{ $account->id }}" @selected(old('contra_account_id', $voucher?->contra_account_id)==$account->id)>{{ $account->code }} - {{ $account->name }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label">Amount *</label><input type="number" step="0.01" name="amount" value="{{ old('amount', $voucher?->amount) }}" class="form-control" required></div>
            <div class="col-md-12"><label class="form-label">Particulars</label><input name="particulars" value="{{ old('particulars', $voucher?->particulars) }}" class="form-control"></div>
            <div class="col-12 v2-actions"><button class="btn btn-primary">Save</button><a href="{{ route($isReceipt ? 'v2.receipts.index' : 'v2.payments.index') }}" class="btn btn-secondary">Back</a></div>
        </form>
    </div></div>
</div>
@endsection
