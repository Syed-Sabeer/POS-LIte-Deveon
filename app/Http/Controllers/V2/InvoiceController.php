<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Models\V2\Account;
use App\Models\V2\Invoice;
use App\Models\V2\Item;
use App\Services\V2\AmountInWords;
use App\Services\V2\NumberService;
use App\Services\V2\PostingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class InvoiceController extends Controller
{
    public function __construct(
        private readonly NumberService $numbers,
        private readonly PostingService $posting,
        private readonly AmountInWords $words
    ) {
    }

    public function index(Request $request, string $type)
    {
        $this->validateType($type);
        $invoices = Invoice::with('account')
            ->where('type', $type)
            ->when($request->search, fn ($q, $search) => $q->where('voucher_no', 'like', "%{$search}%")->orWhere('party_name', 'like', "%{$search}%"))
            ->when($request->from_date, fn ($q, $date) => $q->whereDate('invoice_date', '>=', $date))
            ->when($request->to_date, fn ($q, $date) => $q->whereDate('invoice_date', '<=', $date))
            ->latest('invoice_date')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('v2.invoices.index', ['type' => $type, 'invoices' => $invoices]);
    }

    public function create(string $type)
    {
        $this->validateType($type);

        return view('v2.invoices.form', $this->formData($type));
    }

    public function store(Request $request, string $type)
    {
        $this->validateType($type);
        $data = $this->validated($request, $type);
        $prefix = $type === 'purchase' ? 'PINV' : 'SINV';

        $invoice = DB::transaction(function () use ($data, $request, $type, $prefix) {
            $computed = $this->compute($data, $type);
            $account = Account::findOrFail($data['account_id']);

            $invoice = Invoice::create([
                'type' => $type,
                'account_id' => $account->id,
                'party_name' => $data['party_name'] ?: $account->name,
                'voucher_no' => $data['voucher_no'] ?: $this->numbers->dated($prefix, Invoice::class, 'voucher_no'),
                'invoice_date' => $data['invoice_date'],
                'currency_rate' => $data['currency_rate'] ?? 1,
                'memo' => $data['memo'] ?? null,
                'gross_amount' => $computed['gross_amount'],
                'charges' => $computed['charges'],
                'discount' => $computed['discount'],
                'net_amount' => $computed['net_amount'],
                'received_amount' => $computed['received_amount'],
                'created_by' => $request->user()?->id,
            ]);

            foreach ($computed['items'] as $line) {
                $invoice->items()->create($line);
            }

            $this->posting->postInvoice($invoice, $request->user()?->id);

            return $invoice;
        });

        return redirect()->route($type === 'purchase' ? 'v2.purchase.show' : 'v2.sales.show', $invoice)->with('success', 'Invoice saved.');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['account.detail', 'items.item']);

        return view('v2.invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        $invoice->load('items');

        return view('v2.invoices.form', $this->formData($invoice->type) + ['invoice' => $invoice]);
    }

    public function update(Request $request, Invoice $invoice)
    {
        $data = $this->validated($request, $invoice->type);

        DB::transaction(function () use ($data, $request, $invoice) {
            $computed = $this->compute($data, $invoice->type);
            $account = Account::findOrFail($data['account_id']);

            $invoice->update([
                'account_id' => $account->id,
                'party_name' => $data['party_name'] ?: $account->name,
                'voucher_no' => $data['voucher_no'] ?: $invoice->voucher_no,
                'invoice_date' => $data['invoice_date'],
                'currency_rate' => $data['currency_rate'] ?? 1,
                'memo' => $data['memo'] ?? null,
                'gross_amount' => $computed['gross_amount'],
                'charges' => $computed['charges'],
                'discount' => $computed['discount'],
                'net_amount' => $computed['net_amount'],
                'received_amount' => $computed['received_amount'],
                'updated_by' => $request->user()?->id,
            ]);

            $invoice->items()->delete();
            foreach ($computed['items'] as $line) {
                $invoice->items()->create($line);
            }

            $this->posting->postInvoice($invoice->fresh('items.item'), $request->user()?->id);
        });

        return redirect()->route($invoice->type === 'purchase' ? 'v2.purchase.show' : 'v2.sales.show', $invoice)->with('success', 'Invoice updated.');
    }

    public function destroy(Invoice $invoice)
    {
        DB::transaction(function () use ($invoice) {
            $this->posting->clearSource('invoice', (int) $invoice->id);
            $invoice->items()->delete();
            $invoice->delete();
        });

        return redirect()->route($invoice->type === 'purchase' ? 'v2.purchase.index' : 'v2.sales.index')->with('success', 'Invoice deleted.');
    }

    public function print(Invoice $invoice, string $format = 'invoice')
    {
        $invoice->load(['account.detail', 'items.item']);

        return view('v2.prints.invoice', [
            'invoice' => $invoice,
            'format' => $format,
            'amountInWords' => $this->words->rupees((float) $invoice->net_amount),
        ]);
    }

    private function formData(string $type): array
    {
        $accountTypes = $type === 'purchase' ? [Account::TYPE_PAYABLE] : [Account::TYPE_RECEIVABLE];

        return [
            'type' => $type,
            'accounts' => Account::where('is_active', true)->whereIn('account_type', $accountTypes)->orderBy('name')->get(),
            'items' => Item::where('is_active', true)->orderBy('description')->get(),
        ];
    }

    private function validated(Request $request, string $type): array
    {
        return $request->validate([
            'account_id' => ['required', 'exists:v2_accounts,id'],
            'party_name' => ['nullable', 'string', 'max:255'],
            'voucher_no' => ['nullable', 'string', 'max:50'],
            'invoice_date' => ['required', 'date'],
            'currency_rate' => ['nullable', 'numeric', 'min:0.0001'],
            'memo' => ['nullable', 'string'],
            'charges' => ['nullable', 'numeric', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'received_amount' => [$type === 'sale' ? 'nullable' : 'exclude', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'exists:v2_items,id'],
            'items.*.item_detail' => ['nullable', 'string', 'max:255'],
            'items.*.qty' => ['required', 'numeric', 'min:0.001'],
            'items.*.packet' => ['nullable', 'numeric', 'min:0'],
            'items.*.rate' => ['required', 'numeric', 'min:0'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
        ]);
    }

    private function compute(array $data, string $type): array
    {
        $rows = [];
        $gross = 0;

        foreach ($data['items'] as $row) {
            $item = Item::findOrFail((int) $row['item_id']);
            $rate = (float) $row['rate'];
            $lineDiscount = (float) ($row['discount'] ?? 0);
            $discountedRate = max(0, $rate - $lineDiscount);
            $qty = (float) $row['qty'];
            $amount = $qty * $discountedRate;
            $gross += $amount;

            $rows[] = [
                'item_id' => $item->id,
                'item_code' => $item->code,
                'item_name' => $item->description,
                'item_detail' => $row['item_detail'] ?? null,
                'qty' => $qty,
                'packet' => (float) ($row['packet'] ?? 0),
                'rate' => $rate,
                'discount' => $lineDiscount,
                'discounted_rate' => $discountedRate,
                'amount' => $amount,
            ];
        }

        $charges = (float) ($data['charges'] ?? 0);
        $discount = (float) ($data['discount'] ?? 0);
        $net = max(0, $gross + $charges - $discount);

        return [
            'items' => $rows,
            'gross_amount' => $gross,
            'charges' => $charges,
            'discount' => min($discount, $gross + $charges),
            'net_amount' => $net,
            'received_amount' => $type === 'sale' ? min((float) ($data['received_amount'] ?? 0), $net) : 0,
        ];
    }

    private function validateType(string $type): void
    {
        abort_unless(in_array($type, ['purchase', 'sale'], true), 404);
    }
}
