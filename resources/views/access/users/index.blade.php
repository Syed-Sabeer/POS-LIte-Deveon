@extends('layouts.app.master')
@section('title', 'User Management')
@section('content')
<div class="page-header"><div class="page-title"><h4 class="fw-bold">User Management</h4></div><div class="page-btn"><a href="{{ route('access.users.create') }}" class="btn btn-primary">Create User</a></div></div>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
<div class="card"><div class="card-body p-0 table-responsive"><table class="table table-bordered mb-0"><thead class="table-light"><tr><th>Name</th><th>Username</th><th>Email</th><th>Status</th><th>Roles</th><th>Action</th></tr></thead><tbody>@forelse($users as $user)<tr><td>{{ $user->name }}</td><td>{{ $user->username }}</td><td>{{ $user->email }}</td><td>{{ ucfirst($user->is_active) }}</td><td>@foreach($user->roles as $role)<span class="badge bg-info me-1">{{ $role->name }}</span>@endforeach</td><td><a href="{{ route('access.users.edit', $user) }}" class="btn btn-sm btn-warning">Edit</a></td></tr>@empty<tr><td colspan="6" class="text-center">No users found.</td></tr>@endforelse</tbody></table></div></div>
@endsection
