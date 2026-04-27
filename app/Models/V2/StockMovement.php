<?php

namespace App\Models\V2;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $table = 'v2_stock_movements';

    protected $fillable = [
        'item_id',
        'source_type',
        'source_id',
        'movement_date',
        'voucher_no',
        'account_id',
        'qty_in',
        'qty_out',
        'rate',
        'amount',
        'packing',
        'remarks',
        'created_by',
    ];

    protected $casts = [
        'movement_date' => 'date',
        'qty_in' => 'decimal:3',
        'qty_out' => 'decimal:3',
        'rate' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
