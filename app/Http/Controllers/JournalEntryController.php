<?php

namespace App\Http\Controllers;

use App\Models\JournalEntry;

class JournalEntryController extends Controller
{
    public function index()
    {
        $entries = JournalEntry::with('lines.account')
            ->when(request('from_date'), fn ($q, $date) => $q->whereDate('entry_date', '>=', $date))
            ->when(request('to_date'), fn ($q, $date) => $q->whereDate('entry_date', '<=', $date))
            ->when(request('voucher_type'), fn ($q, $type) => $q->where('voucher_type', $type))
            ->latest('entry_date')
            ->paginate(25)
            ->withQueryString();

        return view('journals.index', compact('entries'));
    }

    public function show(JournalEntry $journalEntry)
    {
        $journalEntry->load('lines.account');

        return view('journals.show', ['entry' => $journalEntry]);
    }
}
