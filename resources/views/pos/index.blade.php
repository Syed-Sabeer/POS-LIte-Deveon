@extends('layouts.app.master')

@section('title', 'POS')

@section('css')
<style>
:root { --line-input-width: 82px; }

.line-discount { width: var(--line-input-width); min-height: 38px; padding-left: 6px; padding-right: 6px; }
.line-qty { width: var(--line-input-width); min-height: 38px; font-size: 15px; font-weight: 600; text-align: center; padding-left: 6px; padding-right: 6px; }
.line-price { width: 96px; min-height: 38px; font-size: 14px; font-weight: 600; text-align: right; padding-left: 6px; padding-right: 6px; }

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
.order-list-panel .table tbody td:nth-child(3) { width: 17%; }
.order-list-panel .table thead th:nth-child(4),
.order-list-panel .table tbody td:nth-child(4) { width: 18%; }
.order-list-panel .table thead th:nth-child(5),
.order-list-panel .table tbody td:nth-child(5) { width: 18%; }

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

    .line-price {
        width: 84px;
        min-height: 36px;
    }
}

.touch-keypad {
    position: fixed;
    right: 20px;
    bottom: 20px;
    z-index: 1045;
    width: min(320px, calc(100vw - 40px));
    border: 1px solid #dee2e6;
    border-radius: 18px;
    background: rgba(255, 255, 255, 0.98);
    box-shadow: 0 16px 40px rgba(33, 37, 41, 0.18);
    backdrop-filter: blur(10px);
}

.touch-keypad__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 12px 14px 8px;
    border-bottom: 1px solid #edf0f2;
    cursor: grab;
    user-select: none;
    touch-action: none;
}

.touch-keypad.dragging .touch-keypad__header {
    cursor: grabbing;
}

.touch-keypad__title {
    margin: 0;
    font-size: 0.82rem;
    font-weight: 700;
    letter-spacing: 0.03em;
    text-transform: uppercase;
    color: #495057;
}

.touch-keypad__field {
    margin: 0;
    font-size: 0.92rem;
    font-weight: 600;
    color: #212529;
}

.touch-keypad__display {
    padding: 10px 14px 0;
    font-size: 1.15rem;
    font-weight: 700;
    color: #fd7e14;
    text-align: right;
    min-height: 28px;
}

.touch-keypad__grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 8px;
    padding: 12px 14px 14px;
}

.touch-keypad__button {
    min-height: 48px;
    border: 1px solid #d9dee3;
    border-radius: 12px;
    background: #f8f9fa;
    color: #212529;
    font-size: 1rem;
    font-weight: 700;
    transition: transform .15s ease, background .15s ease, border-color .15s ease;
}

.touch-keypad__button:hover,
.touch-keypad__button:focus {
    background: #fff3e8;
    border-color: #fd7e14;
    box-shadow: none;
    outline: 0;
}

.touch-keypad__button:active {
    transform: translateY(1px);
}

.touch-keypad__button--accent {
    background: #fd7e14;
    border-color: #fd7e14;
    color: #fff;
}

.touch-keypad__button--accent:hover,
.touch-keypad__button--accent:focus {
    background: #e76f0c;
    border-color: #e76f0c;
    color: #fff;
}

.touch-keypad__button--wide {
    grid-column: span 2;
}

@media (max-width: 575.98px) {
    .touch-keypad {
        right: 12px;
        bottom: 12px;
        width: calc(100vw - 24px);
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
                        <h5 class="mb-1">{{ $refundOrder ? 'Point Of Sale - Refund Edit' : 'Point Of Sale' }}</h5>
                        <p class="mb-0">
                            {{ $refundOrder ? 'Editing sale #' . $refundOrder->order_number . ' in same POS screen' : 'Live inventory, per-item discount, and linked customers' }}
                        </p>
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
                                        <span class="pos-price-chip">PKR {{ number_format($product->selling_price, 0) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12"><div class="alert alert-warning mb-0">No active products found.</div></div>
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
                        <h3 class="mb-0">{{ $refundOrder ? 'Refund Sale Edit' : 'Order List' }}</h3>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-3 gap-2 flex-wrap">
                        <span class="badge badge-dark fs-10 fw-medium badge-xs" id="itemsCount">Items: 0</span>
                        <span class="badge bg-success" id="networkStatus">Online</span>
                        <span class="badge bg-warning text-dark" id="pendingSyncBadge">Pending Sync: 0</span>
                        @if($refundOrder)
                            <a href="{{ route('pos.index') }}" class="btn btn-sm btn-outline-secondary">Exit Refund Mode</a>
                        @endif
                        <button type="button" class="btn btn-sm btn-outline-danger" id="clearFormBtn">Clear Form</button>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="syncNowBtn">Sync Now</button>
                    </div>

                    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
                    @if($errors->any())
                        <div class="alert alert-danger"><ul class="mb-0 ps-3">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                    @endif

                    <form method="POST" action="{{ $refundOrder ? route('pos.orders.update', $refundOrder) : route('pos.checkout') }}" id="checkoutForm">
                        @csrf
                        @if($refundOrder)
                            @method('PUT')
                        @endif
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label mb-0">Customer</label>
                                <button type="button" class="btn btn-sm btn-light" id="openAddCustomerModalBtn">Add Customer</button>
                            </div>
                            <input type="text" id="customerSearch" class="form-control mb-2" placeholder="Search customer">
                            <select name="customer_id" id="customerSelect" class="form-control">
                                <option value="">Walk in Customer</option>
                                @foreach($customers as $customer)
                                    <option
                                        value="{{ $customer->id }}"
                                        data-search="{{ strtolower(trim($customer->full_name . ' ' . ($customer->phone ?? '') . ' ' . ($customer->company_name ?? ''))) }}"
                                        {{ old('customer_id', $refundOrder?->customer_id) == $customer->id ? 'selected' : '' }}
                                    >{{ $customer->full_name }}{{ $customer->phone ? ' - ' . $customer->phone : '' }}</option>
                                @endforeach
                            </select>
                            <input type="hidden" name="customer_name" value="{{ old('customer_name', $refundOrder?->customer_name ?: 'Walk in Customer') }}">
                        </div>

                        <div class="table-responsive">
                            <table class="table table-borderless">
                                <thead>
                                    <tr>
                                        <th class="fw-bold bg-light">Item</th>
                                        <th class="fw-bold bg-light">Qty</th>
                                        <th class="fw-bold bg-light">Price</th>
                                        <th class="fw-bold bg-light">Disc</th>
                                        <th class="fw-bold bg-light text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody id="cartBody"><tr><td colspan="5" class="text-center text-muted">No products selected</td></tr></tbody>
                            </table>
                        </div>

                        <table class="table table-borderless mb-3">
                            <tr><td>Sub Total</td><td class="text-end" id="subTotal">PKR 0</td></tr>
                            <tr><td>Line Discount</td><td class="text-end" id="discountTotal">PKR 0</td></tr>
                            <tr><td>Extra Discount</td><td class="text-end"><input type="text" inputmode="numeric" autocomplete="off" name="additional_discount" id="additionalDiscount" class="form-control form-control-sm text-center" value="{{ old('additional_discount', $refundOrder ? (int) round($refundAdditionalDiscount) : 0) }}" data-touch-input data-touch-field="additionalDiscount" data-touch-label="Extra Discount" data-allow-decimal="false"></td></tr>
                            <tr><td class="fw-bold border-top">Total Payable</td><td class="text-end fw-bold border-top" id="totalPayable">PKR 0</td></tr>
                            <tr><td>Received Amount</td><td class="text-end"><input type="text" inputmode="numeric" autocomplete="off" name="paid_amount" id="paidAmount" class="form-control form-control-sm text-center" value="{{ old('paid_amount', $refundOrder ? (int) round($refundOrder->received_amount ?? $refundOrder->paid_amount) : 0) }}" data-touch-input data-touch-field="paidAmount" data-touch-label="Received Amount" data-allow-decimal="false"></td></tr>
                            <tr><td class="fw-bold">Due Amount</td><td class="text-center fw-bold" id="dueAmount">PKR 0</td></tr>
                            <tr><td class="fw-bold">Return Amount</td><td class="text-end fw-bold text-success" id="returnAmount">PKR 0</td></tr>
                        </table>

                        <input type="hidden" name="invoice_date" value="{{ old('invoice_date', $refundOrder?->invoice_date?->toDateString() ?? now()->toDateString()) }}">

                        <div class="mb-3">
                            <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                            <select name="payment_method" id="paymentMethod" class="form-control" required>
                                @php($posPaymentMethod = old('payment_method', $refundOrder?->payment_method ?: 'cash'))
                                <option value="cash" {{ $posPaymentMethod === 'cash' ? 'selected' : '' }}>Cash</option>
                                <option value="cheque" {{ $posPaymentMethod === 'cheque' ? 'selected' : '' }}>Cheque</option>
                                <option value="pay_later" {{ $posPaymentMethod === 'pay_later' ? 'selected' : '' }}>Pay Later</option>
                            </select>
                        </div>

                        <div id="hiddenItems"></div>
                        <button type="submit" class="btn btn-secondary w-100" id="checkoutBtn" disabled>{{ $refundOrder ? 'Update Sale' : 'Complete Checkout' }}</button>
                    </form>
                </div>
            </div>
        </aside>
    </div>
</div>

<div class="touch-keypad shadow-sm" id="touchKeypad" aria-label="Touch numeric keypad">
    <div class="touch-keypad__header" data-touch-drag-handle>
        <div>
            <p class="touch-keypad__title mb-1">Touch Keypad</p>
            <p class="touch-keypad__field" id="touchKeypadField">Ready</p>
        </div>
        <span class="badge bg-light text-dark">Numeric</span>
    </div>
    <div class="touch-keypad__display" id="touchKeypadDisplay">0</div>
    <div class="touch-keypad__grid">
        <button type="button" class="touch-keypad__button" data-keypad-action="digit" data-keypad-value="7">7</button>
        <button type="button" class="touch-keypad__button" data-keypad-action="digit" data-keypad-value="8">8</button>
        <button type="button" class="touch-keypad__button" data-keypad-action="digit" data-keypad-value="9">9</button>
        <button type="button" class="touch-keypad__button touch-keypad__button--accent" data-keypad-action="backspace">Bksp</button>

        <button type="button" class="touch-keypad__button" data-keypad-action="digit" data-keypad-value="4">4</button>
        <button type="button" class="touch-keypad__button" data-keypad-action="digit" data-keypad-value="5">5</button>
        <button type="button" class="touch-keypad__button" data-keypad-action="digit" data-keypad-value="6">6</button>
        <button type="button" class="touch-keypad__button" data-keypad-action="clear">Clear</button>

        <button type="button" class="touch-keypad__button" data-keypad-action="digit" data-keypad-value="1">1</button>
        <button type="button" class="touch-keypad__button" data-keypad-action="digit" data-keypad-value="2">2</button>
        <button type="button" class="touch-keypad__button" data-keypad-action="digit" data-keypad-value="3">3</button>
        <button type="button" class="touch-keypad__button" data-keypad-action="next">Next</button>

        <button type="button" class="touch-keypad__button touch-keypad__button--wide" data-keypad-action="digit" data-keypad-value="0">0</button>
        <button type="button" class="touch-keypad__button" data-keypad-action="digit" data-keypad-value=".">.</button>
        <button type="button" class="touch-keypad__button touch-keypad__button--accent" data-keypad-action="submit">-></button>
    </div>
</div>

<div class="modal fade" id="addCustomerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Add Customer</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form method="POST" action="{{ route('customers.store') }}" id="addCustomerForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label">Full Name *</label><input type="text" name="full_name" class="form-control" required></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Company Name</label><input type="text" name="company_name" class="form-control"></div>
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
    const OFFLINE_QUEUE_KEY = 'posOfflineOrdersQueueV1';
    const csrfToken = '{{ csrf_token() }}';
    const syncEndpoint = '{{ route('pos.checkout.sync') }}';
    const isRefundMode = {{ $refundOrder ? 'true' : 'false' }};
    const refundOrderData = @json($refundOrderData);
    const cart = new Map();
    let activeTouchFieldKey = null;
    const checkoutForm = document.getElementById('checkoutForm');
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
    const networkStatusEl = document.getElementById('networkStatus');
    const pendingSyncBadgeEl = document.getElementById('pendingSyncBadge');
    const syncNowBtn = document.getElementById('syncNowBtn');
    const clearFormBtn = document.getElementById('clearFormBtn');
    const addCustomerBtn = document.getElementById('openAddCustomerModalBtn');
    const touchKeypadEl = document.getElementById('touchKeypad');
    const touchKeypadDisplayEl = document.getElementById('touchKeypadDisplay');
    const touchKeypadFieldEl = document.getElementById('touchKeypadField');
    const addCustomerForm = document.getElementById('addCustomerForm');

    function money(value) { return 'PKR ' + Number(value).toFixed(0); }

    function sanitizeNumericValue(value, allowDecimal = true) {
        let text = String(value ?? '').replace(/[^0-9.]/g, '');
        if (!allowDecimal) {
            return text.replace(/\./g, '');
        }

        const parts = text.split('.');
        if (parts.length === 1) {
            return parts[0];
        }

        return parts.shift() + '.' + parts.join('');
    }

    function parseNumericValue(value, fallback = 0, allowDecimal = true) {
        const sanitized = sanitizeNumericValue(value, allowDecimal);
        if (sanitized === '' || sanitized === '.') {
            return fallback;
        }

        const parsed = allowDecimal ? Number.parseFloat(sanitized) : Number.parseInt(sanitized, 10);
        return Number.isFinite(parsed) ? parsed : fallback;
    }

    function updateTouchKeypadDisplay() {
        if (!touchKeypadDisplayEl) {
            return;
        }

        if (!activeTouchFieldKey) {
            touchKeypadDisplayEl.textContent = '0';
            if (touchKeypadFieldEl) {
                touchKeypadFieldEl.textContent = 'Ready';
            }
            return;
        }

        const activeInput = document.querySelector('[data-touch-field="' + activeTouchFieldKey + '"]');
        if (!activeInput) {
            touchKeypadDisplayEl.textContent = '0';
            if (touchKeypadFieldEl) {
                touchKeypadFieldEl.textContent = 'Ready';
            }
            return;
        }

        touchKeypadDisplayEl.textContent = activeInput.value || '0';
        if (touchKeypadFieldEl) {
            touchKeypadFieldEl.textContent = activeInput.dataset.touchLabel || activeInput.name || 'Numeric field';
        }
    }

    function setActiveTouchInput(input) {
        if (!input) {
            activeTouchFieldKey = null;
            updateTouchKeypadDisplay();
            return;
        }

        activeTouchFieldKey = input.dataset.touchField || null;
        updateTouchKeypadDisplay();
    }

    function restoreTouchFocus() {
        if (!activeTouchFieldKey) {
            updateTouchKeypadDisplay();
            return;
        }

        const activeInput = document.querySelector('[data-touch-field="' + activeTouchFieldKey + '"]');
        if (!activeInput) {
            activeTouchFieldKey = null;
            updateTouchKeypadDisplay();
            return;
        }

        activeInput.focus();
        try {
            const end = String(activeInput.value || '').length;
            activeInput.setSelectionRange(end, end);
        } catch (e) {
        }

        updateTouchKeypadDisplay();
    }

    function getTouchInputs() {
        return Array.from(document.querySelectorAll('[data-touch-input]')).filter((input) => !input.disabled);
    }

    function focusTouchInputByIndex(startIndex, step) {
        const inputs = getTouchInputs();
        if (inputs.length === 0) {
            return;
        }

        const currentIndex = Math.max(0, startIndex);
        const nextIndex = (currentIndex + step + inputs.length) % inputs.length;
        const nextInput = inputs[nextIndex];
        nextInput.focus();
        setActiveTouchInput(nextInput);
    }

    function focusNextTouchInput() {
        const inputs = getTouchInputs();
        if (inputs.length === 0) {
            return;
        }

        const currentIndex = Math.max(0, inputs.findIndex((input) => input.dataset.touchField === activeTouchFieldKey));
        focusTouchInputByIndex(currentIndex, 1);
    }

    function setupDraggableKeypad() {
        if (!touchKeypadEl) {
            return;
        }

        const dragHandle = touchKeypadEl.querySelector('[data-touch-drag-handle]');
        if (!dragHandle) {
            return;
        }

        let dragging = false;
        let startX = 0;
        let startY = 0;
        let initialLeft = 0;
        let initialTop = 0;

        function clamp(value, min, max) {
            return Math.min(max, Math.max(min, value));
        }

        function onPointerMove(event) {
            if (!dragging) {
                return;
            }

            const nextLeft = initialLeft + (event.clientX - startX);
            const nextTop = initialTop + (event.clientY - startY);
            const maxLeft = Math.max(0, window.innerWidth - touchKeypadEl.offsetWidth);
            const maxTop = Math.max(0, window.innerHeight - touchKeypadEl.offsetHeight);

            touchKeypadEl.style.left = clamp(nextLeft, 0, maxLeft) + 'px';
            touchKeypadEl.style.top = clamp(nextTop, 0, maxTop) + 'px';
            touchKeypadEl.style.right = 'auto';
            touchKeypadEl.style.bottom = 'auto';
        }

        function onPointerUp() {
            if (!dragging) {
                return;
            }

            dragging = false;
            touchKeypadEl.classList.remove('dragging');
            window.removeEventListener('pointermove', onPointerMove);
            window.removeEventListener('pointerup', onPointerUp);
            window.removeEventListener('pointercancel', onPointerUp);
        }

        dragHandle.addEventListener('pointerdown', function (event) {
            if (event.button !== undefined && event.button !== 0) {
                return;
            }

            event.preventDefault();
            dragging = true;
            touchKeypadEl.classList.add('dragging');

            const rect = touchKeypadEl.getBoundingClientRect();
            startX = event.clientX;
            startY = event.clientY;
            initialLeft = rect.left;
            initialTop = rect.top;

            touchKeypadEl.style.left = rect.left + 'px';
            touchKeypadEl.style.top = rect.top + 'px';
            touchKeypadEl.style.right = 'auto';
            touchKeypadEl.style.bottom = 'auto';

            window.addEventListener('pointermove', onPointerMove);
            window.addEventListener('pointerup', onPointerUp);
            window.addEventListener('pointercancel', onPointerUp);
        });
    }

    function syncTouchInputValue(input) {
        if (!input || !input.dataset.touchField) {
            return;
        }

        const fieldKey = input.dataset.touchField;
        const fieldType = input.dataset.fieldType || '';
        const allowDecimal = input.dataset.allowDecimal !== 'false';

        if (fieldKey === 'additionalDiscount' || fieldKey === 'paidAmount') {
            const parsedValue = parseNumericValue(input.value, 0, false);
            input.value = String(parsedValue);
            renderCart();
            return;
        }

        const lineKey = input.dataset.lineKey;
        if (!lineKey || !cart.has(lineKey)) {
            return;
        }

        const rawValue = String(input.value || '').trim();
        if (fieldType === 'qty' && rawValue === '') {
            updateTouchKeypadDisplay();
            return;
        }

        const item = cart.get(lineKey);

        if (fieldType === 'qty') {
            let quantity = parseNumericValue(input.value, 1, false);
            quantity = Math.max(1, Math.floor(quantity));
            item.quantity = quantity;
            input.value = String(quantity);
        } else if (fieldType === 'price') {
            let price = parseNumericValue(input.value, item.price, false);
            price = Math.max(0, price);
            item.price = price;
            input.value = String(price);
        } else if (fieldType === 'discount') {
            let discount = parseNumericValue(input.value, 0, false);
            const maxDiscount = Math.max(0, item.price * item.quantity);
            discount = Math.min(Math.max(0, discount), maxDiscount);
            item.discount = discount;
            input.value = String(discount);
        }

        renderCart();
    }

    function updateFieldFromKeypad(action, value) {
        const activeInput = activeTouchFieldKey ? document.querySelector('[data-touch-field="' + activeTouchFieldKey + '"]') : null;
        if (!activeInput) {
            return;
        }

        const activeFieldType = activeInput.dataset.fieldType || '';

        if (action === 'submit') {
            if (!checkoutBtn.disabled) {
                checkoutBtn.click();
            }
            return;
        }

        if (action === 'next') {
            focusNextTouchInput();
            return;
        }

        if (action === 'clear') {
            activeInput.value = activeFieldType === 'qty' ? '' : '0';
        } else if (action === 'backspace') {
            if (activeInput.value.length > 1) {
                activeInput.value = activeInput.value.slice(0, -1);
            } else {
                activeInput.value = activeFieldType === 'qty' ? '' : '0';
            }
        } else if (action === 'digit') {
            const current = activeInput.value === '0' ? '' : activeInput.value;
            if (value === '.' && current.includes('.')) {
                return;
            }

            activeInput.value = sanitizeNumericValue(current + value, activeInput.dataset.allowDecimal !== 'false');
        }

        syncTouchInputValue(activeInput);
    }

    function getQueue() {
        try {
            const raw = localStorage.getItem(OFFLINE_QUEUE_KEY);
            const parsed = raw ? JSON.parse(raw) : [];
            return Array.isArray(parsed) ? parsed : [];
        } catch (e) {
            return [];
        }
    }

    function saveQueue(queue) {
        localStorage.setItem(OFFLINE_QUEUE_KEY, JSON.stringify(queue));
        updatePendingBadge();
    }

    function updatePendingBadge() {
        const pending = getQueue().length;
        pendingSyncBadgeEl.textContent = 'Pending Sync: ' + pending;
    }

    function updateNetworkStatus() {
        if (navigator.onLine) {
            networkStatusEl.textContent = 'Online';
            networkStatusEl.classList.remove('bg-danger');
            networkStatusEl.classList.add('bg-success');
            syncNowBtn.disabled = false;
        } else {
            networkStatusEl.textContent = 'Offline';
            networkStatusEl.classList.remove('bg-success');
            networkStatusEl.classList.add('bg-danger');
            syncNowBtn.disabled = true;
        }
    }

    function buildCheckoutPayload() {
        const items = [];
        cart.forEach((item) => {
            items.push({
                product_id: item.id,
                quantity: item.quantity,
                unit_price: Number(item.price || 0),
                discount: Number(item.discount || 0),
            });
        });

        return {
            customer_id: customerSelectEl.value || null,
            customer_name: customerSelectEl.value ? '' : 'Walk in Customer',
            payment_method: paymentMethodEl.value,
            invoice_date: checkoutForm.querySelector('input[name="invoice_date"]').value,
            additional_discount: Number(additionalDiscountEl.value || 0),
            paid_amount: Number(paidAmountEl.value || 0),
            items,
        };
    }

    function resetCheckoutForm() {
        cart.clear();
        additionalDiscountEl.value = '0';
        paidAmountEl.value = '0';
        paymentMethodEl.value = 'cash';
        customerSelectEl.value = '';
        if (customerSearch) {
            customerSearch.value = '';
        }
        activeTouchFieldKey = null;
        renderCart();
    }

    function validateOfflinePayload(payload) {
        if (!payload.items || payload.items.length === 0) {
            return 'Add at least one item before checkout.';
        }
        if (!payload.customer_id && payload.payment_method === 'pay_later') {
            return 'Walk in customer cannot use Pay Later.';
        }

        const subtotal = payload.items.reduce((sum, line) => {
            const gross = Number(line.unit_price || 0) * Number(line.quantity || 0);
            const discount = Math.min(Number(line.discount || 0), gross);
            return sum + (gross - discount);
        }, 0);

        const total = Math.max(0, subtotal - Number(payload.additional_discount || 0));
        if (!payload.customer_id && payload.payment_method !== 'pay_later' && Number(payload.paid_amount || 0) < total) {
            return 'Walk in customer must pay full amount.';
        }

        return null;
    }

    async function syncOfflineOrders() {
        if (!navigator.onLine) {
            return;
        }

        const queue = getQueue();
        if (queue.length === 0) {
            return;
        }

        syncNowBtn.disabled = true;
        syncNowBtn.textContent = 'Syncing...';

        const remaining = [];
        for (const queued of queue) {
            try {
                const response = await fetch(syncEndpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(queued.payload),
                });

                if (!response.ok) {
                    remaining.push(queued);
                }
            } catch (e) {
                remaining.push(queued);
            }
        }

        saveQueue(remaining);
        syncNowBtn.textContent = 'Sync Now';
        syncNowBtn.disabled = !navigator.onLine;
    }

    function renderCart() {
        cartBody.innerHTML = '';
        hiddenItems.innerHTML = '';
        let subtotal = 0;
        let discountTotal = 0;
        let payableBeforeHeader = 0;
        let index = 0;

        if (cart.size === 0) {
            cartBody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No products selected</td></tr>';
            subTotalEl.textContent = money(0);
            discountTotalEl.textContent = money(0);
            totalPayableEl.textContent = money(0);
            dueAmountEl.textContent = money(0);
            returnAmountEl.textContent = money(0);
            itemsCount.textContent = 'Items: 0';
            checkoutBtn.disabled = true;
            restoreTouchFocus();
            return;
        }

        cart.forEach((item, key) => {
            const gross = item.price * item.quantity;
            let discount = Number(item.discount || 0);
            if (discount < 0) { discount = 0; }
            if (discount > gross) { discount = gross; }
            item.discount = discount;
            const lineTotal = gross - discount;
            subtotal += gross;
            discountTotal += discount;
            payableBeforeHeader += lineTotal;

            const priceFieldKey = 'price-' + key;
            const qtyFieldKey = 'qty-' + key;
            const discountFieldKey = 'discount-' + key;

            const row = document.createElement('tr');
            row.innerHTML = `
                <td><a href="javascript:void(0);" data-remove="${key}" class="me-2"><i class="ti ti-trash"></i></a>${item.name}<br><small class="text-muted">${item.unit}</small></td>
                <td><input type="text" inputmode="numeric" autocomplete="off" value="${item.quantity}" class="form-control line-qty" data-touch-input data-touch-field="${qtyFieldKey}" data-touch-label="${item.name} quantity" data-line-key="${key}" data-field-type="qty" data-allow-decimal="false"></td>
                <td><input type="text" inputmode="numeric" autocomplete="off" value="${Math.round(Number(item.price))}" class="form-control form-control-sm line-price text-center" data-touch-input data-touch-field="${priceFieldKey}" data-touch-label="${item.name} price" data-line-key="${key}" data-field-type="price" data-allow-decimal="false"></td>
                <td><input type="text" inputmode="numeric" autocomplete="off" value="${Math.round(Number(item.discount))}" class="form-control form-control-sm line-discount text-center" data-touch-input data-touch-field="${discountFieldKey}" data-touch-label="${item.name} discount" data-line-key="${key}" data-field-type="discount" data-allow-decimal="false"></td>
                <td class="text-end">${money(lineTotal)}</td>`;
            cartBody.appendChild(row);

            hiddenItems.insertAdjacentHTML('beforeend',
                '<input type="hidden" name="items[' + index + '][product_id]" value="' + item.id + '">' +
                '<input type="hidden" name="items[' + index + '][quantity]" value="' + item.quantity + '">' +
                '<input type="hidden" name="items[' + index + '][unit_price]" value="' + Math.round(Number(item.price)) + '">' +
                '<input type="hidden" name="items[' + index + '][discount]" value="' + Math.round(Number(item.discount)) + '">'
            );
            index++;
        });

        const extraDiscount = Math.max(0, Number(additionalDiscountEl.value || 0));
        const totalPayable = Math.max(0, payableBeforeHeader - extraDiscount);
        let paid = Number(paidAmountEl.value || 0);
        if (!Number.isFinite(paid) || paid < 0) { paid = 0; }

        if (paymentMethodEl.value === 'pay_later') {
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

        restoreTouchFocus();
    }

    [additionalDiscountEl, paidAmountEl].forEach((el) => {
        el.addEventListener('focus', function () { setActiveTouchInput(this); });
        el.addEventListener('click', function () { setActiveTouchInput(this); });
        el.addEventListener('input', function () { syncTouchInputValue(this); });
    });

    cartBody.addEventListener('focusin', function (event) {
        const touchInput = event.target.closest('[data-touch-input]');
        if (touchInput) { setActiveTouchInput(touchInput); }
    });

    cartBody.addEventListener('click', function (event) {
        const touchInput = event.target.closest('[data-touch-input]');
        if (touchInput) { setActiveTouchInput(touchInput); }

        const removeBtn = event.target.closest('[data-remove]');
        if (removeBtn) {
            cart.delete(removeBtn.dataset.remove);
            renderCart();
        }
    });

    cartBody.addEventListener('change', function (event) {
        const touchInput = event.target.closest('[data-touch-input]');
        if (touchInput) {
            syncTouchInputValue(touchInput);
            setActiveTouchInput(touchInput);
        }
    });

    cartBody.addEventListener('input', function (event) {
        const touchInput = event.target.closest('[data-touch-input]');
        if (touchInput) {
            syncTouchInputValue(touchInput);
            setActiveTouchInput(touchInput);
        }
    });

    paymentMethodEl.addEventListener('change', function () {
        if (this.value === 'pay_later' && !customerSelectEl.value) {
            alert('Select a customer first. Walk in customer cannot use Pay Later.');
            this.value = 'cash';
        }
        renderCart();
    });

    function addCardItem(cardEl) {
        const id = Number(cardEl.dataset.id);
        const key = String(id);
        const stock = Number(cardEl.dataset.stock);

        if (cart.has(key)) {
            const existing = cart.get(key);
            existing.quantity += 1;
        } else {
            cart.set(key, {
                id,
                name: cardEl.dataset.name,
                price: Number(cardEl.dataset.price),
                quantity: 1,
                stock,
                unit: cardEl.dataset.unit || 'pcs',
                discount: 0,
            });
        }
        renderCart();
    }

    document.querySelectorAll('.add-to-cart').forEach((card) => {
        card.addEventListener('click', function () { addCardItem(this); });
        card.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                addCardItem(this);
            }
        });
    });

    if (touchKeypadEl) {
        touchKeypadEl.addEventListener('click', function (event) {
            const button = event.target.closest('[data-keypad-action]');
            if (!button) {
                return;
            }
            updateFieldFromKeypad(button.dataset.keypadAction, button.dataset.keypadValue || '');
        });
    }

    if (addCustomerBtn) {
        addCustomerBtn.addEventListener('click', function () {
            const modalEl = document.getElementById('addCustomerModal');
            if (!modalEl || !window.bootstrap || !window.bootstrap.Modal) {
                return;
            }

            const modal = window.bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();

            const firstField = modalEl.querySelector('input[name="full_name"]');
            if (firstField) {
                setTimeout(function () {
                    firstField.focus();
                }, 150);
            }
        });
    }

    const productSearchEl = document.getElementById('productSearch');
    if (productSearchEl) {
        productSearchEl.addEventListener('input', function () {
            const term = this.value.toLowerCase().trim();
            document.querySelectorAll('.product-card').forEach((card) => {
                card.style.display = (card.dataset.name || '').includes(term) ? '' : 'none';
            });
        });
    }

    const customerSearch = document.getElementById('customerSearch');
    const customerSelect = document.getElementById('customerSelect');
    let customerOptions = [];

    function buildCustomerOptionModel(option) {
        return {
            value: option.value,
            label: option.text,
            search: (option.dataset.search || option.text || '').toLowerCase(),
        };
    }

    function renderCustomerOptions(term = '', selectedValue = customerSelect ? customerSelect.value : '') {
        if (!customerSelect) {
            return;
        }

        const filtered = customerOptions.filter((item) => {
            if (!term) { return true; }
            if (!item.value) { return true; }
            return item.search.includes(term);
        });

        customerSelect.innerHTML = '';
        filtered.forEach((item) => {
            const option = document.createElement('option');
            option.value = item.value;
            option.textContent = item.label;
            if (item.value === selectedValue) {
                option.selected = true;
            }
            customerSelect.appendChild(option);
        });

        if (filtered.length === 0) {
            const emptyOption = document.createElement('option');
            emptyOption.value = '';
            emptyOption.textContent = 'No customer found';
            customerSelect.appendChild(emptyOption);
        }
    }

    if (customerSelect) {
        customerOptions = Array.from(customerSelect.options).map(buildCustomerOptionModel);
    }

    if (customerSearch && customerSelect) {
        customerSearch.addEventListener('input', function () {
            renderCustomerOptions(this.value.toLowerCase().trim(), customerSelect.value);
        });
    }

    async function submitOnlineCheckout(payload) {
        const response = await fetch(syncEndpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
            },
            credentials: 'same-origin',
            body: JSON.stringify(payload),
        });

        const data = await response.json().catch(() => ({}));
        if (!response.ok || !data.ok) {
            throw new Error(data.message || 'Unable to complete checkout.');
        }

        return data;
    }

    checkoutForm.addEventListener('submit', async function (event) {
        if (isRefundMode) {
            checkoutBtn.disabled = true;
            return;
        }

        event.preventDefault();

        const payload = buildCheckoutPayload();
        const validationError = validateOfflinePayload(payload);
        if (validationError) {
            alert(validationError);
            return;
        }

        checkoutBtn.disabled = true;

        if (!navigator.onLine) {
            const queue = getQueue();
            queue.push({
                local_id: Date.now(),
                payload,
                queued_at: new Date().toISOString(),
            });
            saveQueue(queue);
            resetCheckoutForm();
            alert('Internet is offline. Order saved locally and will sync automatically when connection returns.');
            return;
        }

        try {
            const result = await submitOnlineCheckout(payload);
            const receiptUrl = '{{ url('pos/orders') }}/' + result.order_id;
            window.location.href = receiptUrl;
        } catch (error) {
            alert(error.message || 'Checkout failed. Try again.');
            checkoutBtn.disabled = cart.size === 0;
        }
    });

    function initializeRefundOrderCart() {
        if (!isRefundMode || !refundOrderData || !Array.isArray(refundOrderData.items)) {
            return;
        }

        const productCardMap = new Map();
        document.querySelectorAll('.add-to-cart').forEach((card) => {
            productCardMap.set(String(card.dataset.id), card);
        });

        cart.clear();
        refundOrderData.items.forEach((item) => {
            const key = String(item.product_id);
            const card = productCardMap.get(key);

            cart.set(key, {
                id: Number(item.product_id),
                name: item.product_name,
                price: Math.round(Number(item.unit_price || 0)),
                quantity: Math.max(1, Number(item.quantity || 1)),
                stock: Number(card ? (card.dataset.stock || 0) : 0),
                unit: card ? (card.dataset.unit || 'pcs') : 'pcs',
                discount: Math.round(Number(item.discount_amount || 0)),
            });
        });

        if (refundOrderData.payment_method && paymentMethodEl) {
            paymentMethodEl.value = refundOrderData.payment_method;
        }

        if (refundOrderData.customer_id && customerSelectEl) {
            customerSelectEl.value = String(refundOrderData.customer_id);
        }

        renderCart();
    }

    if (clearFormBtn) {
        clearFormBtn.addEventListener('click', function () {
            resetCheckoutForm();
        });
    }

    if (addCustomerForm) {
        addCustomerForm.addEventListener('submit', async function (event) {
            event.preventDefault();
            const submitButton = addCustomerForm.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
            }

            try {
                const response = await fetch(addCustomerForm.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    credentials: 'same-origin',
                    body: new FormData(addCustomerForm),
                });

                const data = await response.json().catch(() => ({}));
                if (!response.ok || !data.ok || !data.customer) {
                    throw new Error(data.message || 'Unable to save customer.');
                }

                const customer = data.customer;
                const label = customer.company_name
                    ? (customer.full_name + ' - ' + customer.company_name)
                    : customer.full_name;
                const search = (customer.full_name + ' ' + (customer.company_name || '') + ' ' + (customer.phone || '')).toLowerCase().trim();

                customerOptions = customerOptions.filter((item) => String(item.value) !== String(customer.id));
                customerOptions.push({
                    value: String(customer.id),
                    label,
                    search,
                });

                if (customerSearch) {
                    renderCustomerOptions(customerSearch.value.toLowerCase().trim(), String(customer.id));
                } else {
                    renderCustomerOptions('', String(customer.id));
                }

                addCustomerForm.reset();

                const addCustomerModalEl = document.getElementById('addCustomerModal');
                if (addCustomerModalEl && window.bootstrap && window.bootstrap.Modal) {
                    const modalInstance = window.bootstrap.Modal.getOrCreateInstance(addCustomerModalEl);
                    modalInstance.hide();
                }
            } catch (error) {
                alert(error.message || 'Unable to save customer.');
            } finally {
                if (submitButton) {
                    submitButton.disabled = false;
                }
            }
        });
    }

    syncNowBtn.addEventListener('click', syncOfflineOrders);
    window.addEventListener('online', function () {
        updateNetworkStatus();
        syncOfflineOrders();
    });
    window.addEventListener('offline', updateNetworkStatus);

    setupDraggableKeypad();
    initializeRefundOrderCart();
    updateNetworkStatus();
    updatePendingBadge();
    if (navigator.onLine) {
        syncOfflineOrders();
    }
})();
</script>
@endsection

