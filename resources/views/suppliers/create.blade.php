@extends('layouts.app.master')

@section('title', 'Create Supplier')

@section('content')
<div class="page-header"><div class="page-title"><h4 class="fw-bold">Create Supplier</h4></div><div class="page-btn"><a href="{{ route('suppliers.index') }}" class="btn btn-secondary">Back</a></div></div>

<div class="card"><div class="card-body">
    <form method="POST" action="{{ route('suppliers.store') }}" class="row g-3">
        @csrf
        <div class="col-md-6"><label class="form-label">Full Name *</label><input type="text" name="full_name" class="form-control" required value="{{ old('full_name') }}"></div>
        <div class="col-md-6"><label class="form-label">Company Name</label><input type="text" name="company_name" class="form-control" value="{{ old('company_name') }}"></div>
        <div class="col-md-4"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control" value="{{ old('phone') }}"></div>
        <div class="col-md-4"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="{{ old('email') }}"></div>
        <div class="col-md-4"><label class="form-label">Opening Balance</label><input type="number" step="0.01" min="0" name="opening_balance" class="form-control" value="{{ old('opening_balance', 0) }}"></div>
        <div class="col-md-4"><label class="form-label">Balance Type</label><select name="balance_type" class="form-control"><option value="dr">DR</option><option value="cr" selected>CR</option></select></div>
        <div class="col-md-8"><label class="form-label">Address</label><input type="text" name="address" class="form-control" value="{{ old('address') }}"></div>
        <div class="col-12 form-check ms-2"><input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" checked><label class="form-check-label" for="is_active">Active</label></div>
        <div class="col-12"><button class="btn btn-primary">Save Supplier</button></div>
    </form>
</div></div>
@endsection
