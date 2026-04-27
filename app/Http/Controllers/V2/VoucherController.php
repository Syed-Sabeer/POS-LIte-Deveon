<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Models\V2\Account;
use App\Models\V2\Voucher;
use App\Services\V2\NumberService;
use App\Services\V2\PostingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VoucherController extends Controller
{
    public function __construct(private readonly NumberService $numbers, private readonly PostingService $posting)
    {
    }

    public function index(Request $request, string $type)
    {
        $this->validateType($type);
        $vouchers = Voucher::with(['account', 'contraAccount'])
            ->where('type', $type)
            ->when($request->search, fn ($q, $search) => $q->where('voucher_no', 'like', "%{$search}%")->orWhere('particulars', 'like', "%{$search}%"))
            ->latest('voucher_date')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('v2.vouchers.index', compact('vouchers', 'type'));
    }

    public function create(string $type)
    {
        $this->validateType($type);

        return view('v2.vouchers.form', $this->formData($type));
    }

    public function store(Request $request, string $type)
    {
        $this->validateType($type);
        $data = $this->validated($request);
        $prefix = $type === 'receipt' ? 'RCV' : 'PAY';

        $voucher = DB::transaction(function () use ($data, $request, $type, $prefix) {
            $voucher = Voucher::create([
                'type' => $type,
                'voucher_no' => $data['voucher_no'] ?: $this->numbers->dated($prefix, Voucher::class, 'voucher_no'),
                'voucher_date' => $data['voucher_date'],
                'post_date' => $data['post_date'] ?? $data['voucher_date'],
                'account_id' => $data['account_id'],
                'contra_account_id' => $data['contra_account_id'],
                'particulars' => $data['particulars'] ?? null,
                'currency_rate' => $data['currency_rate'] ?? 1,
                'amount' => $data['amount'],
                'created_by' => $request->user()?->id,
            ]);

            $this->posting->postVoucher($voucher, $request->user()?->id);

            return $voucher;
        });

        return redirect()->route($type === 'receipt' ? 'v2.receipts.show' : 'v2.payments.show', $voucher)->with('success', 'Voucher saved.');
    }

    public function show(Voucher $voucher)
    {
        $voucher->load(['account', 'contraAccount']);

        return view('v2.vouchers.show', compact('voucher'));
    }

    public function edit(Voucher $voucher)
    {
        return view('v2.vouchers.form', $this->formData($voucher->type) + ['voucher' => $voucher]);
    }

    public function update(Request $request, Voucher $voucher)
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($data, $request, $voucher) {
            $data['voucher_no'] = $data['voucher_no'] ?: $voucher->voucher_no;
            $data['post_date'] = $data['post_date'] ?? $data['voucher_date'];
            $voucher->update($data + ['updated_by' => $request->user()?->id]);
            $this->posting->postVoucher($voucher, $request->user()?->id);
        });

        return redirect()->route($voucher->type === 'receipt' ? 'v2.receipts.show' : 'v2.payments.show', $voucher)->with('success', 'Voucher updated.');
    }

    public function destroy(Voucher $voucher)
    {
        DB::transaction(function () use ($voucher) {
            $this->posting->clearSource('voucher', (int) $voucher->id);
            $voucher->delete();
        });

        return redirect()->route($voucher->type === 'receipt' ? 'v2.receipts.index' : 'v2.payments.index')->with('success', 'Voucher deleted.');
    }

    public function print(Voucher $voucher)
    {
        $voucher->load(['account', 'contraAccount']);

        return view('v2.prints.voucher', compact('voucher'));
    }

    private function formData(string $type): array
    {
        return [
            'type' => $type,
            'accounts' => Account::where('is_active', true)->orderBy('name')->get(),
            'cashAccounts' => Account::where('is_active', true)->where('account_type', Account::TYPE_CASH_BANK)->orderBy('name')->get(),
        ];
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'voucher_no' => ['nullable', 'string', 'max:50'],
            'voucher_date' => ['required', 'date'],
            'post_date' => ['nullable', 'date'],
            'account_id' => ['required', 'exists:v2_accounts,id'],
            'contra_account_id' => ['required', 'exists:v2_accounts,id'],
            'particulars' => ['nullable', 'string'],
            'currency_rate' => ['nullable', 'numeric', 'min:0.0001'],
            'amount' => ['required', 'numeric', 'min:0.01'],
        ]);
    }

    private function validateType(string $type): void
    {
        abort_unless(in_array($type, ['receipt', 'payment'], true), 404);
    }
}
