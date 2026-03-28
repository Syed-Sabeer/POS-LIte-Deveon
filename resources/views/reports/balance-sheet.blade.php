@extends('layouts.app.master')

@section('title', 'Balance Sheet')

@section('content')
<div class="page-header no-print"><div class="page-title"><h4 class="fw-bold">Balance Sheet</h4><h6>As of {{ $asOfDate }}</h6></div><div class="page-btn d-flex gap-2"><form method="GET" class="d-flex gap-2"><input type="date" name="as_of_date" value="{{ $asOfDate }}" class="form-control"><button class="btn btn-secondary">Apply</button></form><button onclick="window.print()" class="btn btn-primary">Print</button></div></div>

<div class="row g-3">
    <div class="col-md-4"><div class="card"><div class="card-body"><h6>Assets</h6><h4>PKR {{ number_format($report['assets_total'],2) }}</h4></div></div></div>
    <div class="col-md-4"><div class="card"><div class="card-body"><h6>Liabilities</h6><h4>PKR {{ number_format($report['liabilities_total'],2) }}</h4></div></div></div>
    <div class="col-md-4"><div class="card"><div class="card-body"><h6>Equity</h6><h4>PKR {{ number_format($report['equity_total'],2) }}</h4></div></div></div>
</div>

<div class="card mt-3"><div class="card-body">
    <div class="row">
        <div class="col-md-4">
            <h6 class="fw-bold">Assets</h6>
            @include('reports.partials.balance-tree', ['nodes' => $report['assets']])
        </div>
        <div class="col-md-4">
            <h6 class="fw-bold">Liabilities</h6>
            @include('reports.partials.balance-tree', ['nodes' => $report['liabilities']])
        </div>
        <div class="col-md-4">
            <h6 class="fw-bold">Equity</h6>
            @include('reports.partials.balance-tree', ['nodes' => $report['equity']])
            <div class="d-flex justify-content-between border-top pt-2 mt-2"><span>Retained Earnings</span><span>PKR {{ number_format($report['retained_earnings'],2) }}</span></div>
        </div>
    </div>

    <hr>
    <div class="d-flex justify-content-between"><strong>Assets</strong><strong>PKR {{ number_format($report['assets_total'],2) }}</strong></div>
    <div class="d-flex justify-content-between"><strong>Liabilities + Equity</strong><strong>PKR {{ number_format($report['liabilities_total'] + $report['equity_total'],2) }}</strong></div>
    <div class="d-flex justify-content-between"><strong>Difference</strong><strong class="{{ abs($report['difference']) < 0.01 ? 'text-success' : 'text-danger' }}">PKR {{ number_format($report['difference'],2) }}</strong></div>
</div></div>
@endsection

@section('css')
<style>@media print{.no-print,.sidebar,.header{display:none!important;}}</style>
@endsection
