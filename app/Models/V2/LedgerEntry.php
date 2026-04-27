<?php

namespace App\Models\V2;

use Illuminate\Database\Eloquent\Model;

class LedgerEntry extends Model
{
    protected $table = 'v2_ledger_entries';

    protected $fillable = [
        'account_id',
        'source_type',
        'source_id',
        'entry_date',
        'voucher_no',
        'particulars',
        'debit',
        'credit',
        'running_balance',
        'created_by',
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
}
