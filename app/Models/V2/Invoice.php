<?php

namespace App\Models\V2;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use SoftDeletes;

    protected $table = 'v2_invoices';

    protected $fillable = [
        'type',
        'account_id',
        'party_name',
        'voucher_no',
        'invoice_date',
        'currency_rate',
        'memo',
        'gross_amount',
        'charges',
        'discount',
        'net_amount',
        'received_amount',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'currency_rate' => 'decimal:4',
        'gross_amount' => 'decimal:2',
        'charges' => 'decimal:2',
        'discount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'received_amount' => 'decimal:2',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }
}
