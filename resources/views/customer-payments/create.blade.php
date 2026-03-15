@extends('layouts.app.master')
@section('title', 'Receive Customer Payment')
@section('content')
<div class="page-header"><div class="page-title"><h4 class="fw-bold">Receive Customer Payment</h4></div><div class="page-btn"><a href="{{ route('customer-payments.index') }}" class="btn btn-secondary">Back</a></div></div>
<div class="card"><div class="card-body">
<form method="POST" action="{{ route('customer-payments.store') }}" class="row g-3">@csrf
<div class="col-md-4"><label class="form-label">Customer *</label><select name="customer_id" class="form-control" required><option value="">Select customer</option>@foreach($customers as $customer)<option value="{{ $customer->id }}">{{ $customer->full_name }}</option>@endforeach</select></div>
<div class="col-md-4"><label class="form-label">Invoice (optional)</label><select name="pos_order_id" class="form-control"><option value="">Advance payment</option>@foreach($invoices as $invoice)<option value="{{ $invoice->id }}">{{ $invoice->order_number }} | Due PKR {{ number_format($invoice->due_amount,2) }}</option>@endforeach</select></div>
<div class="col-md-4"><label class="form-label">Date *</label><input type="date" name="payment_date" class="form-control" value="{{ now()->toDateString() }}" required></div>
<div class="col-md-3"><label class="form-label">Amount *</label><input type="number" step="0.01" min="0.01" name="amount" class="form-control" required></div>
<div class="col-md-3"><label class="form-label">Method *</label><select name="payment_method" class="form-control"><option value="cash">Cash</option><option value="bank">Bank</option><option value="card">Card</option><option value="upi">UPI</option></select></div>
<div class="col-md-3"><label class="form-label">Account</label><select name="account_id" class="form-control"><option value="">Default</option>@foreach($accounts as $account)<option value="{{ $account->id }}">{{ $account->code }} - {{ $account->name }}</option>@endforeach</select></div>
<div class="col-md-3"><label class="form-label">Reference No</label><input type="text" name="reference_no" class="form-control"></div>
<div class="col-12"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
<div class="col-12"><button class="btn btn-primary">Save Payment</button></div>
</form>
</div></div>
@endsection
