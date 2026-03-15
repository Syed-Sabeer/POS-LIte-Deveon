<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountTransaction;
use App\Models\Customer;
use App\Models\PartyLedger;
use App\Models\Supplier;

class LedgerController extends Controller
{
    public function customer()
    {
        $customers = Customer::orderBy('full_name')->get();
        $customerId = (int) request('customer_id');

        $entries = PartyLedger::query()
            ->where('party_type', PartyLedger::TYPE_CUSTOMER)
            ->when($customerId, fn ($q) => $q->where('party_id', $customerId))
            ->when(request('from_date'), fn ($q, $date) => $q->whereDate('entry_date', '>=', $date))
            ->when(request('to_date'), fn ($q, $date) => $q->whereDate('entry_date', '<=', $date))
            ->orderBy('entry_date')
            ->orderBy('id')
            ->paginate(30)
            ->withQueryString();

        return view('ledgers.customer', compact('entries', 'customers', 'customerId'));
    }

    public function supplier()
    {
        $suppliers = Supplier::orderBy('full_name')->get();
        $supplierId = (int) request('supplier_id');

        $entries = PartyLedger::query()
            ->where('party_type', PartyLedger::TYPE_SUPPLIER)
            ->when($supplierId, fn ($q) => $q->where('party_id', $supplierId))
            ->when(request('from_date'), fn ($q, $date) => $q->whereDate('entry_date', '>=', $date))
            ->when(request('to_date'), fn ($q, $date) => $q->whereDate('entry_date', '<=', $date))
            ->orderBy('entry_date')
            ->orderBy('id')
            ->paginate(30)
            ->withQueryString();

        return view('ledgers.supplier', compact('entries', 'suppliers', 'supplierId'));
    }

    public function account()
    {
        $accounts = Account::orderBy('code')->get();
        $accountId = (int) request('account_id');

        $entries = AccountTransaction::with('account')
            ->when($accountId, fn ($q) => $q->where('account_id', $accountId))
            ->when(request('from_date'), fn ($q, $date) => $q->whereDate('transaction_date', '>=', $date))
            ->when(request('to_date'), fn ($q, $date) => $q->whereDate('transaction_date', '<=', $date))
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->paginate(30)
            ->withQueryString();

        return view('ledgers.account', compact('entries', 'accounts', 'accountId'));
    }

    public function cashBook()
    {
        $cashAccount = Account::where('code', Account::CODE_CASH)->firstOrFail();
        $entries = AccountTransaction::where('account_id', $cashAccount->id)
            ->when(request('from_date'), fn ($q, $date) => $q->whereDate('transaction_date', '>=', $date))
            ->when(request('to_date'), fn ($q, $date) => $q->whereDate('transaction_date', '<=', $date))
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->paginate(30)
            ->withQueryString();

        return view('ledgers.cash-book', compact('entries', 'cashAccount'));
    }

    public function bankBook()
    {
        $bankAccount = Account::where('code', Account::CODE_BANK)->firstOrFail();
        $entries = AccountTransaction::where('account_id', $bankAccount->id)
            ->when(request('from_date'), fn ($q, $date) => $q->whereDate('transaction_date', '>=', $date))
            ->when(request('to_date'), fn ($q, $date) => $q->whereDate('transaction_date', '<=', $date))
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->paginate(30)
            ->withQueryString();

        return view('ledgers.bank-book', compact('entries', 'bankAccount'));
    }

    public function customerStatement(Customer $customer)
    {
        $entries = PartyLedger::where('party_type', PartyLedger::TYPE_CUSTOMER)
            ->where('party_id', $customer->id)
            ->when(request('from_date'), fn ($q, $date) => $q->whereDate('entry_date', '>=', $date))
            ->when(request('to_date'), fn ($q, $date) => $q->whereDate('entry_date', '<=', $date))
            ->orderBy('entry_date')
            ->orderBy('id')
            ->get();

        return view('ledgers.customer-statement', compact('customer', 'entries'));
    }

    public function supplierStatement(Supplier $supplier)
    {
        $entries = PartyLedger::where('party_type', PartyLedger::TYPE_SUPPLIER)
            ->where('party_id', $supplier->id)
            ->when(request('from_date'), fn ($q, $date) => $q->whereDate('entry_date', '>=', $date))
            ->when(request('to_date'), fn ($q, $date) => $q->whereDate('entry_date', '<=', $date))
            ->orderBy('entry_date')
            ->orderBy('id')
            ->get();

        return view('ledgers.supplier-statement', compact('supplier', 'entries'));
    }
}
