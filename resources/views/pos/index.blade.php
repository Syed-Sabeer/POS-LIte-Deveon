@extends('layouts.app.master')

@section('title', 'POS')

@section('css')
<style>
:root { --line-input-width: 82px; }

.line-discount { width: var(--line-input-width); min-height: 38px; padding-left: 6px; padding-right: 6px; }
.line-qty { width: var(--line-input-width); min-height: 38px; font-size: 15px; font-weight: 600; text-align: center; padding-left: 6px; padding-right: 6px; }

.product-search { max-width: 320px; }

.pos-product-card {
    cursor: pointer;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    transition: all .18s ease;
    overflow: hidden;
    min-height: 100%;
}

.pos-product-card:hover {
    transform: translateY(-3px);
    border-color: #fd7e14;
    box-shadow: 0 8px 22px rgba(253, 126, 20, 0.16);
}

.pos-product-card:focus {
    outline: 0;
    border-color: #fd7e14;
    box-shadow: 0 0 0 .2rem rgba(253, 126, 20, .25);
}

.pos-product-card .pro-img {
    display: block;
    aspect-ratio: 16 / 10;
    background: linear-gradient(180deg, #fff7ef 0%, #ffffff 100%);
}

.pos-product-card .pro-img img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    padding: 12px;
}

.pos-product-meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
}

.pos-price-chip {
    background: #fff3e8;
    border: 1px solid #ffd6b2;
    color: #c35b00;
    border-radius: 999px;
    padding: 2px 10px;
    font-size: 12px;
    font-weight: 700;
    white-space: nowrap;
}

.pos-stock-chip {
    font-size: 11px;
    color: #6c757d;
}

.order-list-panel .table {
    margin-bottom: 0;
    table-layout: fixed;
    width: 100%;
}

.order-list-panel .table thead th,
.order-list-panel .table tbody td {
    padding: 8px 6px;
    vertical-align: middle;
}

.order-list-panel .table thead th:nth-child(1),
.order-list-panel .table tbody td:nth-child(1) { width: 44%; }
.order-list-panel .table thead th:nth-child(2),
.order-list-panel .table tbody td:nth-child(2) { width: 18%; }
.order-list-panel .table thead th:nth-child(3),
.order-list-panel .table tbody td:nth-child(3) { width: 16%; }
.order-list-panel .table thead th:nth-child(4),
.order-list-panel .table tbody td:nth-child(4) { width: 22%; }

.order-list-panel .table tbody td:first-child {
    word-break: break-word;
}

.order-list-panel .table-responsive {
    overflow-x: hidden;
}

@media (max-width: 575.98px) {
    :root { --line-input-width: 70px; }

    .line-discount,
    .line-qty {
        min-height: 36px;
    }
}
</style>
@endsection

@section('content')
<div class="row pos-wrapper pos-mode">
    <div class="col-md-12 col-lg-6 col-xl-7 d-flex">
        <div class="pos-categories tabs_wrapper p-0 flex-fill">
            <div class="content-wrap">
                <div class="d-flex align-items-center justify-content-between flex-wrap mb-3">
                    <div class="mb-2">
                        <h5 class="mb-1">Point Of Sale</h5>
                        <p class="mb-0">Live inventory, per-item discount, and linked customers</p>
                    </div>
                    {{-- <div class="mb-2 product-search">
                        <input id="productSearch" type="text" class="form-control" placeholder="Search product by name">
                    </div> --}}
                </div>

                <div class="row g-3" id="productGrid">
                    @forelse($products as $product)
                        <div class="col-sm-6 col-xl-4 product-card" data-name="{{ strtolower($product->name) }}">
                            <div class="product-info card mb-0 pos-product-card add-to-cart"
                                role="button"
                                tabindex="0"
                                data-id="{{ $product->id }}"
                                data-name="{{ $product->name }}"
                                data-price="{{ $product->selling_price }}"
                                data-stock="{{ $product->quantity }}"
                                data-unit="{{ $product->unit ?? 'pcs' }}">
                                <a href="javascript:void(0);" class="pro-img">
                                    @if($product->image && Storage::disk('public')->exists($product->image))
                                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}">
                                    @else
                                        <img src="{{ url('/images/placeholder.png') }}" alt="{{ $product->name }}">
                                    @endif
                                </a>
                                <div class="p-2 p-xl-3">
                                    <h6 class="product-name mb-1">{{ $product->name }}</h6>
                                    <div class="pos-product-meta">
                                        <span class="pos-stock-chip">Stock: {{ $product->quantity }} {{ $product->unit ?? 'pcs' }}</span>
                                        <span class="pos-price-chip">PKR {{ number_format($product->selling_price, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12"><div class="alert alert-warning mb-0">No active products with stock found.</div></div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-12 col-lg-6 col-xl-5 ps-0 d-lg-flex">
        <aside class="product-order-list bg-secondary-transparent flex-fill order-list-panel">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h3 class="mb-0">Order List</h3>
                        <span class="badge badge-dark fs-10 fw-medium badge-xs" id="itemsCount">Items: 0</span>
                    </div>

                    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
                    @if($errors->any())
                        <div class="alert alert-danger"><ul class="mb-0 ps-3">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                    @endif

                    <form method="POST" action="{{ route('pos.checkout') }}" id="checkoutForm">
                        @csrf
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label mb-0">Customer</label>
                                <button type="button" class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#addCustomerModal">Add</button>
                            </div>
                            <input type="text" id="customerSearch" class="form-control mb-2" placeholder="Search customer">
                            <select name="customer_id" id="customerSelect" class="form-control">
                                <option value="">Walk in Customer</option>
                                @foreach($customers as $customer)
                                    <option
                                        value="{{ $customer->id }}"
                                        data-search="{{ strtolower(trim($customer->full_name . ' ' . ($customer->phone ?? '') . ' ' . ($customer->company_name ?? ''))) }}"
                                        {{ old('customer_id') == $customer->id ? 'selected' : '' }}
                                    >{{ $customer->full_name }}{{ $customer->phone ? ' - ' . $customer->phone : '' }}</option>
                                @endforeach
                            </select>
                            <input type="hidden" name="customer_name" value="Walk in Customer">
                        </div>

                        <div class="table-responsive">
                            <table class="table table-borderless">
                                <thead>
                                    <tr>
                                        <th class="fw-bold bg-light">Item</th>
                                        <th class="fw-bold bg-light">Qty</th>
                                        <th class="fw-bold bg-light">Disc</th>
                                        <th class="fw-bold bg-light text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody id="cartBody"><tr><td colspan="4" class="text-center text-muted">No products selected</td></tr></tbody>
                            </table>
                        </div>

                        <table class="table table-borderless mb-3">
                            <tr><td>Sub Total</td><td class="text-end" id="subTotal">PKR 0.00</td></tr>
                            <tr><td>Line Discount</td><td class="text-end" id="discountTotal">PKR 0.00</td></tr>
                            <tr><td>Extra Discount</td><td class="text-end"><input type="number" step="0.01" min="0" name="additional_discount" id="additionalDiscount" class="form-control form-control-sm text-end" value="0"></td></tr>
                            <tr><td class="fw-bold border-top">Total Payable</td><td class="text-end fw-bold border-top" id="totalPayable">PKR 0.00</td></tr>
                            <tr><td>Received Amount</td><td class="text-end"><input type="number" step="0.01" min="0" name="paid_amount" id="paidAmount" class="form-control form-control-sm text-end" value="0"></td></tr>
                            <tr><td class="fw-bold">Due Amount</td><td class="text-end fw-bold" id="dueAmount">PKR 0.00</td></tr>
                            <tr><td class="fw-bold">Return Amount</td><td class="text-end fw-bold text-success" id="returnAmount">PKR 0.00</td></tr>
                        </table>

                        <input type="hidden" name="invoice_date" value="{{ old('invoice_date', now()->toDateString()) }}">

                        <div class="mb-3">
                            <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                            <select name="payment_method" id="paymentMethod" class="form-control" required>
                                <option value="cash">Cash</option>
                                <option value="cheque">Cheque</option>
                                <option value="pay_later">Pay Later</option>
                            </select>
                        </div>

                        <div id="hiddenItems"></div>
                        <button type="submit" class="btn btn-secondary w-100" id="checkoutBtn" disabled>Complete Checkout</button>
                    </form>
                </div>
            </div>
        </aside>
    </div>
</div>

<div class="modal fade" id="addCustomerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Add Customer</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form method="POST" action="{{ route('customers.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label">Full Name *</label><input type="text" name="full_name" class="form-control" required></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Company Name</label><input type="text" name="company_name" class="form-control"></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control"></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control"></div>
                        <div class="col-12"><label class="form-label">Address</label><textarea name="address" class="form-control" rows="2"></textarea></div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save Customer</button></div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
(function () {
    const cart = new Map();
    const cartBody = document.getElementById('cartBody');
    const hiddenItems = document.getElementById('hiddenItems');
    const subTotalEl = document.getElementById('subTotal');
    const discountTotalEl = document.getElementById('discountTotal');
    const totalPayableEl = document.getElementById('totalPayable');
    const dueAmountEl = document.getElementById('dueAmount');
    const returnAmountEl = document.getElementById('returnAmount');
    const additionalDiscountEl = document.getElementById('additionalDiscount');
    const paidAmountEl = document.getElementById('paidAmount');
    const paymentMethodEl = document.getElementById('paymentMethod');
    const customerSelectEl = document.getElementById('customerSelect');
    const checkoutBtn = document.getElementById('checkoutBtn');
    const itemsCount = document.getElementById('itemsCount');

    function money(value) { return 'PKR ' + Number(value).toFixed(2); }

    function renderCart() {
        cartBody.innerHTML = '';
        hiddenItems.innerHTML = '';
        let subtotal = 0; let discountTotal = 0; let payableBeforeHeader = 0; let index = 0;

        if (cart.size === 0) {
            cartBody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">No products selected</td></tr>';
            subTotalEl.textContent = money(0); discountTotalEl.textContent = money(0); totalPayableEl.textContent = money(0);
            itemsCount.textContent = 'Items: 0'; checkoutBtn.disabled = true; return;
        }

        cart.forEach((item, key) => {
            const gross = item.price * item.quantity;
            let discount = Number(item.discount || 0);
            if (discount < 0) { discount = 0; }
            if (discount > gross) { discount = gross; }
            item.discount = discount;
            const lineTotal = gross - discount;
            subtotal += gross; discountTotal += discount; payableBeforeHeader += lineTotal;

            const row = document.createElement('tr');
            row.innerHTML = `
                <td><a href="javascript:void(0);" data-remove="${key}" class="me-2"><i class="ti ti-trash"></i></a>${item.name}<br><small class="text-muted">${item.unit}</small></td>
                <td><input type="number" min="1" max="${item.stock}" value="${item.quantity}" class="form-control line-qty" data-qty="${key}"></td>
                <td><input type="number" min="0" max="${gross.toFixed(2)}" value="${item.discount}" step="0.01" class="form-control form-control-sm line-discount" data-discount="${key}"></td>
                <td class="text-end">${money(lineTotal)}</td>`;
            cartBody.appendChild(row);

            hiddenItems.insertAdjacentHTML('beforeend',
                '<input type="hidden" name="items[' + index + '][product_id]" value="' + item.id + '">' +
                '<input type="hidden" name="items[' + index + '][quantity]" value="' + item.quantity + '">' +
                '<input type="hidden" name="items[' + index + '][discount]" value="' + item.discount + '">'
            );
            index++;
        });

        const extraDiscount = Math.max(0, Number(additionalDiscountEl.value || 0));
        const totalPayable = Math.max(0, payableBeforeHeader - extraDiscount);
        let paid = Number(paidAmountEl.value || 0);
        if (!Number.isFinite(paid) || paid < 0) { paid = 0; }
        const paymentMethod = paymentMethodEl.value;

        if (paymentMethod === 'pay_later') {
            paid = 0;
            paidAmountEl.value = '0.00';
            paidAmountEl.setAttribute('readonly', 'readonly');
        } else {
            paidAmountEl.removeAttribute('readonly');
        }

        const changeAmount = Math.max(0, paid - totalPayable);
        const dueAmount = Math.max(0, totalPayable - Math.min(paid, totalPayable));

        subTotalEl.textContent = money(subtotal);
        discountTotalEl.textContent = money(discountTotal + extraDiscount);
        totalPayableEl.textContent = money(totalPayable);
        dueAmountEl.textContent = money(dueAmount);
        returnAmountEl.textContent = money(changeAmount);
        itemsCount.textContent = 'Items: ' + cart.size;
        checkoutBtn.disabled = false;
    }

    [additionalDiscountEl, paidAmountEl].forEach((el) => {
        el.addEventListener('input', renderCart);
    });

    paymentMethodEl.addEventListener('change', function () {
        if (this.value === 'pay_later' && !customerSelectEl.value) {
            alert('Select a customer first. Walk in customer cannot use Pay Later.');
            this.value = 'cash';
        }
        renderCart();
    });

    function addCardItem(cardEl) {
        const id = Number(cardEl.dataset.id); const key = String(id); const stock = Number(cardEl.dataset.stock);
        if (stock <= 0) { return; }
        if (cart.has(key)) {
            const existing = cart.get(key); if (existing.quantity < existing.stock) { existing.quantity += 1; }
        } else {
            cart.set(key, { id, name: cardEl.dataset.name, price: Number(cardEl.dataset.price), quantity: 1, stock, unit: cardEl.dataset.unit || 'pcs', discount: 0 });
        }
        renderCart();
    }

    document.querySelectorAll('.add-to-cart').forEach((card) => {
        card.addEventListener('click', function () {
            addCardItem(this);
        });
        card.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                addCardItem(this);
            }
        });
    });

    cartBody.addEventListener('change', function (event) {
        const qtyInput = event.target.closest('[data-qty]');
        if (qtyInput) {
            const item = cart.get(qtyInput.dataset.qty); if (!item) { return; }
            let qty = Number(qtyInput.value); if (!Number.isFinite(qty) || qty < 1) { qty = 1; }
            if (qty > item.stock) { qty = item.stock; }
            item.quantity = qty; renderCart(); return;
        }

        const discountInput = event.target.closest('[data-discount]');
        if (discountInput) {
            const item = cart.get(discountInput.dataset.discount); if (!item) { return; }
            let discount = Number(discountInput.value); if (!Number.isFinite(discount) || discount < 0) { discount = 0; }
            const maxDiscount = item.price * item.quantity; if (discount > maxDiscount) { discount = maxDiscount; }
            item.discount = discount; renderCart();
        }
    });

    cartBody.addEventListener('click', function (event) {
        const removeBtn = event.target.closest('[data-remove]');
        if (removeBtn) { cart.delete(removeBtn.dataset.remove); renderCart(); }
    });

    const productSearchEl = document.getElementById('productSearch');
    if (productSearchEl) {
        productSearchEl.addEventListener('input', function () {
            const term = this.value.toLowerCase().trim();
            document.querySelectorAll('.product-card').forEach((card) => { card.style.display = (card.dataset.name || '').includes(term) ? '' : 'none'; });
        });
    }

    const customerSearch = document.getElementById('customerSearch');
    const customerSelect = document.getElementById('customerSelect');
    if (customerSearch && customerSelect) {
        const originalOptions = Array.from(customerSelect.options).map((option) => ({
            value: option.value,
            label: option.text,
            search: (option.dataset.search || option.text || '').toLowerCase(),
        }));

        customerSearch.addEventListener('input', function () {
            const term = this.value.toLowerCase().trim();
            const selectedValue = customerSelect.value;
            const filtered = originalOptions.filter((item) => {
                if (!term) { return true; }
                if (!item.value) { return true; }
                return item.search.includes(term);
            });

            customerSelect.innerHTML = '';
            filtered.forEach((item) => {
                const option = document.createElement('option');
                option.value = item.value;
                option.textContent = item.label;
                if (item.value === selectedValue) { option.selected = true; }
                customerSelect.appendChild(option);
            });

            if (filtered.length === 0) {
                const emptyOption = document.createElement('option');
                emptyOption.value = '';
                emptyOption.textContent = 'No customer found';
                customerSelect.appendChild(emptyOption);
            }
        });
    }
})();
</script>
@endsection
