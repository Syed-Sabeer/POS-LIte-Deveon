@extends('layouts.app.master')

@section('title', $title)
@section('css')@include('v2.partials.style')@endsection

@section('content')
<div class="v2-wrap">
    <div class="page-header"><div class="page-title v2-title"><h4 class="fw-bold">{{ $title }}</h4></div><a href="{{ route('v2.dashboard') }}" class="btn btn-secondary">Exit</a></div>
    @include('v2.partials.messages')
    @can('v2 insert')
    <div class="card mb-3"><div class="card-body"><form method="POST" action="{{ $storeRoute }}" class="row g-2">@csrf<div class="col-md-6"><input name="name" class="form-control" placeholder="Name" required></div><div class="col-md-2"><button class="btn btn-primary w-100">Save</button></div></form></div></div>
    @endcan
    <div class="card"><div class="table-responsive"><table class="table table-bordered mb-0"><thead class="table-light"><tr><th>Name</th><th>Active</th><th>Action</th></tr></thead><tbody>
        @forelse($items as $item)<tr><td>{{ $item->name }}</td><td>{{ $item->is_active ? 'Yes' : 'No' }}</td><td class="v2-actions">
            @can($permissionEdit)<form method="POST" action="{{ str_contains($storeRoute, 'categories') ? route('v2.categories.update',$item) : route('v2.brands.update',$item) }}" class="d-flex gap-2">@csrf @method('PUT')<input name="name" value="{{ $item->name }}" class="form-control form-control-sm"><button class="btn btn-sm btn-warning">Edit</button></form>@endcan
            @can($permissionDelete)<form method="POST" action="{{ str_contains($storeRoute, 'categories') ? route('v2.categories.destroy',$item) : route('v2.brands.destroy',$item) }}" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn btn-sm btn-danger">Delete</button></form>@endcan
        </td></tr>@empty<tr><td colspan="3" class="text-center">No records found.</td></tr>@endforelse
    </tbody></table></div><div class="p-3">{{ $items->links() }}</div></div>
</div>
@endsection
