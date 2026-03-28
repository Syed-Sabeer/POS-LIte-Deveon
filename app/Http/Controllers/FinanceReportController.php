<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\PosOrder;
use App\Models\PurchaseInvoice;
use App\Models\Supplier;
use App\Services\Accounting\PayableService;
use App\Services\Accounting\ReceivableService;

class FinanceReportController extends Controller
{
    public function __construct(
        private readonly ReceivableService $receivableService,
        private readonly PayableService $payableService
    ) {
    }

    public function receivables()
    {
        $orders = PosOrder::with('customer')
            ->where('due_amount', '>', 0)
            ->when(request('customer_id'), fn ($q, $id) => $q->where('customer_id', $id))
            ->when(request('from_date'), fn ($q, $date) => $q->whereDate('invoice_date', '>=', $date))
            ->when(request('to_date'), fn ($q, $date) => $q->whereDate('invoice_date', '<=', $date))
            ->latest('invoice_date')
            ->paginate(20)
            ->withQueryString();

        $customers = Customer::orderBy('full_name')->get();
    $totalDue = $this->receivableService->totalOutstanding();

        return view('finance-reports.receivables', compact('orders', 'customers', 'totalDue'));
    }

    public function payables()
    {
        $invoices = PurchaseInvoice::with('supplier')
            ->where('due_amount', '>', 0)
            ->when(request('supplier_id'), fn ($q, $id) => $q->where('supplier_id', $id))
            ->when(request('from_date'), fn ($q, $date) => $q->whereDate('invoice_date', '>=', $date))
            ->when(request('to_date'), fn ($q, $date) => $q->whereDate('invoice_date', '<=', $date))
            ->latest('invoice_date')
            ->paginate(20)
            ->withQueryString();

        $suppliers = Supplier::orderBy('full_name')->get();
    $totalDue = $this->payableService->totalOutstanding();

        return view('finance-reports.payables', compact('invoices', 'suppliers', 'totalDue'));
    }
}
