<?php

namespace App\Models\V2;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use SoftDeletes;

    protected $table = 'v2_items';

    protected $fillable = [
        'category_id',
        'brand_id',
        'code',
        'nick',
        'description',
        'bf_qty',
        'minimum_qty',
        'maximum_qty',
        'packing',
        'packet_qty',
        'opening_cost',
        'cost',
        'retail_rate',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'bf_qty' => 'decimal:3',
        'minimum_qty' => 'decimal:3',
        'maximum_qty' => 'decimal:3',
        'packet_qty' => 'decimal:3',
        'opening_cost' => 'decimal:2',
        'cost' => 'decimal:2',
        'retail_rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function getBalanceQtyAttribute(): float
    {
        return (float) $this->bf_qty
            + (float) $this->stockMovements()->sum('qty_in')
            - (float) $this->stockMovements()->sum('qty_out');
    }
}
