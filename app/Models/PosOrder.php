<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Customer;

class PosOrder extends Model
{
    use HasFactory;

    public const PAYMENT_STATUS_PAID = 'paid';
    public const PAYMENT_STATUS_PARTIAL = 'partial';
    public const PAYMENT_STATUS_UNPAID = 'unpaid';

    protected $fillable = [
        'order_number',
        'user_id',
        'customer_id',
        'customer_name',
        'payment_method',
        'invoice_date',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total',
        'paid_amount',
        'due_amount',
        'payment_status',
        'status',
        'notes',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_amount' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(PosOrderItem::class);
    }

    public function customerPayments()
    {
        return $this->hasMany(CustomerPayment::class);
    }
}
