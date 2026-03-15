@csrf
<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">Supplier *</label>
        <select name="supplier_id" class="form-control" required>
            <option value="">Select supplier</option>
            @foreach($suppliers as $supplier)
                <option value="{{ $supplier->id }}" {{ (string) old('supplier_id', $invoice->supplier_id ?? '') === (string) $supplier->id ? 'selected' : '' }}>{{ $supplier->full_name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">Invoice Date *</label>
        <input type="date" name="invoice_date" class="form-control" required value="{{ old('invoice_date', isset($invoice) ? optional($invoice->invoice_date)->format('Y-m-d') : now()->toDateString()) }}">
    </div>
    <div class="col-md-2">
        <label class="form-label">Discount</label>
        <input type="number" step="0.01" min="0" name="discount_amount" id="discount_amount" class="form-control" value="{{ old('discount_amount', $invoice->discount_amount ?? 0) }}">
    </div>
    <div class="col-md-2">
        <label class="form-label">Tax</label>
        <input type="number" step="0.01" min="0" name="tax_amount" id="tax_amount" class="form-control" value="{{ old('tax_amount', $invoice->tax_amount ?? 0) }}">
    </div>
    <div class="col-md-1">
        <label class="form-label">Paid</label>
        <input type="number" step="0.01" min="0" name="paid_amount" class="form-control" value="{{ old('paid_amount', $invoice->paid_amount ?? 0) }}">
    </div>
</div>

<div class="table-responsive mt-3">
    <table class="table table-bordered" id="purchaseItemsTable">
        <thead class="table-light"><tr><th>Product</th><th width="150">Cost Price</th><th width="120">Qty</th><th width="150">Line Total</th><th width="80">Action</th></tr></thead>
        <tbody>
            @php $oldItems = old('items', isset($invoice) ? $invoice->items->map(fn($i)=>['product_id'=>$i->product_id,'cost_price'=>$i->cost_price,'quantity'=>$i->quantity])->toArray() : [['product_id'=>'','cost_price'=>0,'quantity'=>1]]); @endphp
            @foreach($oldItems as $idx => $item)
            <tr>
                <td>
                    <select name="items[{{ $idx }}][product_id]" class="form-control product-select" required>
                        <option value="">Select product</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" data-cost="{{ $product->cost_price }}" {{ (string)($item['product_id'] ?? '') === (string)$product->id ? 'selected' : '' }}>{{ $product->name }}</option>
                        @endforeach
                    </select>
                </td>
                <td><input type="number" step="0.01" min="0" name="items[{{ $idx }}][cost_price]" class="form-control cost-price" value="{{ $item['cost_price'] ?? 0 }}" required></td>
                <td><input type="number" min="1" name="items[{{ $idx }}][quantity]" class="form-control qty" value="{{ $item['quantity'] ?? 1 }}" required></td>
                <td class="line-total text-end">0.00</td>
                <td><button type="button" class="btn btn-sm btn-danger remove-row">X</button></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<button type="button" class="btn btn-light" id="addRowBtn">Add Row</button>
<div class="mt-3 row g-2">
    <div class="col-md-6"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2">{{ old('notes', $invoice->notes ?? '') }}</textarea></div>
    <div class="col-md-3"><label class="form-label">Save As</label><select name="status" class="form-control"><option value="draft">Draft</option><option value="posted">Posted</option></select></div>
    <div class="col-md-3"><label class="form-label">Total</label><input type="text" class="form-control" id="grandTotal" readonly></div>
</div>
<button class="btn btn-primary mt-3">Save Purchase Invoice</button>

@section('script')
<script>
(function(){
    const tableBody = document.querySelector('#purchaseItemsTable tbody');
    const addRowBtn = document.getElementById('addRowBtn');
    const grandTotalEl = document.getElementById('grandTotal');

    function recalc(){
        let subtotal = 0;
        tableBody.querySelectorAll('tr').forEach((row) => {
            const cost = Number(row.querySelector('.cost-price').value || 0);
            const qty = Number(row.querySelector('.qty').value || 0);
            const line = cost * qty;
            row.querySelector('.line-total').textContent = line.toFixed(2);
            subtotal += line;
        });
        const discount = Number(document.getElementById('discount_amount')?.value || 0);
        const tax = Number(document.getElementById('tax_amount')?.value || 0);
        grandTotalEl.value = Math.max(0, subtotal - discount + tax).toFixed(2);
    }

    addRowBtn?.addEventListener('click', () => {
        const index = tableBody.querySelectorAll('tr').length;
        const first = tableBody.querySelector('tr');
        const clone = first.cloneNode(true);
        clone.querySelectorAll('input, select').forEach((el) => {
            const name = el.getAttribute('name');
            if (name) {
                el.setAttribute('name', name.replace(/items\[\d+\]/, 'items[' + index + ']'));
            }
            if (el.tagName === 'SELECT') el.value = '';
            if (el.classList.contains('qty')) el.value = 1;
            if (el.classList.contains('cost-price')) el.value = 0;
        });
        tableBody.appendChild(clone);
        recalc();
    });

    tableBody.addEventListener('click', (e) => {
        if (e.target.classList.contains('remove-row') && tableBody.querySelectorAll('tr').length > 1) {
            e.target.closest('tr').remove();
            recalc();
        }
    });

    tableBody.addEventListener('change', (e) => {
        if (e.target.classList.contains('product-select')) {
            const selected = e.target.selectedOptions[0];
            const cost = selected?.dataset?.cost || 0;
            e.target.closest('tr').querySelector('.cost-price').value = cost;
        }
        recalc();
    });

    tableBody.addEventListener('input', recalc);
    document.getElementById('discount_amount')?.addEventListener('input', recalc);
    document.getElementById('tax_amount')?.addEventListener('input', recalc);
    recalc();
})();
</script>
@endsection
