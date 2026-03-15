@extends('layouts.app.master')

@section('title', 'Add Product')

@section('content')
<div class="container mt-4">
    <h2>Add Product</h2>
    <a href="{{ route('products.index') }}" class="btn btn-secondary mb-3">Back to Products</a>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data" class="card p-4">
        @csrf
        @include('products.partials.form', ['product' => null])
        <button type="submit" class="btn btn-primary">Create Product</button>
    </form>
</div>
@endsection
