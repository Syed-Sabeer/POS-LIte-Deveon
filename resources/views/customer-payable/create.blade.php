@extends('layouts.app.master')

@section('title', 'Receive Payment - ' . $customer->full_name)

@section('content')
<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">Receive Payment</h4>
            <h6>{{ $customer->full_name }}</h6>
        </div>
    </div>
    <div class="page-btn mt-0">
        <a href="{{ route('customer-payable.index') }}" class="btn btn-secondary"><i class="ti ti-arrow-left me-1"></i>Back</a>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row">
    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0">Outstanding Invoices</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-borderless mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Invoice</th>
                                <th>Date</th>
                                <th class="text-end">Total</th>
                                <th class="text-end">Paid</th>
                                <th class="text-end">Due</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($customer->posOrders as $order)
                                <tr>
                                    <td><a href="{{ route('pos.orders.show', $order) }}" target="_blank">#{{ $order->id }}</a></td>
                                    <td>{{ $order->invoice_date->format('Y-m-d') }}</td>
                                    <td class="text-end">PKR {{ number_format($order->total, 2) }}</td>
                                    <td class="text-end">PKR {{ number_format($order->paid_amount, 2) }}</td>
                                    <td class="text-end"><span class="badge bg-warning">PKR {{ number_format($order->due_amount, 2) }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No outstanding invoices.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0">Customer Info</h6>
            </div>
            <div class="card-body">
                <p class="mb-2"><strong>Name:</strong> {{ $customer->full_name }}</p>
                <p class="mb-2"><strong>Company:</strong> {{ $customer->company_name ?: '-' }}</p>
                <p class="mb-3"><strong>Phone:</strong> {{ $customer->phone ?: '-' }}</p>
                <hr>
                <p class="mb-0"><strong class="text-warning">Total Outstanding:</strong></p>
                <h4 class="text-warning mb-3">PKR {{ number_format($customer->getPendingAmount(), 2) }}</h4>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Record Payment</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('customer-payments.store') }}">
                    @csrf

                    <input type="hidden" name="customer_id" value="{{ $customer->id }}">

                    <div class="mb-3">
                        <label class="form-label">Payment Date <span class="text-danger">*</span></label>
                        <input type="date" name="payment_date" class="form-control" value="{{ old('payment_date', now()->toDateString()) }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Invoice <span class="text-danger">*</span></label>
                        <select name="pos_order_id" class="form-control" required>
                            <option value="">Select Invoice</option>
                            @foreach($customer->posOrders as $order)
                                <option value="{{ $order->id }}" data-due="{{ $order->due_amount }}">
                                    #{{ $order->id }} - PKR {{ number_format($order->due_amount, 2) }} due
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Amount to Receive <span class="text-danger">*</span></label>
                        <input type="number" name="amount" step="0.01" min="0" class="form-control" placeholder="0.00" required>
                        <small class="text-muted" id="amountHelp">Max: PKR 0.00</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                        <select name="payment_method" class="form-control" required>
                            <option value="cash">Cash</option>
                            <option value="cheque">Cheque</option>
                            <option value="bank_transfer">Bank Transfer</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Record Payment</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelector('select[name="pos_order_id"]').addEventListener('change', function () {
    const option = this.options[this.selectedIndex];
    const maxAmount = parseFloat(option.dataset.due) || 0;
    document.getElementById('amountHelp').textContent = 'Max: PKR ' + maxAmount.toFixed(2);
    document.querySelector('input[name="amount"]').max = maxAmount;
});
</script>
@endsection
