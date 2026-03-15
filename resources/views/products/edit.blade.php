@extends('layouts.app.master')

@section('title', 'Edit Product')

@section('content')
<div class="container mt-4">
    <h2>Edit Product</h2>
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

    <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data" class="card p-4">
        @csrf
        @method('PUT')
        @include('products.partials.form', ['product' => $product])
        <button type="submit" class="btn btn-primary">Update Product</button>
    </form>
</div>
@endsection
