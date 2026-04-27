<?php

namespace App\Models\V2;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use SoftDeletes;

    public const TYPE_RECEIVABLE = 'receivable';
    public const TYPE_PAYABLE = 'payable';
    public const TYPE_CASH_BANK = 'cash_bank';
    public const TYPE_ASSET = 'asset';
    public const TYPE_LIABILITY = 'liability';
    public const TYPE_CAPITAL = 'capital';
    public const TYPE_REVENUE = 'revenue';
    public const TYPE_EXPENSE = 'expense';

    protected $table = 'v2_accounts';

    protected $fillable = [
        'account_type',
        'code',
        'name',
        'opening_date',
        'opening_amount',
        'currency_rate',
        'is_system',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'opening_date' => 'date',
        'opening_amount' => 'decimal:2',
        'currency_rate' => 'decimal:4',
        'is_system' => 'boolean',
        'is_active' => 'boolean',
    ];

    public static function types(): array
    {
        return [
            self::TYPE_RECEIVABLE => 'Receivable',
            self::TYPE_PAYABLE => 'Payable',
            self::TYPE_CASH_BANK => 'Cash/Bank',
            self::TYPE_ASSET => 'Asset',
            self::TYPE_LIABILITY => 'Liability',
            self::TYPE_CAPITAL => 'Capital',
            self::TYPE_REVENUE => 'Revenue',
            self::TYPE_EXPENSE => 'Expense',
        ];
    }

    public static function prefixForType(string $type): string
    {
        return [
            self::TYPE_RECEIVABLE => 'ACR',
            self::TYPE_PAYABLE => 'ACP',
            self::TYPE_CASH_BANK => 'CBK',
            self::TYPE_ASSET => 'AST',
            self::TYPE_LIABILITY => 'LIB',
            self::TYPE_CAPITAL => 'CAP',
            self::TYPE_REVENUE => 'REV',
            self::TYPE_EXPENSE => 'EXP',
        ][$type] ?? 'ACC';
    }

    public function detail()
    {
        return $this->hasOne(AccountDetail::class);
    }

    public function ledgerEntries()
    {
        return $this->hasMany(LedgerEntry::class);
    }

    public function getBalanceAttribute(): float
    {
        return (float) $this->opening_amount
            + (float) $this->ledgerEntries()->sum('debit')
            - (float) $this->ledgerEntries()->sum('credit');
    }
}
