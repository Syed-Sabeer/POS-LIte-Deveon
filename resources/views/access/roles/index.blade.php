@extends('layouts.app.master')
@section('title', 'Role Management')
@section('content')
<div class="page-header"><div class="page-title"><h4 class="fw-bold">Role Management</h4></div><div class="page-btn"><a href="{{ route('access.roles.create') }}" class="btn btn-primary">Create Role</a></div></div>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
<div class="card"><div class="card-body p-0 table-responsive"><table class="table table-bordered mb-0"><thead class="table-light"><tr><th>Name</th><th>Permissions</th><th>Action</th></tr></thead><tbody>@forelse($roles as $role)<tr><td>{{ $role->name }}</td><td>@foreach($role->permissions as $permission)<span class="badge bg-info me-1 mb-1">{{ $permission->name }}</span>@endforeach</td><td><a href="{{ route('access.roles.edit', $role) }}" class="btn btn-sm btn-warning">Edit</a></td></tr>@empty<tr><td colspan="3" class="text-center">No roles found.</td></tr>@endforelse</tbody></table></div></div>
@endsection
