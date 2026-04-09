@extends('layouts.app.master')

@section('title', 'Refund Sale')

@section('content')
<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">Refund Sale / Edit Order</h4>
            <h6>Order #{{ $order->order_number }}</h6>
        </div>
    </div>
    <div class="page-btn mt-0 d-flex gap-2">
        <a href="{{ route('pos.orders.show', $order) }}" class="btn btn-info"><i class="ti ti-receipt me-1"></i>Back to Receipt</a>
        <a href="{{ route('pos.orders') }}" class="btn btn-secondary"><i class="ti ti-list me-1"></i>Order History</a>
    </div>
</div>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card mt-3">
    <div class="card-body">
        <form method="POST" action="{{ route('pos.orders.update', $order) }}" id="refund-form">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Customer</label>
                    <select name="customer_id" class="form-select" id="customer_id">
                        <option value="">Walk in Customer</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ (string) old('customer_id', $order->customer_id) === (string) $customer->id ? 'selected' : '' }}>
                                {{ $customer->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Walk in Name</label>
                    <input type="text" name="customer_name" class="form-control" value="{{ old('customer_name', $order->customer_name) }}" placeholder="Walk in Customer">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Invoice Date</label>
                    <input type="date" name="invoice_date" class="form-control" value="{{ old('invoice_date', optional($order->invoice_date)->format('Y-m-d') ?: optional($order->created_at)->format('Y-m-d')) }}" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Payment Method</label>
                    <select name="payment_method" class="form-select" id="payment_method" required>
                        @php($paymentMethod = old('payment_method', $order->payment_method))
                        <option value="cash" {{ $paymentMethod === 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="cheque" {{ $paymentMethod === 'cheque' ? 'selected' : '' }}>Cheque</option>
                        <option value="pay_later" {{ $paymentMethod === 'pay_later' ? 'selected' : '' }}>Pay Later</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Invoice Discount</label>
                    <input type="number" step="0.01" min="0" name="additional_discount" id="additional_discount" class="form-control" value="{{ old('additional_discount', 0) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Paid Amount</label>
                    <input type="number" step="0.01" min="0" name="paid_amount" id="paid_amount" class="form-control" value="{{ old('paid_amount', $order->received_amount ?? $order->paid_amount) }}" required>
                </div>
            </div>

            <div class="table-responsive mt-4">
                <table class="table table-bordered align-middle mb-0" id="refund-items-table">
                    <thead class="table-light">
                        <tr>
                            <th>Product</th>
                            <th width="130">Qty</th>
                            <th width="170">Unit Price</th>
                            <th width="170">Discount</th>
                            <th width="170" class="text-end">Line Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $idx => $item)
                            <tr class="item-row">
                                <td>
                                    {{ $item->product_name }}
                                    <input type="hidden" name="items[{{ $idx }}][product_id]" value="{{ $item->product_id }}">
                                </td>
                                <td>
                                    <input type="number" min="1" step="1" name="items[{{ $idx }}][quantity]" class="form-control item-qty" value="{{ old('items.'.$idx.'.quantity', $item->quantity) }}" required>
                                </td>
                                <td>
                                    <input type="number" min="0" step="0.01" name="items[{{ $idx }}][unit_price]" class="form-control item-price" value="{{ old('items.'.$idx.'.unit_price', $item->unit_price) }}" required>
                                </td>
                                <td>
                                    <input type="number" min="0" step="0.01" name="items[{{ $idx }}][discount]" class="form-control item-discount" value="{{ old('items.'.$idx.'.discount', $item->discount_amount ?? 0) }}">
                                </td>
                                <td class="text-end item-total">0.00</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="4" class="text-end">Subtotal</th>
                            <th class="text-end" id="summary-subtotal">0.00</th>
                        </tr>
                        <tr>
                            <th colspan="4" class="text-end">Invoice Discount</th>
                            <th class="text-end" id="summary-invoice-discount">0.00</th>
                        </tr>
                        <tr>
                            <th colspan="4" class="text-end">Total Discount</th>
                            <th class="text-end" id="summary-discount">0.00</th>
                        </tr>
                        <tr>
                            <th colspan="4" class="text-end">Total</th>
                            <th class="text-end" id="summary-total">0.00</th>
                        </tr>
                        <tr>
                            <th colspan="4" class="text-end">Due</th>
                            <th class="text-end" id="summary-due">0.00</th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i>Update Sale</button>
                <a href="{{ route('pos.orders.show', $order) }}" class="btn btn-light">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const table = document.getElementById('refund-items-table');
    const additionalDiscountInput = document.getElementById('additional_discount');
    const paidAmountInput = document.getElementById('paid_amount');

    function toNumber(value) {
        const n = parseFloat(value);
        return Number.isFinite(n) ? n : 0;
    }

    function recalcTotals() {
        let subtotal = 0;
        let lineDiscountTotal = 0;

        table.querySelectorAll('tbody tr.item-row').forEach((row) => {
            const qty = Math.max(1, Math.floor(toNumber(row.querySelector('.item-qty')?.value)));
            const price = Math.max(0, toNumber(row.querySelector('.item-price')?.value));
            let discount = Math.max(0, toNumber(row.querySelector('.item-discount')?.value));

            const gross = qty * price;
            if (discount > gross) discount = gross;
            const lineTotal = gross - discount;

            subtotal += gross;
            lineDiscountTotal += discount;
            row.querySelector('.item-total').textContent = lineTotal.toFixed(2);
        });

        const invoiceDiscount = Math.max(0, toNumber(additionalDiscountInput?.value));
        const totalDiscount = Math.min(subtotal, lineDiscountTotal + invoiceDiscount);
        const total = Math.max(0, subtotal - totalDiscount);
        const paid = Math.max(0, toNumber(paidAmountInput?.value));
        const due = Math.max(0, total - Math.min(total, paid));

        document.getElementById('summary-subtotal').textContent = subtotal.toFixed(2);
        document.getElementById('summary-invoice-discount').textContent = invoiceDiscount.toFixed(2);
        document.getElementById('summary-discount').textContent = totalDiscount.toFixed(2);
        document.getElementById('summary-total').textContent = total.toFixed(2);
        document.getElementById('summary-due').textContent = due.toFixed(2);
    }

    document.getElementById('refund-form').addEventListener('input', function (event) {
        if (event.target.matches('.item-qty, .item-price, .item-discount, #additional_discount, #paid_amount')) {
            recalcTotals();
        }
    });

    recalcTotals();
});
</script>
@endsection
