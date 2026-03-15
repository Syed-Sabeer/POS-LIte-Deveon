<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use HasFactory, SoftDeletes;

    public const CODE_CASH = '1101';
    public const CODE_BANK = '1102';
    public const CODE_RECEIVABLE = '1103';
    public const CODE_INVENTORY = '1104';
    public const CODE_PAYABLE = '2101';
    public const CODE_SALES_REVENUE = '4101';
    public const CODE_COGS = '5101';

    protected $fillable = [
        'name',
        'code',
        'type',
        'parent_id',
        'is_system',
        'is_active',
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function journalEntryLines()
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    public function accountTransactions()
    {
        return $this->hasMany(AccountTransaction::class);
    }
}
