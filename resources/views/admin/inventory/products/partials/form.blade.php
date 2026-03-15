<div class="mb-3">
    <label for="name" class="form-label">Product Name <span class="text-danger">*</span></label>
    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
        value="{{ old('name', $product->name ?? '') }}" required maxlength="255">
    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="mb-3">
    <label for="sku" class="form-label">SKU <span class="text-danger">*</span></label>
    <input type="text" name="sku" id="sku" class="form-control @error('sku') is-invalid @enderror"
        value="{{ old('sku', $product->sku ?? '') }}" required maxlength="100">
    @error('sku') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="mb-3">
    <label for="cost_price" class="form-label">Cost Price <span class="text-danger">*</span></label>
    <input type="number" name="cost_price" id="cost_price" class="form-control @error('cost_price') is-invalid @enderror"
        value="{{ old('cost_price', $product->cost_price ?? '') }}" required min="0" step="0.01">
    @error('cost_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="mb-3">
    <label for="selling_price" class="form-label">Selling Price <span class="text-danger">*</span></label>
    <input type="number" name="selling_price" id="selling_price" class="form-control @error('selling_price') is-invalid @enderror"
        value="{{ old('selling_price', $product->selling_price ?? '') }}" required min="0" step="0.01">
    @error('selling_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="mb-3">
    <label for="quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
    <input type="number" name="quantity" id="quantity" class="form-control @error('quantity') is-invalid @enderror"
        value="{{ old('quantity', $product->quantity ?? 0) }}" required min="0" step="1">
    @error('quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="mb-3">
    <label for="image" class="form-label">Product Image</label>
    <input type="file" name="image" id="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
    @if(isset($product) && $product->image && Storage::disk('public')->exists($product->image))
        <div class="mt-2">
            <img src="{{ asset('storage/' . $product->image) }}" alt="Image" width="100" class="rounded">
        </div>
    @endif
    @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>
