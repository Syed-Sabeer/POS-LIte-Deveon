<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerPaymentRequest;
use App\Models\Account;
use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\PosOrder;
use App\Services\Accounting\PostingService;
use App\Support\FinanceNumber;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CustomerPaymentController extends Controller
{
    public function __construct(private readonly PostingService $postingService)
    {
    }

    public function index()
    {
        $payments = CustomerPayment::with(['customer', 'posOrder'])
            ->when(request('customer_id'), fn ($q, $id) => $q->where('customer_id', $id))
            ->when(request('from_date'), fn ($q, $date) => $q->whereDate('payment_date', '>=', $date))
            ->when(request('to_date'), fn ($q, $date) => $q->whereDate('payment_date', '<=', $date))
            ->latest('payment_date')
            ->paginate(20)
            ->withQueryString();

        $customers = Customer::orderBy('full_name')->get();

        return view('customer-payments.index', compact('payments', 'customers'));
    }

    public function create()
    {
        $customers = Customer::orderBy('full_name')->get();
        $accounts = Account::whereIn('code', [Account::CODE_CASH, Account::CODE_BANK])->where('is_active', true)->get();
        $invoices = PosOrder::where('due_amount', '>', 0)->where('status', 'completed')->latest('invoice_date')->get();

        return view('customer-payments.create', compact('customers', 'accounts', 'invoices'));
    }

    public function store(StoreCustomerPaymentRequest $request)
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $request) {
            $invoice = null;
            if (! empty($data['pos_order_id'])) {
                $invoice = PosOrder::lockForUpdate()->findOrFail((int) $data['pos_order_id']);
                if ((int) $invoice->customer_id !== (int) $data['customer_id']) {
                    throw ValidationException::withMessages(['pos_order_id' => 'Invoice does not belong to selected customer.']);
                }

                if ((float) $data['amount'] > (float) $invoice->due_amount) {
                    throw ValidationException::withMessages(['amount' => 'Payment amount cannot exceed invoice due amount.']);
                }
            }

            $payment = CustomerPayment::create([
                'customer_id' => $data['customer_id'],
                'pos_order_id' => $data['pos_order_id'] ?? null,
                'payment_date' => $data['payment_date'],
                'reference_no' => $data['reference_no'] ?: FinanceNumber::next('CRV', CustomerPayment::class),
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

            $this->postingService->postCustomerPayment($payment, $request->user()?->id);
        });

        return redirect()->route('customer-payments.index')->with('success', 'Customer payment saved successfully.');
    }

    public function show(CustomerPayment $customerPayment)
    {
        $customerPayment->load(['customer', 'posOrder', 'account']);

        return view('customer-payments.show', ['payment' => $customerPayment]);
    }

    public function receipt(CustomerPayment $customerPayment)
    {
        $customerPayment->load(['customer', 'posOrder', 'account']);

        return view('customer-payments.receipt', ['payment' => $customerPayment]);
    }

    public function payable()
    {
        $customers = Customer::orderBy('full_name')
            ->with(['posOrders' => fn ($q) => $q->where('status', 'completed')->where('due_amount', '>', 0)])
            ->get()
            ->filter(fn ($c) => $c->getPendingAmount() > 0);

        return view('customer-payable.index', compact('customers'));
    }

    public function payableCreate(Customer $customer)
    {
        $customer->load(['posOrders' => fn ($q) => $q->where('status', 'completed')->where('due_amount', '>', 0)->latest()]);

        return view('customer-payable.create', compact('customer'));
    }
}
