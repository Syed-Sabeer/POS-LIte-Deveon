<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'sku',
        'name',
        'cost_price',
        'selling_price',
        'quantity',
        'unit',
        'image',
        'is_active',
    ];

    protected static function booted()
    {
        static::creating(function (self $product) {
            if (empty($product->sku)) {
                $product->sku = self::generateUniqueSku();
            }
        });
    }

    protected static function generateUniqueSku(): string
    {
        do {
            $candidate = 'SKU-' . now()->format('ymd') . '-' . strtoupper(Str::random(6));
        } while (self::withTrashed()->where('sku', $candidate)->exists());

        return $candidate;
    }

    public function purchaseInvoiceItems()
    {
        return $this->hasMany(PurchaseInvoiceItem::class);
    }
}
