@extends('layouts.app.master')

@section('title', 'Reports')
@section('css')@include('v2.partials.style')@endsection

@section('content')
<div class="v2-wrap">
    <div class="page-header"><div class="page-title v2-title"><h4 class="fw-bold">Reports</h4></div><a href="{{ route('v2.dashboard') }}" class="btn btn-secondary">Exit</a></div>
    @foreach($reports as $group => $items)
        <div class="card mb-4"><div class="card-header"><h5 class="mb-0">{{ $group }}</h5></div><div class="card-body"><div class="v2-button-grid">@foreach($items as $key => $label)<a class="v2-big-button" href="{{ route('v2.reports.show', $key) }}"><i class="ti ti-printer"></i><span>{{ $label }}</span></a>@endforeach</div></div></div>
    @endforeach
</div>
@endsection
