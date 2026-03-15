@extends('layouts.app.master')

@section('title', 'Edit Product')

@section('content')
<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">Edit Product</h4>
            <h6>Update product information</h6>
        </div>
    </div>
    <ul class="table-top-head">
        <li>
            <a data-bs-toggle="tooltip" data-bs-placement="top" title="Refresh"><i class="ti ti-refresh"></i></a>
        </li>
        <li>
            <a data-bs-toggle="tooltip" data-bs-placement="top" title="Collapse" id="collapse-header"><i class="ti ti-chevron-up"></i></a>
        </li>
    </ul>
    <div class="page-btn mt-0">
        <a href="{{ route('admin.products.index') }}" class="btn btn-secondary"><i data-feather="arrow-left" class="me-2"></i>Back to Products</a>
    </div>
</div>

<form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data" class="add-product-form">
    @csrf
    @method('PUT')
    <div class="add-product">
        <div class="accordions-items-seperate" id="accordionSpacingExample">
            <div class="accordion-item border mb-4">
                <h2 class="accordion-header" id="headingSpacingOne">
                    <div class="accordion-button collapsed bg-white" data-bs-toggle="collapse" data-bs-target="#SpacingOne" aria-expanded="true" aria-controls="SpacingOne">
                        <div class="d-flex align-items-center justify-content-between flex-fill">
                            <h5 class="d-flex align-items-center"><i data-feather="info" class="text-primary me-2"></i><span>Product Information</span></h5>
                        </div>
                    </div>
                </h2>
                <div id="SpacingOne" class="accordion-collapse collapse show" aria-labelledby="headingSpacingOne">
                    <div class="accordion-body border-top">
                        <div class="row">
                            <div class="col-sm-6 col-12">
                                <div class="mb-3">
                                    <label class="form-label">Product Name <span class="text-danger ms-1">*</span></label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $product->name) }}" required maxlength="255">
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-sm-6 col-12">
                                <div class="mb-3">
                                    <label class="form-label">Stock Unit <span class="text-danger ms-1">*</span></label>
                                    <select name="unit" class="form-control @error('unit') is-invalid @enderror" required>
                                        <option value="pcs" {{ old('unit', $product->unit ?? 'pcs') === 'pcs' ? 'selected' : '' }}>pcs</option>
                                        <option value="kg" {{ old('unit', $product->unit) === 'kg' ? 'selected' : '' }}>kg</option>
                                        <option value="ltr" {{ old('unit', $product->unit) === 'ltr' ? 'selected' : '' }}>ltr</option>
                                        <option value="pack" {{ old('unit', $product->unit) === 'pack' ? 'selected' : '' }}>pack</option>
                                    </select>
                                    @error('unit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6 col-12">
                                <div class="mb-3">
                                    <label class="form-label">Cost Price <span class="text-danger ms-1">*</span></label>
                                    <input type="number" name="cost_price" class="form-control @error('cost_price') is-invalid @enderror" value="{{ old('cost_price', $product->cost_price) }}" required min="0" step="0.01">
                                    @error('cost_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-sm-6 col-12">
                                <div class="mb-3">
                                    <label class="form-label">Selling Price <span class="text-danger ms-1">*</span></label>
                                    <input type="number" name="selling_price" class="form-control @error('selling_price') is-invalid @enderror" value="{{ old('selling_price', $product->selling_price) }}" required min="0" step="0.01">
                                    @error('selling_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6 col-12">
                                <div class="mb-3">
                                    <label class="form-label">Stock Quantity <span class="text-danger ms-1">*</span></label>
                                    <input type="number" name="quantity" class="form-control @error('quantity') is-invalid @enderror" value="{{ old('quantity', $product->quantity) }}" required min="0" step="1">
                                    @error('quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-sm-6 col-12">
                                <div class="mb-3">
                                    <label class="form-label">Product Image</label>
                                    <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
                                    @if($product->image && Storage::disk('public')->exists($product->image))
                                        <div class="mt-2">
                                            <img src="{{ asset('storage/' . $product->image) }}" alt="Image" width="100" class="rounded">
                                        </div>
                                    @endif
                                    @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="d-flex align-items-center justify-content-end mb-4">
        <a href="{{ route('admin.products.index') }}" class="btn btn-secondary me-2">Cancel</a>
        <button type="submit" class="btn btn-primary">Save Changes</button>
    </div>
</form>
@endsection
