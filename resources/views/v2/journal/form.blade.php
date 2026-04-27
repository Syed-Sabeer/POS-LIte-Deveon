@extends('layouts.app.master')

@section('title', 'New Journal Voucher')
@section('css')@include('v2.partials.style')@endsection

@section('content')
<div class="v2-wrap">
    <div class="page-header"><div class="page-title v2-title"><h4 class="fw-bold">New Journal Voucher</h4></div><a href="{{ route('v2.journal.index') }}" class="btn btn-secondary">List</a></div>
    @include('v2.partials.messages')
    <form method="POST" action="{{ route('v2.journal.store') }}">@csrf
        <div class="card mb-3"><div class="card-body row g-3"><div class="col-md-3"><label class="form-label">Voucher No</label><input name="voucher_no" class="form-control" placeholder="Auto"></div><div class="col-md-3"><label class="form-label">Date *</label><input type="date" name="voucher_date" value="{{ now()->toDateString() }}" class="form-control" required></div><div class="col-md-2"><label class="form-label">Currency Rate</label><input type="number" step="0.0001" name="currency_rate" value="1" class="form-control"></div><div class="col-md-4"><label class="form-label">Remarks</label><input name="remarks" class="form-control"></div></div></div>
        <div class="card mb-3"><div class="card-header"><h5 class="mb-0">Lines</h5></div><div class="table-responsive"><table class="table table-bordered mb-0"><thead class="table-light"><tr><th>Account</th><th>Particulars</th><th>Post Date</th><th>Debit</th><th>Credit</th></tr></thead><tbody>@for($i=0;$i<4;$i++)<tr><td><select name="lines[{{ $i }}][account_id]" class="form-control">@foreach($accounts as $account)<option value="{{ $account->id }}">{{ $account->code }} - {{ $account->name }}</option>@endforeach</select></td><td><input name="lines[{{ $i }}][particulars]" class="form-control"></td><td><input type="date" name="lines[{{ $i }}][post_date]" value="{{ now()->toDateString() }}" class="form-control"></td><td><input type="number" step="0.01" name="lines[{{ $i }}][debit]" value="0" class="form-control"></td><td><input type="number" step="0.01" name="lines[{{ $i }}][credit]" value="0" class="form-control"></td></tr>@endfor</tbody></table></div></div>
        <div class="v2-actions"><button class="btn btn-primary">Save</button><a href="{{ route('v2.journal.index') }}" class="btn btn-secondary">Back</a></div>
    </form>
</div>
@endsection
