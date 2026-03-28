<?php

namespace App\Services\Accounting;

use App\Models\PurchaseInvoice;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Builder;

class PayableService
{
    public function outstandingQuery(): Builder
    {
        return PurchaseInvoice::query()->where('due_amount', '>', 0)->where('status', 'posted');
    }

    public function totalOutstanding(): float
    {
        return (float) $this->outstandingQuery()->sum('due_amount');
    }

    public function supplierOutstanding(Supplier $supplier): float
    {
        return (float) $this->outstandingQuery()->where('supplier_id', $supplier->id)->sum('due_amount');
    }
}
