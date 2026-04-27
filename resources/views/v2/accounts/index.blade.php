@extends('layouts.app.master')

@section('title', 'Accounts Manager')
@section('css')@include('v2.partials.style')@endsection

@section('content')
<div class="v2-wrap">
    <div class="page-header">
        <div class="page-title v2-title"><h4 class="fw-bold">Accounts Manager</h4><h6>Account master</h6></div>
        <div class="v2-actions"><a href="{{ route('v2.dashboard') }}" class="btn btn-secondary">Exit</a></div>
    </div>
    @include('v2.partials.messages')

    @can('v2 insert')
    <div class="card v2-simple-card mb-4">
        <div class="card-header"><h5 class="mb-0">New Account</h5></div>
        <div class="card-body">
            <form method="POST" action="{{ route('v2.accounts.store') }}" class="row g-3">
                @csrf
                <div class="col-md-3"><label class="form-label">Account Type *</label><select name="account_type" class="form-control" required>@foreach($types as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach</select></div>
                <div class="col-md-3"><label class="form-label">Account Name *</label><input name="name" class="form-control" required></div>
                <div class="col-md-2"><label class="form-label">Opening Date *</label><input type="date" name="opening_date" value="{{ now()->toDateString() }}" class="form-control" required></div>
                <div class="col-md-2"><label class="form-label">Opening Amount</label><input type="number" step="0.01" name="opening_amount" value="0" class="form-control"></div>
                <div class="col-md-1"><label class="form-label">Rate</label><input type="number" step="0.0001" name="currency_rate" value="1.00" class="form-control"></div>
                <div class="col-md-1 d-flex align-items-end"><label class="form-check"><input class="form-check-input" type="checkbox" name="is_active" value="1" checked> Active</label></div>
                <div class="col-12"><button class="btn btn-primary">Save</button></div>
            </form>
        </div>
    </div>
    @endcan

    <div class="card v2-simple-card">
        <div class="card-header">
            <form method="GET" class="row g-2">
                <div class="col-md-4"><input name="search" value="{{ request('search') }}" class="form-control" placeholder="Search account"></div>
                <div class="col-md-3"><select name="account_type" class="form-control"><option value="">All Types</option>@foreach($types as $key => $label)<option value="{{ $key }}" @selected(request('account_type')===$key)>{{ $label }}</option>@endforeach</select></div>
                <div class="col-md-2"><button class="btn btn-secondary w-100">Search</button></div>
                <div class="col-md-2"><button type="button" onclick="window.print()" class="btn btn-light w-100">Print</button></div>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered v2-table mb-0">
                <thead class="table-light"><tr><th>Code</th><th>Name</th><th>Type</th><th>Opening</th><th>Balance</th><th>Active</th><th>Action</th></tr></thead>
                <tbody>
                    @forelse($accounts as $account)
                        <tr>
                            <td>{{ $account->code }}</td><td>{{ $account->name }}</td><td>{{ $types[$account->account_type] ?? $account->account_type }}</td>
                            <td>{{ optional($account->opening_date)->format('Y-m-d') }} / PKR {{ number_format((float)$account->opening_amount,2) }}</td>
                            <td>PKR {{ number_format($account->balance,2) }}</td><td>{{ $account->is_active ? 'Yes' : 'No' }}</td>
                            <td class="v2-actions">
                                @can('v2 edit')<a class="btn btn-sm btn-warning" href="{{ route('v2.accounts.edit', $account) }}">Edit</a>@endcan
                                @can('v2 delete')<form method="POST" action="{{ route('v2.accounts.destroy', $account) }}" onsubmit="return confirm('Delete account?')">@csrf @method('DELETE')<button class="btn btn-sm btn-danger">Delete</button></form>@endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center">No accounts found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">{{ $accounts->links() }}</div>
    </div>
</div>
@endsection
