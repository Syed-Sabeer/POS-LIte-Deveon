<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartyLedger extends Model
{
    use HasFactory;

    public const TYPE_CUSTOMER = 'customer';
    public const TYPE_SUPPLIER = 'supplier';

    protected $fillable = [
        'party_type',
        'party_id',
        'entry_date',
        'voucher_type',
        'voucher_id',
        'reference_no',
        'description',
        'debit',
        'credit',
        'balance',
        'created_by',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
        'balance' => 'decimal:2',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
