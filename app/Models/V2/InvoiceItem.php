<?php

namespace App\Models\V2;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    protected $table = 'v2_invoice_items';

    protected $fillable = [
        'invoice_id',
        'item_id',
        'item_code',
        'item_name',
        'item_detail',
        'qty',
        'packet',
        'rate',
        'discount',
        'discounted_rate',
        'amount',
    ];

    protected $casts = [
        'qty' => 'decimal:3',
        'packet' => 'decimal:3',
        'rate' => 'decimal:2',
        'discount' => 'decimal:2',
        'discounted_rate' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
