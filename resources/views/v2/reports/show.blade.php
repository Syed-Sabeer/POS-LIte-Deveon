@extends('layouts.app.master')

@section('title', ucwords(str_replace('-', ' ', $report)))
@section('css')@include('v2.partials.style')@endsection

@section('content')
<div class="v2-wrap">
    <div class="page-header"><div class="page-title v2-title"><h4 class="fw-bold">{{ ucwords(str_replace('-', ' ', $report)) }}</h4></div><div class="v2-actions"><button onclick="window.print()" class="btn btn-primary">Print</button><a href="{{ route('v2.reports.index') }}" class="btn btn-secondary">Back</a></div></div>
    <div class="card mb-3 no-print"><div class="card-body"><form class="row g-2"><div class="col-md-2"><input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control"></div><div class="col-md-2"><input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control"></div><div class="col-md-3"><select name="account_id" class="form-control"><option value="">All Accounts</option>@foreach($accounts as $account)<option value="{{ $account->id }}" @selected(request('account_id')==$account->id)>{{ $account->code }} - {{ $account->name }}</option>@endforeach</select></div><div class="col-md-3"><select name="item_id" class="form-control"><option value="">All Items</option>@foreach($items as $item)<option value="{{ $item->id }}" @selected(request('item_id')==$item->id)>{{ $item->code }} - {{ $item->description }}</option>@endforeach</select></div><div class="col-md-2"><button class="btn btn-secondary w-100">Apply</button></div></form></div></div>
    <div class="card"><div class="table-responsive">
        @if(in_array($report, ['trial-balance', 'trial-balance-detail', 'aged-trial-balance', 'income-statement', 'balance-sheet'], true))
            <table class="table table-bordered mb-0"><thead class="table-light"><tr><th>Code</th><th>Account</th><th>Type</th><th class="text-end">Debit</th><th class="text-end">Credit</th></tr></thead><tbody>
                @php($filteredAccounts = $statementAccounts)
                @if($report === 'income-statement')
                    @php($filteredAccounts = $statementAccounts->whereIn('account_type', ['revenue', 'expense']))
                @elseif($report === 'balance-sheet')
                    @php($filteredAccounts = $statementAccounts->whereIn('account_type', ['asset', 'receivable', 'cash_bank', 'liability', 'payable', 'capital']))
                @endif
                @forelse($filteredAccounts as $account)
                    @php($balance = $account->balance)
                    <tr><td>{{ $account->code }}</td><td>{{ $account->name }}</td><td>{{ ucwords(str_replace('_', ' ', $account->account_type)) }}</td><td class="text-end">{{ $balance >= 0 ? number_format($balance,2) : '0.00' }}</td><td class="text-end">{{ $balance < 0 ? number_format(abs($balance),2) : '0.00' }}</td></tr>
                @empty
                    <tr><td colspan="5" class="text-center">No records.</td></tr>
                @endforelse
            </tbody><tfoot><tr><th colspan="3">Total</th><th class="text-end">{{ number_format($filteredAccounts->sum(fn($account) => max(0, $account->balance)),2) }}</th><th class="text-end">{{ number_format($filteredAccounts->sum(fn($account) => max(0, -$account->balance)),2) }}</th></tr></tfoot></table>
        @elseif(str_contains($report, 'stock') || str_contains($report, 'item') || str_contains($report, 'inventory'))
            <table class="table table-bordered mb-0"><thead class="table-light"><tr><th>Date</th><th>Item</th><th>Voucher</th><th>Qty In</th><th>Qty Out</th><th>Amount</th></tr></thead><tbody>@forelse($stockMovements as $move)<tr><td>{{ $move->movement_date->format('Y-m-d') }}</td><td>{{ $move->item?->description }}</td><td>{{ $move->voucher_no }}</td><td>{{ number_format((float)$move->qty_in,3) }}</td><td>{{ number_format((float)$move->qty_out,3) }}</td><td>{{ number_format((float)$move->amount,2) }}</td></tr>@empty<tr><td colspan="6" class="text-center">No records.</td></tr>@endforelse</tbody></table>
        @elseif(str_contains($report, 'voucher') || str_contains($report, 'journal'))
            <table class="table table-bordered mb-0"><thead class="table-light"><tr><th>Date</th><th>Voucher</th><th>Type</th><th>Account</th><th>Particulars</th><th>Amount</th></tr></thead><tbody>@forelse($vouchers as $voucher)<tr><td>{{ $voucher->voucher_date->format('Y-m-d') }}</td><td>{{ $voucher->voucher_no }}</td><td>{{ strtoupper($voucher->type) }}</td><td>{{ $voucher->account?->name }}</td><td>{{ $voucher->particulars ?: $voucher->remarks }}</td><td>{{ number_format((float)$voucher->amount,2) }}</td></tr>@empty<tr><td colspan="6" class="text-center">No records.</td></tr>@endforelse</tbody></table>
        @elseif(str_contains($report, 'sale') || str_contains($report, 'purchase'))
            <table class="table table-bordered mb-0"><thead class="table-light"><tr><th>Date</th><th>Voucher</th><th>Type</th><th>Party</th><th>Gross</th><th>Net</th></tr></thead><tbody>@forelse($invoices as $invoice)<tr><td>{{ $invoice->invoice_date->format('Y-m-d') }}</td><td>{{ $invoice->voucher_no }}</td><td>{{ strtoupper($invoice->type) }}</td><td>{{ $invoice->party_name }}</td><td>{{ number_format((float)$invoice->gross_amount,2) }}</td><td>{{ number_format((float)$invoice->net_amount,2) }}</td></tr>@empty<tr><td colspan="6" class="text-center">No records.</td></tr>@endforelse</tbody></table>
        @else
            <table class="table table-bordered mb-0"><thead class="table-light"><tr><th>Date</th><th>Account</th><th>Voucher</th><th>Particulars</th><th>Debit</th><th>Credit</th><th>Balance</th></tr></thead><tbody>@forelse($ledgerEntries as $entry)<tr><td>{{ $entry->entry_date->format('Y-m-d') }}</td><td>{{ $entry->account?->code }} - {{ $entry->account?->name }}</td><td>{{ $entry->voucher_no }}</td><td>{{ $entry->particulars }}</td><td>{{ number_format((float)$entry->debit,2) }}</td><td>{{ number_format((float)$entry->credit,2) }}</td><td>{{ number_format((float)$entry->running_balance,2) }}</td></tr>@empty<tr><td colspan="7" class="text-center">No records.</td></tr>@endforelse</tbody></table>
        @endif
    </div></div>
</div>
@endsection
