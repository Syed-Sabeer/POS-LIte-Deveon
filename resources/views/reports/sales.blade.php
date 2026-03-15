@extends('layouts.app.master')

@section('title', 'Sales Reports')

@section('content')
<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">Sales Reports</h4>
            <h6>Daily, weekly, and monthly totals</h6>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted">Daily Sales</h6>
                <h3 class="mb-0">${{ number_format($dailySales, 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted">Weekly Sales</h6>
                <h3 class="mb-0">${{ number_format($weeklySales, 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted">Monthly Sales</h6>
                <h3 class="mb-0">${{ number_format($monthlySales, 2) }}</h3>
            </div>
        </div>
    </div>
</div>
@endsection
