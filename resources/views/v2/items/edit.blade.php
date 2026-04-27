@extends('layouts.app.master')

@section('title', 'Edit Item')
@section('css')@include('v2.partials.style')@endsection

@section('content')
<div class="v2-wrap">
    <div class="page-header"><div class="page-title v2-title"><h4 class="fw-bold">Edit Item</h4><h6>{{ $item->code }}</h6></div><a href="{{ route('v2.items.index') }}" class="btn btn-secondary">Back</a></div>
    @include('v2.partials.messages')
    <div class="card"><div class="card-body"><form method="POST" action="{{ route('v2.items.update',$item) }}" class="row g-3">@csrf @method('PUT')
        <div class="col-md-3"><label class="form-label">Category</label><select name="category_id" class="form-control">@foreach($categories as $category)<option value="{{ $category->id }}" @selected($item->category_id===$category->id)>{{ $category->name }}</option>@endforeach</select></div>
        <div class="col-md-3"><label class="form-label">Brand</label><select name="brand_id" class="form-control">@foreach($brands as $brand)<option value="{{ $brand->id }}" @selected($item->brand_id===$brand->id)>{{ $brand->name }}</option>@endforeach</select></div>
        <div class="col-md-3"><label class="form-label">Nick</label><input name="nick" value="{{ $item->nick }}" class="form-control"></div>
        <div class="col-md-3"><label class="form-label">Description / Item Name *</label><input name="description" value="{{ $item->description }}" class="form-control" required></div>
        <div class="col-md-2"><label class="form-label">B/F Qty</label><input type="number" step="0.001" name="bf_qty" value="{{ $item->bf_qty }}" class="form-control"></div>
        <div class="col-md-2"><label class="form-label">Minimum Qty</label><input type="number" step="0.001" name="minimum_qty" value="{{ $item->minimum_qty }}" class="form-control"></div>
        <div class="col-md-2"><label class="form-label">Maximum Qty</label><input type="number" step="0.001" name="maximum_qty" value="{{ $item->maximum_qty }}" class="form-control"></div>
        <div class="col-md-2"><label class="form-label">Packing</label><input name="packing" value="{{ $item->packing }}" class="form-control"></div>
        <div class="col-md-2"><label class="form-label">Packet Qty</label><input type="number" step="0.001" name="packet_qty" value="{{ $item->packet_qty }}" class="form-control"></div>
        <div class="col-md-2"><label class="form-label">Opening Cost</label><input type="number" step="0.01" name="opening_cost" value="{{ $item->opening_cost }}" class="form-control"></div>
        <div class="col-md-2"><label class="form-label">Cost</label><input type="number" step="0.01" name="cost" value="{{ $item->cost }}" class="form-control"></div>
        <div class="col-md-2"><label class="form-label">Retail Rate</label><input type="number" step="0.01" name="retail_rate" value="{{ $item->retail_rate }}" class="form-control"></div>
        <div class="col-md-2 d-flex align-items-end"><label class="form-check"><input class="form-check-input" type="checkbox" name="is_active" value="1" @checked($item->is_active)> Active</label></div>
        <div class="col-12 v2-actions"><button class="btn btn-primary">Save</button><a href="{{ route('v2.items.index') }}" class="btn btn-secondary">Exit</a></div>
    </form></div></div>
</div>
@endsection
