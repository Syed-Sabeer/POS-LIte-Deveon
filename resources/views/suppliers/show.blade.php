@extends('layouts.app.master')

@section('title', 'Supplier Details')

@section('content')
<div class="page-header"><div class="page-title"><h4 class="fw-bold">Supplier Details</h4></div><div class="page-btn d-flex gap-2"><a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-warning">Edit</a><a href="{{ route('suppliers.index') }}" class="btn btn-secondary">Back</a></div></div>
<div class="card"><div class="card-body">
    <div class="row g-3">
        <div class="col-md-4"><strong>Name:</strong> {{ $supplier->full_name }}</div>
        <div class="col-md-4"><strong>Company:</strong> {{ $supplier->company_name ?: '-' }}</div>
        <div class="col-md-4"><strong>Phone:</strong> {{ $supplier->phone ?: '-' }}</div>
        <div class="col-md-4"><strong>Email:</strong> {{ $supplier->email ?: '-' }}</div>
        <div class="col-md-4"><strong>Opening:</strong> {{ strtoupper($supplier->balance_type) }} PKR {{ number_format($supplier->opening_balance,2) }}</div>
        <div class="col-md-4"><strong>Status:</strong> {{ $supplier->is_active ? 'Active' : 'Inactive' }}</div>
        <div class="col-12"><strong>Address:</strong> {{ $supplier->address ?: '-' }}</div>
    </div>
</div></div>
@endsection
