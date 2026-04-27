@extends('layouts.app.master')

@section('title', 'User Rights')
@section('css')@include('v2.partials.style')@endsection

@section('content')
<div class="v2-wrap">
    <div class="page-header"><div class="page-title v2-title"><h4 class="fw-bold">User Rights</h4><h6>Add/Remove Users and permissions</h6></div><a href="{{ route('v2.dashboard') }}" class="btn btn-secondary">Exit</a></div>
    @include('v2.partials.messages')
    @foreach($users as $user)
        <div class="card mb-3">
            <div class="card-header"><h5 class="mb-0">{{ $user->name }} - {{ $user->email }}</h5></div>
            <div class="card-body">
                <form method="POST" action="{{ route('v2.users.rights.update', $user) }}">
                    @csrf @method('PUT')
                    <div class="row g-2">
                        @foreach($permissions as $permission)
                            <div class="col-md-3">
                                <label class="d-flex gap-2 border rounded p-2">
                                    <input type="checkbox" name="permissions[]" value="{{ $permission->name }}" @checked($user->can($permission->name))>
                                    <span>{{ str_replace('v2 ', '', $permission->name) }}</span>
                                </label>
                            </div>
                        @endforeach
                    </div>
                    <button class="btn btn-primary mt-3">Save Rights</button>
                </form>
            </div>
        </div>
    @endforeach
</div>
@endsection
