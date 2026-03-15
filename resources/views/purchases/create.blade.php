@extends('layouts.app.master')

@section('title', 'Create Purchase Invoice')

@section('content')
<div class="page-header"><div class="page-title"><h4 class="fw-bold">Create Purchase Invoice</h4></div><div class="page-btn"><a href="{{ route('purchases.index') }}" class="btn btn-secondary">Back</a></div></div>
<div class="card"><div class="card-body"><form method="POST" action="{{ route('purchases.store') }}">@include('purchases._form')</form></div></div>
@endsection
