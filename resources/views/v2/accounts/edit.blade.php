@extends('layouts.app.master')

@section('title', 'Edit Account')
@section('css')@include('v2.partials.style')@endsection

@section('content')
<div class="v2-wrap">
    <div class="page-header"><div class="page-title v2-title"><h4 class="fw-bold">Edit Account</h4><h6>{{ $account->code }}</h6></div><a href="{{ route('v2.accounts.index') }}" class="btn btn-secondary">Back</a></div>
    @include('v2.partials.messages')
    <div class="card"><div class="card-body">
        <form method="POST" action="{{ route('v2.accounts.update', $account) }}" class="row g-3">@csrf @method('PUT')
            <div class="col-md-3"><label class="form-label">Account Type *</label><select name="account_type" class="form-control" required>@foreach($types as $key => $label)<option value="{{ $key }}" @selected($account->account_type===$key)>{{ $label }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label">Account Name *</label><input name="name" value="{{ $account->name }}" class="form-control" required></div>
            <div class="col-md-2"><label class="form-label">Opening Date *</label><input type="date" name="opening_date" value="{{ optional($account->opening_date)->toDateString() }}" class="form-control" required></div>
            <div class="col-md-2"><label class="form-label">Opening Amount</label><input type="number" step="0.01" name="opening_amount" value="{{ $account->opening_amount }}" class="form-control"></div>
            <div class="col-md-1"><label class="form-label">Rate</label><input type="number" step="0.0001" name="currency_rate" value="{{ $account->currency_rate }}" class="form-control"></div>
            <div class="col-12"><label class="form-check"><input class="form-check-input" type="checkbox" name="is_active" value="1" @checked($account->is_active)> Active in Books</label></div>
            <div class="col-12 v2-actions"><button class="btn btn-primary">Save</button><a href="{{ route('v2.accounts.index') }}" class="btn btn-secondary">Exit</a></div>
        </form>
    </div></div>
</div>
@endsection
