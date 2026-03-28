<?php

namespace App\Services\Accounting;

use App\Models\Customer;
use App\Models\PosOrder;
use Illuminate\Database\Eloquent\Builder;

class ReceivableService
{
    public function outstandingQuery(): Builder
    {
        return PosOrder::query()->where('due_amount', '>', 0)->where('status', 'completed');
    }

    public function totalOutstanding(): float
    {
        return (float) $this->outstandingQuery()->sum('due_amount');
    }

    public function customerOutstanding(Customer $customer): float
    {
        return (float) $this->outstandingQuery()->where('customer_id', $customer->id)->sum('due_amount');
    }
}
