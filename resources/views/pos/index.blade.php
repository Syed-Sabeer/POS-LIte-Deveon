@extends('layouts.app.master')

@section('title', 'POS')

@section('css')
<style>
.sidebar { display: none !important; }
.page-wrapper { margin-left: 0 !important; }
.line-discount { width: 92px; }
</style>
@endsection

@section('content')
<div class="row pos-wrapper pos-mode">
    <div class="col-md-12 col-lg-7 col-xl-8 d-flex">
        <div class="pos-categories tabs_wrapper p-0 flex-fill">
            <div class="content-wrap">
                <div class="d-flex align-items-center justify-content-between flex-wrap mb-3">
                    <div class="mb-2">
                        <h5 class="mb-1">Point Of Sale</h5>
                        <p class="mb-0">Live inventory, per-item discount, and linked customers</p>
                    </div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <a href="{{ route('pos.orders') }}" class="btn btn-dark btn-sm"><i class="ti ti-list me-1"></i>Order History</a>
                        <a href="{{ route('reports.sales') }}" class="btn btn-info btn-sm"><i class="ti ti-chart-bar me-1"></i>Reports</a>
                        <input id="productSearch" type="text" class="form-control" placeholder="Search product">
                    </div>
                </div>

                <div class="row g-3" id="productGrid">
                    @forelse($products as $product)
                        <div class="col-sm-6 col-xl-4 product-card" data-name="{{ strtolower($product->name) }}">
                            <div class="product-info card mb-0 border">
                                <a href="javascript:void(0);" class="pro-img">
                                    @if($product->image && Storage::disk('public')->exists($product->image))
                                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}">
                                    @else
                                        <img src="{{ url('/images/placeholder.png') }}" alt="{{ $product->name }}">
                                    @endif
                                </a>
                                <h6 class="product-name mb-1">{{ $product->name }}</h6>
                                <p class="mb-2 fs-12 text-muted">Stock: {{ $product->quantity }} {{ $product->unit ?? 'pcs' }}</p>
                                <div class="d-flex align-items-center justify-content-between">
                                    <p class="text-gray-9 mb-0">${{ number_format($product->selling_price, 2) }}</p>
                                    <button type="button" class="btn btn-sm btn-primary add-to-cart"
                                        data-id="{{ $product->id }}"
                                        data-name="{{ $product->name }}"
                                        data-price="{{ $product->selling_price }}"
                                        data-stock="{{ $product->quantity }}"
                                        data-unit="{{ $product->unit ?? 'pcs' }}">Add</button>
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

    <div class="col-md-12 col-lg-5 col-xl-4 ps-0 d-lg-flex">
        <aside class="product-order-list bg-secondary-transparent flex-fill">
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
                                    <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>{{ $customer->full_name }}{{ $customer->phone ? ' - ' . $customer->phone : '' }}</option>
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
                            <tr><td>Sub Total</td><td class="text-end" id="subTotal">$0.00</td></tr>
                            <tr><td>Total Discount</td><td class="text-end" id="discountTotal">$0.00</td></tr>
                            <tr><td class="fw-bold border-top">Total Payable</td><td class="text-end fw-bold border-top" id="totalPayable">$0.00</td></tr>
                        </table>

                        <div class="mb-3">
                            <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                            <select name="payment_method" class="form-control" required>
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="upi">UPI</option>
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
    const checkoutBtn = document.getElementById('checkoutBtn');
    const itemsCount = document.getElementById('itemsCount');

    function money(value) { return '$' + Number(value).toFixed(2); }

    function renderCart() {
        cartBody.innerHTML = '';
        hiddenItems.innerHTML = '';
        let subtotal = 0; let discountTotal = 0; let payable = 0; let index = 0;

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
            subtotal += gross; discountTotal += discount; payable += lineTotal;

            const row = document.createElement('tr');
            row.innerHTML = `
                <td><a href="javascript:void(0);" data-remove="${key}" class="me-2"><i class="ti ti-trash"></i></a>${item.name}<br><small class="text-muted">${item.unit}</small></td>
                <td><input type="number" min="1" max="${item.stock}" value="${item.quantity}" class="form-control form-control-sm" data-qty="${key}"></td>
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

        subTotalEl.textContent = money(subtotal);
        discountTotalEl.textContent = money(discountTotal);
        totalPayableEl.textContent = money(payable);
        itemsCount.textContent = 'Items: ' + cart.size;
        checkoutBtn.disabled = false;
    }

    document.querySelectorAll('.add-to-cart').forEach((btn) => {
        btn.addEventListener('click', function () {
            const id = Number(this.dataset.id); const key = String(id); const stock = Number(this.dataset.stock);
            if (stock <= 0) { return; }
            if (cart.has(key)) {
                const existing = cart.get(key); if (existing.quantity < existing.stock) { existing.quantity += 1; }
            } else {
                cart.set(key, { id, name: this.dataset.name, price: Number(this.dataset.price), quantity: 1, stock, unit: this.dataset.unit || 'pcs', discount: 0 });
            }
            renderCart();
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

    document.getElementById('productSearch').addEventListener('input', function () {
        const term = this.value.toLowerCase().trim();
        document.querySelectorAll('.product-card').forEach((card) => { card.style.display = (card.dataset.name || '').includes(term) ? '' : 'none'; });
    });

    const customerSearch = document.getElementById('customerSearch');
    const customerSelect = document.getElementById('customerSelect');
    const options = Array.from(customerSelect.options);
    customerSearch.addEventListener('input', function () {
        const term = this.value.toLowerCase().trim();
        customerSelect.innerHTML = '';
        options.forEach((option) => {
            if (!term || option.text.toLowerCase().includes(term)) { customerSelect.appendChild(option.cloneNode(true)); }
        });
        if (!customerSelect.querySelector('option[value=""]')) {
            const walkIn = document.createElement('option'); walkIn.value = ''; walkIn.textContent = 'Walk in Customer'; customerSelect.prepend(walkIn);
        }
    });
})();
</script>
@endsection
