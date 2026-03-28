@extends('layouts.app.master')

@section('title', 'Edit Customer')

@section('content')
<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">Edit Customer</h4>
            <h6>Update customer details</h6>
        </div>
    </div>
    <div class="page-btn mt-0">
        <a href="{{ route('customers.index') }}" class="btn btn-secondary"><i class="ti ti-arrow-left me-1"></i>Back to Customers</a>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card">
    <div class="card-header">
        <h6 class="mb-0">Customer Information</h6>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('customers.update', $customer) }}" class="row g-3">
            @csrf
            @method('PUT')

            <div class="col-md-6">
                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                <input type="text" name="full_name" class="form-control" value="{{ old('full_name', $customer->full_name) }}" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Company Name</label>
                <input type="text" name="company_name" class="form-control" value="{{ old('company_name', $customer->company_name) }}">
            </div>

            <div class="col-md-6">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone', $customer->phone) }}">
            </div>

            <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $customer->email) }}">
            </div>

            <div class="col-12">
                <label class="form-label">Address</label>
                <input type="text" name="address" class="form-control" value="{{ old('address', $customer->address) }}">
            </div>

            <div class="col-12">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $customer->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">Active</label>
                </div>
            </div>

            <div class="col-12 text-end">
                <a href="{{ route('customers.index') }}" class="btn btn-light">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Customer</button>
            </div>
        </form>
    </div>
</div>
@endsection
