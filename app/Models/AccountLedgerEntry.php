<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountLedgerEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'entry_date',
        'source_type',
        'source_id',
        'journal_entry_id',
        'reference_no',
        'description',
        'debit',
        'credit',
        'running_balance',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
        'running_balance' => 'decimal:2',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class);
    }
}
