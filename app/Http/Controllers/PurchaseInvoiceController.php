<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePurchaseInvoiceRequest;
use App\Http\Requests\UpdatePurchaseInvoiceRequest;
use App\Models\Product;
use App\Models\PurchaseInvoice;
use App\Models\Supplier;
use App\Services\Accounting\PostingService;
use App\Support\FinanceNumber;
use Illuminate\Support\Facades\DB;

class PurchaseInvoiceController extends Controller
{
    public function __construct(private readonly PostingService $postingService)
    {
    }

    public function index()
    {
        $invoices = PurchaseInvoice::with('supplier')
            ->when(request('supplier_id'), fn ($q, $supplierId) => $q->where('supplier_id', $supplierId))
            ->when(request('payment_status'), fn ($q, $status) => $q->where('payment_status', $status))
            ->when(request('status'), fn ($q, $status) => $q->where('status', $status))
            ->when(request('from_date'), fn ($q, $d) => $q->whereDate('invoice_date', '>=', $d))
            ->when(request('to_date'), fn ($q, $d) => $q->whereDate('invoice_date', '<=', $d))
            ->latest('invoice_date')
            ->paginate(20)
            ->withQueryString();

        $suppliers = Supplier::where('is_active', true)->orderBy('full_name')->get();

        return view('purchases.index', compact('invoices', 'suppliers'));
    }

    public function create()
    {
        $suppliers = Supplier::where('is_active', true)->orderBy('full_name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();

        return view('purchases.create', compact('suppliers', 'products'));
    }

    public function store(StorePurchaseInvoiceRequest $request)
    {
        $data = $request->validated();

        $invoice = DB::transaction(function () use ($data, $request) {
            $computed = $this->computeTotals($data);

            $invoice = PurchaseInvoice::create([
                'invoice_number' => FinanceNumber::next('PINV', PurchaseInvoice::class, 'invoice_number'),
                'supplier_id' => $data['supplier_id'],
                'invoice_date' => $data['invoice_date'],
                'subtotal' => $computed['subtotal'],
                'discount_amount' => $computed['discount_amount'],
                'tax_amount' => $computed['tax_amount'],
                'total' => $computed['total'],
                'paid_amount' => $computed['paid_amount'],
                'due_amount' => $computed['due_amount'],
                'payment_status' => $computed['payment_status'],
                'status' => $data['status'] ?? PurchaseInvoice::STATUS_DRAFT,
                'notes' => $data['notes'] ?? null,
                'created_by' => $request->user()?->id,
            ]);

            foreach ($computed['lines'] as $line) {
                $invoice->items()->create($line);
            }

            if ($invoice->status === PurchaseInvoice::STATUS_POSTED) {
                $this->postInvoice($invoice, $request->user()?->id);
            }

            return $invoice;
        });

        return redirect()->route('purchases.show', $invoice)->with('success', 'Purchase invoice saved.');
    }

    public function show(PurchaseInvoice $purchase)
    {
        $purchase->load(['supplier', 'items.product', 'supplierPayments']);

        return view('purchases.show', ['invoice' => $purchase]);
    }

    public function edit(PurchaseInvoice $purchase)
    {
        if ($purchase->status !== PurchaseInvoice::STATUS_DRAFT) {
            return redirect()->route('purchases.show', $purchase)->with('error', 'Only draft purchase invoices can be edited.');
        }

        $purchase->load('items');
        $suppliers = Supplier::where('is_active', true)->orderBy('full_name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();

        return view('purchases.edit', ['invoice' => $purchase, 'suppliers' => $suppliers, 'products' => $products]);
    }

    public function update(UpdatePurchaseInvoiceRequest $request, PurchaseInvoice $purchase)
    {
        if ($purchase->status !== PurchaseInvoice::STATUS_DRAFT) {
            return redirect()->route('purchases.show', $purchase)->with('error', 'Only draft purchase invoices can be edited.');
        }

        $data = $request->validated();

        DB::transaction(function () use ($data, $request, $purchase) {
            $computed = $this->computeTotals($data);

            $purchase->update([
                'supplier_id' => $data['supplier_id'],
                'invoice_date' => $data['invoice_date'],
                'subtotal' => $computed['subtotal'],
                'discount_amount' => $computed['discount_amount'],
                'tax_amount' => $computed['tax_amount'],
                'total' => $computed['total'],
                'paid_amount' => $computed['paid_amount'],
                'due_amount' => $computed['due_amount'],
                'payment_status' => $computed['payment_status'],
                'notes' => $data['notes'] ?? null,
            ]);

            $purchase->items()->delete();
            foreach ($computed['lines'] as $line) {
                $purchase->items()->create($line);
            }

            if (($data['status'] ?? PurchaseInvoice::STATUS_DRAFT) === PurchaseInvoice::STATUS_POSTED) {
                $purchase->update(['status' => PurchaseInvoice::STATUS_POSTED]);
                $this->postInvoice($purchase->fresh('items.product'), $request->user()?->id);
            }
        });

        return redirect()->route('purchases.show', $purchase)->with('success', 'Purchase invoice updated.');
    }

    public function post(PurchaseInvoice $purchase)
    {
        if ($purchase->status !== PurchaseInvoice::STATUS_DRAFT) {
            return back()->with('error', 'Only draft invoice can be posted.');
        }

        DB::transaction(function () use ($purchase) {
            $purchase->load('items.product');
            $purchase->update(['status' => PurchaseInvoice::STATUS_POSTED]);
            $this->postInvoice($purchase, auth()->id());
        });

        return redirect()->route('purchases.show', $purchase)->with('success', 'Purchase invoice posted successfully.');
    }

    private function postInvoice(PurchaseInvoice $invoice, ?int $createdBy): void
    {
        foreach ($invoice->items as $item) {
            $item->product->increment('quantity', $item->quantity);
            $item->product->update(['cost_price' => $item->cost_price]);
        }

        $this->postingService->postPurchase($invoice, $createdBy);
    }

    private function computeTotals(array $data): array
    {
        $lines = [];
        $subtotal = 0;

        foreach ($data['items'] as $item) {
            $lineTotal = ((float) $item['cost_price']) * ((int) $item['quantity']);
            $subtotal += $lineTotal;

            $product = Product::findOrFail((int) $item['product_id']);
            $lines[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'cost_price' => (float) $item['cost_price'],
                'quantity' => (int) $item['quantity'],
                'line_total' => $lineTotal,
            ];
        }

        $discount = (float) ($data['discount_amount'] ?? 0);
        $tax = (float) ($data['tax_amount'] ?? 0);
        $total = max(0, ($subtotal - $discount) + $tax);
        $paid = min((float) ($data['paid_amount'] ?? 0), $total);
        $due = $total - $paid;

        return [
            'lines' => $lines,
            'subtotal' => $subtotal,
            'discount_amount' => $discount,
            'tax_amount' => $tax,
            'total' => $total,
            'paid_amount' => $paid,
            'due_amount' => $due,
            'payment_status' => $due <= 0 ? 'paid' : ($paid > 0 ? 'partial' : 'unpaid'),
        ];
    }
}
