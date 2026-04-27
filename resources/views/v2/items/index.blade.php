@extends('layouts.app.master')

@section('title', 'Stock Manager')
@section('css')@include('v2.partials.style')@endsection

@section('content')
<div class="v2-wrap">
    <div class="page-header"><div class="page-title v2-title"><h4 class="fw-bold">Stock Manager</h4><h6>Item master</h6></div><a href="{{ route('v2.dashboard') }}" class="btn btn-secondary">Exit</a></div>
    @include('v2.partials.messages')
    @can('v2 insert')
    <div class="card mb-4"><div class="card-body"><form method="POST" action="{{ route('v2.items.store') }}" class="row g-3">@csrf
        <div class="col-md-3"><label class="form-label">Category</label><select name="category_id" class="form-control">@foreach($categories as $category)<option value="{{ $category->id }}">{{ $category->name }}</option>@endforeach</select></div>
        <div class="col-md-3"><label class="form-label">Brand</label><select name="brand_id" class="form-control">@foreach($brands as $brand)<option value="{{ $brand->id }}">{{ $brand->name }}</option>@endforeach</select></div>
        <div class="col-md-3"><label class="form-label">Nick</label><input name="nick" class="form-control"></div>
        <div class="col-md-3"><label class="form-label">Description / Item Name *</label><input name="description" class="form-control" required></div>
        <div class="col-md-2"><label class="form-label">B/F Qty</label><input type="number" step="0.001" name="bf_qty" value="0" class="form-control"></div>
        <div class="col-md-2"><label class="form-label">Minimum Qty</label><input type="number" step="0.001" name="minimum_qty" value="0" class="form-control"></div>
        <div class="col-md-2"><label class="form-label">Maximum Qty</label><input type="number" step="0.001" name="maximum_qty" value="0" class="form-control"></div>
        <div class="col-md-2"><label class="form-label">Packing</label><input name="packing" class="form-control"></div>
        <div class="col-md-2"><label class="form-label">Packet Qty</label><input type="number" step="0.001" name="packet_qty" value="0" class="form-control"></div>
        <div class="col-md-2"><label class="form-label">Opening Cost</label><input type="number" step="0.01" name="opening_cost" value="0" class="form-control"></div>
        <div class="col-md-2"><label class="form-label">Cost</label><input type="number" step="0.01" name="cost" value="0" class="form-control"></div>
        <div class="col-md-2"><label class="form-label">Retail Rate</label><input type="number" step="0.01" name="retail_rate" value="0" class="form-control"></div>
        <div class="col-md-2 d-flex align-items-end"><label class="form-check"><input class="form-check-input" type="checkbox" name="is_active" value="1" checked> Active</label></div>
        <div class="col-12"><button class="btn btn-primary">Save</button></div>
    </form></div></div>
    @endcan
    <div class="card"><div class="card-header"><form class="row g-2"><div class="col-md-4"><input name="search" value="{{ request('search') }}" class="form-control" placeholder="Search item/code"></div><div class="col-md-3"><select name="category_id" class="form-control"><option value="">All Categories</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(request('category_id')==$category->id)>{{ $category->name }}</option>@endforeach</select></div><div class="col-md-3"><select name="brand_id" class="form-control"><option value="">All Brands</option>@foreach($brands as $brand)<option value="{{ $brand->id }}" @selected(request('brand_id')==$brand->id)>{{ $brand->name }}</option>@endforeach</select></div><div class="col-md-2"><button class="btn btn-secondary w-100">Search</button></div></form></div>
    <div class="table-responsive"><table class="table table-bordered mb-0"><thead class="table-light"><tr><th>Code</th><th>Description</th><th>Category</th><th>Brand</th><th>Balance Qty</th><th>Packing</th><th>Cost</th><th>Retail</th><th>Action</th></tr></thead><tbody>
        @forelse($items as $item)<tr><td>{{ $item->code }}</td><td>{{ $item->description }}</td><td>{{ $item->category?->name }}</td><td>{{ $item->brand?->name }}</td><td>{{ number_format($item->balance_qty,3) }}</td><td>{{ $item->packing }}</td><td>@can('v2 cost visible')PKR {{ number_format((float)$item->cost,2) }}@else Hidden @endcan</td><td>PKR {{ number_format((float)$item->retail_rate,2) }}</td><td class="v2-actions">@can('v2 edit')<a class="btn btn-sm btn-warning" href="{{ route('v2.items.edit',$item) }}">Edit</a>@endcan @can('v2 delete')<form method="POST" action="{{ route('v2.items.destroy',$item) }}" onsubmit="return confirm('Delete item?')">@csrf @method('DELETE')<button class="btn btn-sm btn-danger">Delete</button></form>@endcan</td></tr>@empty<tr><td colspan="9" class="text-center">No items found.</td></tr>@endforelse
    </tbody></table></div><div class="p-3">{{ $items->links() }}</div></div>
</div>
@endsection
