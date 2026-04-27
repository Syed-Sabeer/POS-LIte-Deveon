@extends('layouts.app.master')

@php($isPurchase = $type === 'purchase')
@php
    $invoiceModel = isset($invoice) ? $invoice : null;

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
        $oldLinesData = $invoiceModel?->items
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
@section('title', ($invoiceModel ? 'Edit ' : 'New ') . ($isPurchase ? 'Purchase Invoice' : 'Sale Invoice'))
@section('css')@include('v2.partials.style')@endsection

@section('content')
<div class="v2-wrap">
    <div class="page-header"><div class="page-title v2-title"><h4 class="fw-bold">{{ $invoiceModel ? 'Edit' : 'New' }} {{ $isPurchase ? 'Purchase Invoice' : 'Sale Invoice' }}</h4></div><a href="{{ route($isPurchase ? 'v2.purchase.index' : 'v2.sales.index') }}" class="btn btn-secondary">List</a></div>
    @include('v2.partials.messages')
    <form method="POST" action="{{ $invoiceModel ? route($isPurchase ? 'v2.purchase.update' : 'v2.sales.update', $invoiceModel) : route($isPurchase ? 'v2.purchase.store' : 'v2.sales.store') }}" id="v2InvoiceForm">
        @csrf
        @if($invoiceModel) @method('PUT') @endif
        <div class="card mb-3"><div class="card-body row g-3">
            <div class="col-md-4"><label class="form-label">{{ $isPurchase ? 'Supplier Account' : 'Customer Account' }} *</label><select name="account_id" class="form-control" required>@foreach($accounts as $account)<option value="{{ $account->id }}" @selected(old('account_id', $invoiceModel?->account_id)==$account->id)>{{ $account->code }} - {{ $account->name }}</option>@endforeach</select></div>
            <div class="col-md-3"><label class="form-label">{{ $isPurchase ? 'Supplier Name' : 'Customer Name' }}</label><input name="party_name" value="{{ old('party_name', $invoiceModel?->party_name) }}" class="form-control"></div>
            <div class="col-md-2"><label class="form-label">{{ $isPurchase ? 'Voucher No' : 'Invoice No' }}</label><input name="voucher_no" value="{{ old('voucher_no', $invoiceModel?->voucher_no) }}" class="form-control" placeholder="Auto"></div>
            <div class="col-md-2"><label class="form-label">Date *</label><input type="date" name="invoice_date" value="{{ old('invoice_date', optional($invoiceModel?->invoice_date)->toDateString() ?: now()->toDateString()) }}" class="form-control" required></div>
            <div class="col-md-1"><label class="form-label">Rate</label><input type="number" step="0.0001" name="currency_rate" value="{{ old('currency_rate', $invoiceModel?->currency_rate ?? 1) }}" class="form-control"></div>
            <div class="col-md-12"><label class="form-label">Memo</label><input name="memo" value="{{ old('memo', $invoiceModel?->memo) }}" class="form-control"></div>
        </div></div>

        <div class="card mb-3"><div class="card-header d-flex justify-content-between"><h5 class="mb-0">Items</h5><button type="button" class="btn btn-primary btn-sm" id="addLineBtn">Add Line</button></div>
            <div class="table-responsive"><table class="table table-bordered mb-0" id="itemLines"><thead class="table-light"><tr><th>S.No</th><th>Item</th><th>Item Detail</th><th>Qty</th><th>Packet</th><th>Rate</th><th>Discount</th><th>Amount</th><th></th></tr></thead><tbody></tbody></table></div>
        </div>

        <div class="card mb-3"><div class="card-body row g-3">
            <div class="col-md-3"><label class="form-label">Gross Amount</label><input id="grossAmount" class="form-control" readonly></div>
            <div class="col-md-3"><label class="form-label">Charges</label><input type="number" step="0.01" name="charges" id="charges" value="{{ old('charges', $invoiceModel?->charges ?? 0) }}" class="form-control"></div>
            <div class="col-md-3"><label class="form-label">Discount</label><input type="number" step="0.01" name="discount" id="discount" value="{{ old('discount', $invoiceModel?->discount ?? 0) }}" class="form-control"></div>
            <div class="col-md-3"><label class="form-label">Net Amount</label><input id="netAmount" class="form-control" readonly></div>
            @if(!$isPurchase)
                <div class="col-md-3"><label class="form-label">Received Amount</label><input type="number" step="0.01" name="received_amount" value="{{ old('received_amount', $invoiceModel?->received_amount ?? 0) }}" class="form-control"></div>
            @endif
        </div></div>
        <div class="v2-actions"><button class="btn btn-primary">Save</button><a href="{{ route($isPurchase ? 'v2.purchase.index' : 'v2.sales.index') }}" class="btn btn-secondary">Back</a></div>
    </form>
</div>
@endsection

@section('script')
<script>
(function(){
    const items = @json($itemsData);
    const oldLines = @json($oldLinesData);
    const tbody = document.querySelector('#itemLines tbody');
    const addBtn = document.getElementById('addLineBtn');
    const grossEl = document.getElementById('grossAmount');
    const chargesEl = document.getElementById('charges');
    const discountEl = document.getElementById('discount');
    const netEl = document.getElementById('netAmount');

    function optionHtml(selected) {
        return items.map(item => `<option value="${item.id}" data-rate="${item.rate}" ${String(selected)===String(item.id)?'selected':''}>${item.code} - ${item.name}</option>`).join('');
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
})();
</script>
@endsection
