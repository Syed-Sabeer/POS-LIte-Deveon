@extends('layouts.app.master')

@section('title', $module['heading'])

@section('css')
<style>
.accounting-module-link {
    border-radius: 8px;
    color: inherit;
    min-height: 118px;
    transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
}

.accounting-module-link:hover {
    border-color: #0d6efd;
    box-shadow: 0 8px 20px rgba(15, 23, 42, .08);
    color: inherit;
    transform: translateY(-2px);
}

.accounting-icon-box {
    align-items: center;
    background: #f8f9fa;
    border: 1px solid #edf0f2;
    border-radius: 8px;
    display: inline-flex;
    height: 42px;
    justify-content: center;
    width: 42px;
}

.accounting-summary-card {
    border-radius: 8px;
    min-height: 112px;
}

.accounting-line-list {
    min-width: 260px;
}

.accounting-empty-state {
    min-height: 160px;
}

.accounting-form-card .form-label {
    font-weight: 600;
}
</style>
@endsection

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4 class="fw-bold">{{ $module['heading'] }}</h4>
        <h6>{{ $module['subtitle'] }}</h6>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row g-3 mb-4">
    @foreach($modules as $key => $item)
        <div class="col-sm-6 col-xl-3">
            <a href="{{ route($item['route']) }}" class="card accounting-module-link text-decoration-none mb-0 {{ $activeKey === $key ? 'border-primary shadow-sm' : '' }}">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="accounting-icon-box text-{{ $item['tone'] }}">
                            <i class="{{ $item['icon'] }} fs-22"></i>
                        </span>
                        @if($activeKey === $key)
                            <span class="badge bg-primary">Active</span>
                        @endif
                    </div>
                    <h6 class="fw-bold mb-1">{{ $item['title'] }}</h6>
                    <p class="text-muted mb-0">{{ implode(', ', $item['type_labels']) }}</p>
                </div>
            </a>
        </div>
    @endforeach
</div>

<div class="row g-3 mb-4">
    @foreach($summaryItems as $summary)
        <div class="col-sm-6 col-xl-3">
            <div class="card accounting-summary-card mb-0">
                <div class="card-body d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <p class="text-muted mb-1">{{ $summary['label'] }}</p>
                        <h4 class="fw-bold mb-0">{{ $summary['value'] }}</h4>
                    </div>
                    <span class="accounting-icon-box text-{{ $module['tone'] }}">
                        <i class="{{ $summary['icon'] }} fs-22"></i>
                    </span>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="card accounting-form-card mb-4">
    <div class="card-header">
        <h5 class="fw-bold mb-0">New {{ $module['title'] }} Entry</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route($module['store_route']) }}" class="row g-3" id="accountingEntryForm">
            @csrf

            <div class="col-md-3">
                <label class="form-label">Date <span class="text-danger">*</span></label>
                <input type="date" name="entry_date" value="{{ old('entry_date', now()->toDateString()) }}" class="form-control" required>
            </div>

            <div class="col-md-3">
                <label class="form-label">Reference</label>
                <input type="text" name="reference_no" value="{{ old('reference_no') }}" class="form-control" placeholder="Auto">
            </div>

            @if(isset($module['actions']))
                <div class="col-md-3">
                    <label class="form-label">Entry Type <span class="text-danger">*</span></label>
                    <select name="entry_action" class="form-control" data-entry-action required>
                        @foreach($module['actions'] as $value => $label)
                            <option value="{{ $value }}" {{ old('entry_action', array_key_first($module['actions'])) === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="col-md-{{ isset($module['actions']) ? '3' : '6' }}">
                <label class="form-label">{{ $module['party_label'] }} <span class="text-danger">*</span></label>
                <input type="text" name="party_name" value="{{ old('party_name') }}" class="form-control" required>
            </div>

            <div class="col-md-3">
                <label class="form-label">{{ $module['amount_label'] }} <span class="text-danger">*</span></label>
                <input type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount') }}" class="form-control" required>
            </div>

            @if($module['show_paid_amount'])
                <div class="col-md-3">
                    <label class="form-label">{{ $module['paid_label'] }}</label>
                    <input type="number" step="0.01" min="0" name="paid_amount" value="{{ old('paid_amount', 0) }}" class="form-control">
                </div>
            @endif

            @if($module['show_payment_account'] || isset($module['action_visibility']))
                <div class="col-md-3" data-payment-account-field>
                    <label class="form-label">Cash / Bank Account</label>
                    <select name="payment_account_id" class="form-control">
                        @foreach($paymentAccounts as $account)
                            <option value="{{ $account->id }}" {{ (string) old('payment_account_id') === (string) $account->id ? 'selected' : '' }}>
                                {{ $account->code }} - {{ $account->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            @if($module['show_purchase_account'] || isset($module['action_visibility']))
                <div class="col-md-3" data-purchase-account-field>
                    <label class="form-label">Purchase / Expense Account</label>
                    <select name="purchase_account_id" class="form-control">
                        @foreach($purchaseAccounts as $account)
                            <option value="{{ $account->id }}" {{ (string) old('purchase_account_id') === (string) $account->id ? 'selected' : '' }}>
                                {{ $account->code }} - {{ $account->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="col-md-{{ ($module['show_paid_amount'] || isset($module['action_visibility'])) ? '6' : '9' }}">
                <label class="form-label">Notes</label>
                <input type="text" name="notes" value="{{ old('notes') }}" class="form-control">
            </div>

            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="ti ti-device-floppy me-1"></i>Save Entry
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card mb-0">
    <div class="card-header">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">From</label>
                <input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">To</label>
                <input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Reference or party">
            </div>
            <div class="col-md-2">
                <button class="btn btn-secondary w-100">Filter</button>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Reference</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th>Journal Lines</th>
                        <th class="text-end">Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($entries as $entry)
                        @php($entryAmount = (float) $entry->lines->sum(fn ($line) => (float) $line->debit))
                        <tr>
                            <td>{{ $entry->entry_date->format('Y-m-d') }}</td>
                            <td>{{ $entry->reference_no }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $module['type_labels'][$entry->voucher_type] ?? ucwords(str_replace('_', ' ', $entry->voucher_type)) }}</span></td>
                            <td>{{ $entry->description }}</td>
                            <td>
                                <div class="accounting-line-list">
                                    @foreach($entry->lines as $line)
                                        <div class="d-flex align-items-center justify-content-between gap-3 border-bottom py-1">
                                            <span>{{ $line->account?->code }} - {{ $line->account?->name }}</span>
                                            <span class="fw-semibold text-nowrap">
                                                {{ (float) $line->debit > 0 ? 'Dr' : 'Cr' }}
                                                PKR {{ number_format((float) ((float) $line->debit > 0 ? $line->debit : $line->credit), 2) }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                            <td class="text-end">PKR {{ number_format($entryAmount, 2) }}</td>
                            <td><span class="badge bg-success">{{ strtoupper($entry->status) }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="accounting-empty-state d-flex flex-column align-items-center justify-content-center text-center p-4">
                                    <span class="accounting-icon-box text-{{ $module['tone'] }} mb-3">
                                        <i class="{{ $module['icon'] }} fs-22"></i>
                                    </span>
                                    <h6 class="fw-bold mb-0">{{ $module['empty'] }}</h6>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($entries->hasPages())
        <div class="p-3">{{ $entries->links() }}</div>
    @endif
</div>
@endsection

@section('script')
<script>
(function () {
    const actionSelect = document.querySelector('[data-entry-action]');
    const paymentField = document.querySelector('[data-payment-account-field]');
    const purchaseField = document.querySelector('[data-purchase-account-field]');
    const visibility = @json($module['action_visibility'] ?? []);

    function setFieldVisible(field, visible) {
        if (!field) {
            return;
        }

        field.classList.toggle('d-none', !visible);
    }

    function refreshFields() {
        if (!actionSelect) {
            return;
        }

        const settings = visibility[actionSelect.value] || {};
        setFieldVisible(paymentField, Boolean(settings.payment));
        setFieldVisible(purchaseField, Boolean(settings.purchase));
    }

    if (actionSelect) {
        actionSelect.addEventListener('change', refreshFields);
        refreshFields();
    }
})();
</script>
@endsection
