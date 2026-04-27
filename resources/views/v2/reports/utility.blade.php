@extends('layouts.app.master')

@section('title', $title)
@section('css')@include('v2.partials.style')@endsection

@section('content')
<div class="v2-wrap">
    <div class="page-header"><div class="page-title v2-title"><h4 class="fw-bold">{{ $title }}</h4></div><a href="{{ route('v2.dashboard') }}" class="btn btn-secondary">Exit</a></div>
    <div class="card"><div class="card-body"><pre class="mb-0">{{ json_encode($payload, JSON_PRETTY_PRINT) }}</pre></div></div>
</div>
@endsection
