@extends('layouts.app.master')

@section('title', 'Edit Account Details')
@section('css')@include('v2.partials.style')@endsection

@section('content')
<div class="v2-wrap">
    <div class="page-header"><div class="page-title v2-title"><h4 class="fw-bold">Edit Account Details</h4></div><a href="{{ route('v2.account-details.index') }}" class="btn btn-secondary">Back</a></div>
    @include('v2.partials.messages')
    <div class="card"><div class="card-body">
        <form method="POST" action="{{ route('v2.account-details.update', $accountDetail) }}" class="row g-3">@csrf @method('PUT')
            <div class="col-md-4"><label class="form-label">Linked Account *</label><select name="account_id" class="form-control" required>@foreach($accounts as $account)<option value="{{ $account->id }}" @selected($accountDetail->account_id===$account->id)>{{ $account->code }} - {{ $account->name }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label">Name *</label><input name="name" value="{{ $accountDetail->name }}" class="form-control" required></div>
            <div class="col-md-4"><label class="form-label">Phone</label><input name="phone" value="{{ $accountDetail->phone }}" class="form-control"></div>
            <div class="col-md-4"><label class="form-label">Address</label><input name="address" value="{{ $accountDetail->address }}" class="form-control"></div>
            <div class="col-md-2"><label class="form-label">City</label><input name="city" value="{{ $accountDetail->city }}" class="form-control"></div>
            <div class="col-md-2"><label class="form-label">Fax</label><input name="fax" value="{{ $accountDetail->fax }}" class="form-control"></div>
            <div class="col-md-2"><label class="form-label">Credit Days</label><input type="number" name="credit_days" value="{{ $accountDetail->credit_days }}" class="form-control"></div>
            <div class="col-md-2"><label class="form-label">Contact</label><input name="contact" value="{{ $accountDetail->contact }}" class="form-control"></div>
            <div class="col-md-3"><label class="form-label">Invoice Limit</label><input type="number" step="0.01" name="invoice_limit" value="{{ $accountDetail->invoice_limit }}" class="form-control"></div>
            <div class="col-md-3"><label class="form-label">Ledger Limit</label><input type="number" step="0.01" name="ledger_limit" value="{{ $accountDetail->ledger_limit }}" class="form-control"></div>
            @for($i=0;$i<5;$i++)<div class="col-md-2"><label class="form-label">P/S SMS {{ $i+1 }}</label><input name="purchase_sale_sms_contacts[]" value="{{ $accountDetail->purchase_sale_sms_contacts[$i] ?? '' }}" class="form-control"></div>@endfor
            @for($i=0;$i<5;$i++)<div class="col-md-2"><label class="form-label">P/R SMS {{ $i+1 }}</label><input name="payment_receipt_sms_contacts[]" value="{{ $accountDetail->payment_receipt_sms_contacts[$i] ?? '' }}" class="form-control"></div>@endfor
            <div class="col-md-12"><label class="form-label">Remarks</label><input name="remarks" value="{{ $accountDetail->remarks }}" class="form-control"></div>
            <div class="col-12 v2-actions"><button class="btn btn-primary">Save</button><a href="{{ route('v2.account-details.index') }}" class="btn btn-secondary">Exit</a></div>
        </form>
    </div></div>
</div>
@endsection
