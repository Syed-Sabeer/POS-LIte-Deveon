<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Models\V2\Account;
use App\Models\V2\LedgerEntry;
use Illuminate\Http\Request;

class LedgerController extends Controller
{
    private const TYPE_PERMISSIONS = [
        'asset' => 'v2 assets ledger',
        'receivable' => 'v2 receivables ledger',
        'payable' => 'v2 payables ledger',
        'liability' => 'v2 liabilities ledger',
        'capital' => 'v2 capital ledger',
        'revenue' => 'v2 revenue ledger',
        'expense' => 'v2 expenses ledger',
        'cash_bank' => 'v2 cash bank ledger',
    ];

    public function summary(Request $request, string $type)
    {
        abort_unless(array_key_exists($type, self::TYPE_PERMISSIONS), 404);
        abort_unless($request->user()?->can(self::TYPE_PERMISSIONS[$type]), 403);

        $accounts = Account::with('detail')
            ->where('account_type', $type)
            ->when($request->search, fn ($q, $search) => $q->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"))
            ->when(! $request->boolean('zeroed') && $request->boolean('non_zeroed'), fn ($q) => $q->where(function ($q) {
                $q->where('opening_amount', '<>', 0)->orWhereHas('ledgerEntries');
            }))
            ->orderBy('name')
            ->paginate(30)
            ->withQueryString();

        return view('v2.ledgers.summary', [
            'accounts' => $accounts,
            'type' => $type,
            'types' => Account::types(),
        ]);
    }

    public function detail(Request $request, Account $account, string $mode = 'ledger')
    {
        $entries = LedgerEntry::where('account_id', $account->id)
            ->when($request->from_date, fn ($q, $date) => $q->whereDate('entry_date', '>=', $date))
            ->when($request->to_date, fn ($q, $date) => $q->whereDate('entry_date', '<=', $date))
            ->orderBy('entry_date')
            ->orderBy('id')
            ->get();

        return view('v2.ledgers.detail', compact('account', 'entries', 'mode'));
    }
}
