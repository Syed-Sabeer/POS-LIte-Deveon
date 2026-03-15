@extends('layouts.app.master')
@section('title', 'Edit Role')
@section('content')
<div class="page-header"><div class="page-title"><h4 class="fw-bold">Edit Role {{ $role->name }}</h4></div><div class="page-btn"><a href="{{ route('access.roles.index') }}" class="btn btn-secondary">Back</a></div></div>
<div class="card"><div class="card-body"><form method="POST" action="{{ route('access.roles.update', $role) }}">@csrf @method('PUT')<div class="mb-3"><label class="form-label">Role Name</label><input type="text" name="name" value="{{ $role->name }}" class="form-control" required></div><div class="mb-3"><label class="form-label">Permissions</label><div class="row g-2">@foreach($permissions as $permission)<div class="col-md-4"><label class="d-flex align-items-center gap-2 border rounded p-2"><input type="checkbox" name="permissions[]" value="{{ $permission->name }}" {{ in_array($permission->name, $assignedPermissions) ? 'checked' : '' }}><span>{{ $permission->name }}</span></label></div>@endforeach</div></div><button class="btn btn-primary">Update Role</button></form></div></div>
@endsection
