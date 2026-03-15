@extends('layouts.app.master')

@section('title', 'Edit Purchase Invoice')

@section('content')
<div class="page-header"><div class="page-title"><h4 class="fw-bold">Edit Purchase Invoice {{ $invoice->invoice_number }}</h4></div><div class="page-btn"><a href="{{ route('purchases.show', $invoice) }}" class="btn btn-secondary">Back</a></div></div>
<div class="card"><div class="card-body"><form method="POST" action="{{ route('purchases.update', $invoice) }}">@method('PUT') @include('purchases._form')</form></div></div>
@endsection
