@extends('layouts.app.master')
@section('title', 'Create User')
@section('content')
<div class="page-header"><div class="page-title"><h4 class="fw-bold">Create User</h4></div><div class="page-btn"><a href="{{ route('access.users.index') }}" class="btn btn-secondary">Back</a></div></div>
<div class="card"><div class="card-body"><form method="POST" action="{{ route('access.users.store') }}" class="row g-3">@csrf
<div class="col-md-4"><label class="form-label">Name</label><input name="name" class="form-control" required></div>
<div class="col-md-4"><label class="form-label">Username</label><input name="username" class="form-control" required></div>
<div class="col-md-4"><label class="form-label">Email</label><input name="email" type="email" class="form-control" required></div>
<div class="col-md-4"><label class="form-label">Password</label><input name="password" type="password" class="form-control" required></div>
<div class="col-md-4"><label class="form-label">Confirm Password</label><input name="password_confirmation" type="password" class="form-control" required></div>
<div class="col-md-4"><label class="form-label">Status</label><select name="is_active" class="form-control"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
<div class="col-12"><label class="form-label">Roles</label><div class="row g-2">@foreach($roles as $role)<div class="col-md-3"><label class="d-flex align-items-center gap-2 border rounded p-2"><input type="checkbox" name="roles[]" value="{{ $role->name }}"><span>{{ $role->name }}</span></label></div>@endforeach</div></div>
<div class="col-12"><button class="btn btn-primary">Save User</button></div>
</form></div></div>
@endsection
