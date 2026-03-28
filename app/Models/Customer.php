<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name',
        'company_name',
        'phone',
        'email',
        'address',
        'opening_balance',
        'balance_type',
        'is_active',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function posOrders()
    {
        return $this->hasMany(PosOrder::class);
    }

    public function customerPayments()
    {
        return $this->hasMany(CustomerPayment::class);
    }

    public function partyLedgers()
    {
        return $this->hasMany(PartyLedger::class, 'party_id')->where('party_type', PartyLedger::TYPE_CUSTOMER);
    }

    public function getPendingAmount(): float
    {
        $totalDue = $this->posOrders()
            ->where('status', 'completed')
            ->sum('due_amount');

        return max(0, $totalDue);
    }
}
