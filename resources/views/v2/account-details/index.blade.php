@extends('layouts.app.master')

@section('title', 'Account Details')
@section('css')@include('v2.partials.style')@endsection

@section('content')
<div class="v2-wrap">
    <div class="page-header"><div class="page-title v2-title"><h4 class="fw-bold">Account Details</h4><h6>Customer and supplier profile</h6></div><a href="{{ route('v2.dashboard') }}" class="btn btn-secondary">Exit</a></div>
    @include('v2.partials.messages')
    @can('v2 insert')
    <div class="card mb-4"><div class="card-body">
        <form method="POST" action="{{ route('v2.account-details.store') }}" class="row g-3">@csrf
            <div class="col-md-4"><label class="form-label">Linked Account *</label><select name="account_id" class="form-control" required>@foreach($accounts as $account)<option value="{{ $account->id }}">{{ $account->code }} - {{ $account->name }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label">Name *</label><input name="name" class="form-control" required></div>
            <div class="col-md-4"><label class="form-label">Phone</label><input name="phone" class="form-control"></div>
            <div class="col-md-4"><label class="form-label">Address</label><input name="address" class="form-control"></div>
            <div class="col-md-2"><label class="form-label">City</label><input name="city" class="form-control"></div>
            <div class="col-md-2"><label class="form-label">Fax</label><input name="fax" class="form-control"></div>
            <div class="col-md-2"><label class="form-label">Credit Days</label><input type="number" name="credit_days" value="0" class="form-control"></div>
            <div class="col-md-2"><label class="form-label">Contact</label><input name="contact" class="form-control"></div>
            <div class="col-md-3"><label class="form-label">Invoice Limit</label><input type="number" step="0.01" name="invoice_limit" value="0" class="form-control"></div>
            <div class="col-md-3"><label class="form-label">Ledger Limit</label><input type="number" step="0.01" name="ledger_limit" value="0" class="form-control"></div>
            @for($i=0;$i<5;$i++)
                <div class="col-md-2"><label class="form-label">P/S SMS {{ $i+1 }}</label><input name="purchase_sale_sms_contacts[]" class="form-control"></div>
            @endfor
            @for($i=0;$i<5;$i++)
                <div class="col-md-2"><label class="form-label">P/R SMS {{ $i+1 }}</label><input name="payment_receipt_sms_contacts[]" class="form-control"></div>
            @endfor
            <div class="col-md-12"><label class="form-label">Remarks</label><input name="remarks" class="form-control"></div>
            <div class="col-12"><button class="btn btn-primary">Save</button></div>
        </form>
    </div></div>
    @endcan
    <div class="card"><div class="card-header"><form class="row g-2"><div class="col-md-5"><input name="search" value="{{ request('search') }}" class="form-control" placeholder="Search name or phone"></div><div class="col-md-2"><button class="btn btn-secondary w-100">Search</button></div><div class="col-md-2"><button type="button" onclick="window.print()" class="btn btn-light w-100">Print</button></div></form></div>
        <div class="table-responsive"><table class="table table-bordered mb-0"><thead class="table-light"><tr><th>Name</th><th>Account</th><th>City</th><th>Phone</th><th>Contact</th><th>Limits</th><th>Action</th></tr></thead><tbody>
        @forelse($details as $detail)<tr><td>{{ $detail->name }}</td><td>{{ $detail->account?->code }} - {{ $detail->account?->name }}</td><td>{{ $detail->city }}</td><td>{{ $detail->phone }}</td><td>{{ $detail->contact }}</td><td>{{ number_format((float)$detail->invoice_limit,2) }} / {{ number_format((float)$detail->ledger_limit,2) }}</td><td class="v2-actions">@can('v2 edit')<a class="btn btn-sm btn-warning" href="{{ route('v2.account-details.edit',$detail) }}">Edit</a>@endcan @can('v2 delete')<form method="POST" action="{{ route('v2.account-details.destroy',$detail) }}" onsubmit="return confirm('Delete details?')">@csrf @method('DELETE')<button class="btn btn-sm btn-danger">Delete</button></form>@endcan</td></tr>@empty<tr><td colspan="7" class="text-center">No details found.</td></tr>@endforelse
        </tbody></table></div><div class="p-3">{{ $details->links() }}</div></div>
</div>
@endsection
