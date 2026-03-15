<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSupplierPaymentRequest;
use App\Models\Account;
use App\Models\PurchaseInvoice;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Services\Accounting\PostingService;
use App\Support\FinanceNumber;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SupplierPaymentController extends Controller
{
    public function __construct(private readonly PostingService $postingService)
    {
    }

    public function index()
    {
        $payments = SupplierPayment::with(['supplier', 'purchaseInvoice'])
            ->when(request('supplier_id'), fn ($q, $id) => $q->where('supplier_id', $id))
            ->when(request('from_date'), fn ($q, $date) => $q->whereDate('payment_date', '>=', $date))
            ->when(request('to_date'), fn ($q, $date) => $q->whereDate('payment_date', '<=', $date))
            ->latest('payment_date')
            ->paginate(20)
            ->withQueryString();

        $suppliers = Supplier::orderBy('full_name')->get();

        return view('supplier-payments.index', compact('payments', 'suppliers'));
    }

    public function create()
    {
        $suppliers = Supplier::orderBy('full_name')->get();
        $accounts = Account::whereIn('code', [Account::CODE_CASH, Account::CODE_BANK])->where('is_active', true)->get();
        $invoices = PurchaseInvoice::where('due_amount', '>', 0)->where('status', 'posted')->latest('invoice_date')->get();

        return view('supplier-payments.create', compact('suppliers', 'accounts', 'invoices'));
    }

    public function store(StoreSupplierPaymentRequest $request)
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $request) {
            $invoice = null;
            if (! empty($data['purchase_invoice_id'])) {
                $invoice = PurchaseInvoice::lockForUpdate()->findOrFail((int) $data['purchase_invoice_id']);
                if ((int) $invoice->supplier_id !== (int) $data['supplier_id']) {
                    throw ValidationException::withMessages(['purchase_invoice_id' => 'Invoice does not belong to selected supplier.']);
                }

                if ((float) $data['amount'] > (float) $invoice->due_amount) {
                    throw ValidationException::withMessages(['amount' => 'Payment amount cannot exceed invoice due amount.']);
                }
            }

            $payment = SupplierPayment::create([
                'supplier_id' => $data['supplier_id'],
                'purchase_invoice_id' => $data['purchase_invoice_id'] ?? null,
                'payment_date' => $data['payment_date'],
                'reference_no' => $data['reference_no'] ?: FinanceNumber::next('SPV', SupplierPayment::class),
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'],
                'account_id' => $data['account_id'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => $request->user()?->id,
            ]);

            if ($invoice) {
                $invoice->paid_amount = (float) $invoice->paid_amount + (float) $payment->amount;
                $invoice->due_amount = max(0, (float) $invoice->total - (float) $invoice->paid_amount);
                $invoice->payment_status = $invoice->due_amount <= 0 ? 'paid' : 'partial';
                $invoice->save();
            }

            $this->postingService->postSupplierPayment($payment, $request->user()?->id);
        });

        return redirect()->route('supplier-payments.index')->with('success', 'Supplier payment saved successfully.');
    }

    public function show(SupplierPayment $supplierPayment)
    {
        $supplierPayment->load(['supplier', 'purchaseInvoice', 'account']);

        return view('supplier-payments.show', ['payment' => $supplierPayment]);
    }

    public function voucher(SupplierPayment $supplierPayment)
    {
        $supplierPayment->load(['supplier', 'purchaseInvoice', 'account']);

        return view('supplier-payments.voucher', ['payment' => $supplierPayment]);
    }
}
