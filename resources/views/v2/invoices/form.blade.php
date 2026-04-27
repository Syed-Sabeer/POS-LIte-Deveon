@extends('layouts.app.master')

@php
    $isPurchase = isset($type) && $type === 'purchase';
    $invoice = $invoice ?? null;
    $defaultAccountType = $isPurchase ? 'payable' : 'receivable';

    $itemsData = $items
        ->map(function ($item) use ($isPurchase) {
            return [
                'id' => $item->id,
                'code' => $item->code,
                'name' => $item->description,
                'rate' => (float) ($isPurchase ? $item->cost : $item->retail_rate),
            ];
        })
        ->values()
        ->all();

    $oldLinesData = old('items');
    if ($oldLinesData === null) {
        $oldLinesData = $invoice?->items
            ?->map(function ($line) {
                return [
                    'item_id' => $line->item_id,
                    'item_detail' => $line->item_detail,
                    'qty' => $line->qty,
                    'packet' => $line->packet,
                    'rate' => $line->rate,
                    'discount' => $line->discount,
                ];
            })
            ->values()
            ->all() ?? [];
    }
@endphp
@section('title', ($invoice ? 'Edit ' : 'New ') . ($isPurchase ? 'Purchase Invoice' : 'Sale Invoice'))
@section('css')@include('v2.partials.style')@endsection

@section('content')
<div class="v2-wrap">
    <div class="page-header"><div class="page-title v2-title"><h4 class="fw-bold">{{ $invoice ? 'Edit' : 'New' }} {{ $isPurchase ? 'Purchase Invoice' : 'Sale Invoice' }}</h4></div><a href="{{ route($isPurchase ? 'v2.purchase.index' : 'v2.sales.index') }}" class="btn btn-secondary">List</a></div>
    @include('v2.partials.messages')
    <form method="POST" action="{{ $invoice ? route($isPurchase ? 'v2.purchase.update' : 'v2.sales.update', $invoice) : route($isPurchase ? 'v2.purchase.store' : 'v2.sales.store') }}" id="v2InvoiceForm">
        @csrf
        @if($invoice) @method('PUT') @endif
        <div class="card mb-3"><div class="card-body row g-3">
            <div class="col-md-4">
                <label class="form-label d-flex justify-content-between align-items-center">
                    <span>{{ $isPurchase ? 'Supplier Account' : 'Customer Account' }} *</span>
                    <button type="button" class="btn btn-link btn-sm p-0" id="newAccountBtn">Add new</button>
                </label>
                <select name="account_id" id="accountSelect" class="form-control" required>@foreach($accounts as $account)<option value="{{ $account->id }}" @selected(old('account_id', $invoice?->account_id)==$account->id)>{{ $account->code }} - {{ $account->name }}</option>@endforeach</select>
            </div>
            <div class="col-md-3"><label class="form-label">{{ $isPurchase ? 'Supplier Name' : 'Customer Name' }}</label><input name="party_name" value="{{ old('party_name', $invoice?->party_name) }}" class="form-control"></div>
            <div class="col-md-2"><label class="form-label">{{ $isPurchase ? 'Voucher No' : 'Invoice No' }}</label><input name="voucher_no" value="{{ old('voucher_no', $invoice?->voucher_no) }}" class="form-control" placeholder="Auto"></div>
            <div class="col-md-2"><label class="form-label">Date *</label><input type="date" name="invoice_date" value="{{ old('invoice_date', optional($invoice?->invoice_date)->toDateString() ?: now()->toDateString()) }}" class="form-control" required></div>
            <div class="col-md-1"><label class="form-label">Rate</label><input type="number" step="0.0001" name="currency_rate" value="{{ old('currency_rate', $invoice?->currency_rate ?? 1) }}" class="form-control"></div>
            <div class="col-md-12"><label class="form-label">Memo</label><input name="memo" value="{{ old('memo', $invoice?->memo) }}" class="form-control"></div>
        </div></div>

        <div class="card mb-3"><div class="card-header d-flex justify-content-between"><h5 class="mb-0">Items</h5><div class="d-flex gap-2"><button type="button" class="btn btn-outline-secondary btn-sm" id="newItemBtn">Add item</button><button type="button" class="btn btn-primary btn-sm" id="addLineBtn">Add Line</button></div></div>
            <div class="table-responsive"><table class="table table-bordered mb-0" id="itemLines"><thead class="table-light"><tr><th>S.No</th><th>Item</th><th>Item Detail</th><th>Qty</th><th>Packet</th><th>Rate</th><th>Discount</th><th>Amount</th><th></th></tr></thead><tbody></tbody></table></div>
        </div>

        <div class="card mb-3"><div class="card-body row g-3">
            <div class="col-md-3"><label class="form-label">Gross Amount</label><input id="grossAmount" class="form-control" readonly></div>
            <div class="col-md-3"><label class="form-label">Charges</label><input type="number" step="0.01" name="charges" id="charges" value="{{ old('charges', $invoice?->charges ?? 0) }}" class="form-control"></div>
            <div class="col-md-3"><label class="form-label">Discount</label><input type="number" step="0.01" name="discount" id="discount" value="{{ old('discount', $invoice?->discount ?? 0) }}" class="form-control"></div>
            <div class="col-md-3"><label class="form-label">Net Amount</label><input id="netAmount" class="form-control" readonly></div>
            @if(!$isPurchase)
                <div class="col-md-3"><label class="form-label">Received Amount</label><input type="number" step="0.01" name="received_amount" value="{{ old('received_amount', $invoice?->received_amount ?? 0) }}" class="form-control"></div>
            @endif
        </div></div>
        <div class="v2-actions"><button class="btn btn-primary">Save</button><a href="{{ route($isPurchase ? 'v2.purchase.index' : 'v2.sales.index') }}" class="btn btn-secondary">Back</a></div>
    </form>
</div>
@endsection

@section('modals')
<div class="modal fade" id="quickAccountModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add {{ $isPurchase ? 'Supplier' : 'Customer' }} Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="quickAccountForm">
                <div class="modal-body">
                    <div class="alert alert-danger d-none" id="quickAccountError"></div>
                    <input type="hidden" name="account_type" value="{{ $defaultAccountType }}">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Name *</label><input type="text" name="name" class="form-control" required></div>
                        <div class="col-md-3"><label class="form-label">Opening Date *</label><input type="date" name="opening_date" value="{{ now()->toDateString() }}" class="form-control" required></div>
                        <div class="col-md-3"><label class="form-label">Opening Amount</label><input type="number" step="0.01" name="opening_amount" value="0" class="form-control"></div>
                        <div class="col-md-3"><label class="form-label">Currency Rate</label><input type="number" step="0.0001" name="currency_rate" value="1" class="form-control"></div>
                        <div class="col-md-3 d-flex align-items-end"><div class="form-check"><input class="form-check-input" type="checkbox" name="is_active" value="1" id="quickAccountActive" checked><label class="form-check-label" for="quickAccountActive">Active</label></div></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="quickAccountSaveBtn">Save Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="quickItemModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="quickItemForm">
                <div class="modal-body">
                    <div class="alert alert-danger d-none" id="quickItemError"></div>
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Description *</label><input type="text" name="description" class="form-control" required></div>
                        <div class="col-md-3"><label class="form-label">Nick</label><input type="text" name="nick" class="form-control"></div>
                        <div class="col-md-3"><label class="form-label">Cost</label><input type="number" step="0.01" name="cost" class="form-control"></div>
                        <div class="col-md-3"><label class="form-label">Retail Rate</label><input type="number" step="0.01" name="retail_rate" class="form-control"></div>
                        <div class="col-md-3"><label class="form-label">Packing</label><input type="text" name="packing" class="form-control"></div>
                        <div class="col-md-3"><label class="form-label">Packet Qty</label><input type="number" step="0.001" name="packet_qty" value="0" class="form-control"></div>
                        <div class="col-md-3 d-flex align-items-end"><div class="form-check"><input class="form-check-input" type="checkbox" name="is_active" value="1" id="quickItemActive" checked><label class="form-check-label" for="quickItemActive">Active</label></div></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="quickItemSaveBtn">Save Item</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
(function(){
    const isPurchase = @json($isPurchase);
    let items = @json($itemsData);
    const oldLines = @json($oldLinesData);
    const tbody = document.querySelector('#itemLines tbody');
    const addBtn = document.getElementById('addLineBtn');
    const grossEl = document.getElementById('grossAmount');
    const chargesEl = document.getElementById('charges');
    const discountEl = document.getElementById('discount');
    const netEl = document.getElementById('netAmount');
    const accountSelect = document.getElementById('accountSelect');
    const newAccountBtn = document.getElementById('newAccountBtn');
    const quickAccountModal = new bootstrap.Modal(document.getElementById('quickAccountModal'));
    const quickItemModal = new bootstrap.Modal(document.getElementById('quickItemModal'));
    const quickAccountForm = document.getElementById('quickAccountForm');
    const quickItemForm = document.getElementById('quickItemForm');
    const quickAccountError = document.getElementById('quickAccountError');
    const quickItemError = document.getElementById('quickItemError');
    const csrfToken = document.querySelector('input[name="_token"]').value;
    const saveAccountUrl = @json(route('v2.accounts.store'));
    const saveItemUrl = @json(route('v2.items.store'));
    const newItemBtn = document.getElementById('newItemBtn');

    function optionHtml(selected) {
        return items.map(item => `<option value="${item.id}" data-rate="${item.rate}" ${String(selected)===String(item.id)?'selected':''}>${item.code} - ${item.name}</option>`).join('');
    }

    function itemRate(item) {
        return isPurchase ? Number(item.cost || 0) : Number(item.retail_rate || 0);
    }

    function appendItemToSelects(item) {
        items.push(item);
        Array.from(document.querySelectorAll('.item-select')).forEach(select => {
            if (!Array.from(select.options).some(option => String(option.value) === String(item.id))) {
                const option = document.createElement('option');
                option.value = item.id;
                option.dataset.rate = item.rate;
                option.textContent = `${item.code} - ${item.name}`;
                select.appendChild(option);
            }
        });
    }

    function appendAccountOption(account) {
        if (!Array.from(accountSelect.options).some(option => String(option.value) === String(account.id))) {
            const option = document.createElement('option');
            option.value = account.id;
            option.textContent = `${account.code} - ${account.name}`;
            accountSelect.appendChild(option);
        }
        accountSelect.value = account.id;
    }

    function errorText(payload, fallback) {
        if (payload?.message) {
            return payload.message;
        }

        if (payload?.errors) {
            return Object.values(payload.errors).flat().join(' ');
        }

        return fallback;
    }

    function addLine(data = {}) {
        const row = document.createElement('tr');
        row.innerHTML = `<td class="serial"></td><td><select name="items[][item_id]" class="form-control item-select" required>${optionHtml(data.item_id)}</select></td><td><input name="items[][item_detail]" value="${data.item_detail || ''}" class="form-control"></td><td><input type="number" step="0.001" name="items[][qty]" value="${data.qty || 1}" class="form-control calc" required></td><td><input type="number" step="0.001" name="items[][packet]" value="${data.packet || 0}" class="form-control"></td><td><input type="number" step="0.01" name="items[][rate]" value="${data.rate || ''}" class="form-control calc" required></td><td><input type="number" step="0.01" name="items[][discount]" value="${data.discount || 0}" class="form-control calc"></td><td class="line-amount text-end">0.00</td><td><button type="button" class="btn btn-sm btn-danger remove-line">X</button></td>`;
        tbody.appendChild(row);
        refreshNames();
        if (!data.rate) {
            const select = row.querySelector('.item-select');
            row.querySelector('input[name$="[rate]"]').value = select.options[select.selectedIndex]?.dataset.rate || 0;
        }
        calculate();
    }

    function refreshNames() {
        Array.from(tbody.rows).forEach((row, index) => {
            row.querySelector('.serial').textContent = index + 1;
            row.querySelectorAll('select,input').forEach(input => {
                input.name = input.name.replace(/items\[\]/, `items[${index}]`);
            });
        });
    }

    function calculate() {
        let gross = 0;
        Array.from(tbody.rows).forEach(row => {
            const qty = Number(row.querySelector('input[name$="[qty]"]').value || 0);
            const rate = Number(row.querySelector('input[name$="[rate]"]').value || 0);
            const disc = Number(row.querySelector('input[name$="[discount]"]').value || 0);
            const amount = qty * Math.max(0, rate - disc);
            row.querySelector('.line-amount').textContent = amount.toFixed(2);
            gross += amount;
        });
        const net = Math.max(0, gross + Number(chargesEl.value || 0) - Number(discountEl.value || 0));
        grossEl.value = gross.toFixed(2);
        netEl.value = net.toFixed(2);
    }

    tbody.addEventListener('input', calculate);
    tbody.addEventListener('change', function(e){
        if (e.target.classList.contains('item-select')) {
            const row = e.target.closest('tr');
            row.querySelector('input[name$="[rate]"]').value = e.target.options[e.target.selectedIndex]?.dataset.rate || 0;
        }
        calculate();
    });
    tbody.addEventListener('click', function(e){ if(e.target.classList.contains('remove-line')) { e.target.closest('tr').remove(); refreshNames(); calculate(); } });
    addBtn.addEventListener('click', () => addLine());
    chargesEl.addEventListener('input', calculate);
    discountEl.addEventListener('input', calculate);
    (oldLines.length ? oldLines : [{}]).forEach(addLine);

    if (newAccountBtn) {
        newAccountBtn.addEventListener('click', () => {
            quickAccountError.classList.add('d-none');
            quickAccountForm.reset();
            quickAccountForm.querySelector('input[name="account_type"]').value = @json($defaultAccountType);
            quickAccountForm.querySelector('input[name="opening_date"]').value = new Date().toISOString().slice(0, 10);
            quickAccountForm.querySelector('input[name="opening_amount"]').value = '0';
            quickAccountForm.querySelector('input[name="currency_rate"]').value = '1';
            document.getElementById('quickAccountActive').checked = true;
            quickAccountModal.show();
        });
    }

    if (newItemBtn) {
        newItemBtn.addEventListener('click', () => {
            quickItemError.classList.add('d-none');
            quickItemForm.reset();
            document.getElementById('quickItemActive').checked = true;
            quickItemModal.show();
        });
    }

    quickAccountForm.addEventListener('submit', async function(event) {
        event.preventDefault();
        quickAccountError.classList.add('d-none');

        const formData = new FormData(quickAccountForm);
        if (!formData.get('is_active')) {
            formData.set('is_active', '0');
        }

        try {
            const response = await fetch(saveAccountUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: formData,
            });

            const payload = await response.json();

            if (!response.ok) {
                throw payload;
            }

            appendAccountOption(payload.account);
            quickAccountModal.hide();
        } catch (error) {
            quickAccountError.textContent = errorText(error, 'Unable to save account.');
            quickAccountError.classList.remove('d-none');
        }
    });

    quickItemForm.addEventListener('submit', async function(event) {
        event.preventDefault();
        quickItemError.classList.add('d-none');

        const formData = new FormData(quickItemForm);
        if (!formData.get('is_active')) {
            formData.set('is_active', '0');
        }

        try {
            const response = await fetch(saveItemUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: formData,
            });

            const payload = await response.json();

            if (!response.ok) {
                throw payload;
            }

            const item = payload.item;
            appendItemToSelects({
                id: item.id,
                code: item.code,
                name: item.description,
                rate: itemRate(item),
                cost: item.cost,
                retail_rate: item.retail_rate,
            });

            const lastRow = tbody.lastElementChild;
            if (lastRow) {
                const select = lastRow.querySelector('.item-select');
                select.value = item.id;
                select.dispatchEvent(new Event('change', { bubbles: true }));
            }

            quickItemModal.hide();
        } catch (error) {
            quickItemError.textContent = errorText(error, 'Unable to save item.');
            quickItemError.classList.remove('d-none');
        }
    });
})();
</script>
@endsection
