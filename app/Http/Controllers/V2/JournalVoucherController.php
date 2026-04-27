<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Models\V2\Account;
use App\Models\V2\Voucher;
use App\Services\V2\NumberService;
use App\Services\V2\PostingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JournalVoucherController extends Controller
{
    public function __construct(private readonly NumberService $numbers, private readonly PostingService $posting)
    {
    }

    public function index(Request $request)
    {
        $vouchers = Voucher::where('type', 'journal')
            ->when($request->search, fn ($q, $search) => $q->where('voucher_no', 'like', "%{$search}%")->orWhere('remarks', 'like', "%{$search}%"))
            ->latest('voucher_date')
            ->paginate(20)
            ->withQueryString();

        return view('v2.journal.index', compact('vouchers'));
    }

    public function create()
    {
        return view('v2.journal.form', ['accounts' => Account::where('is_active', true)->orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['lines'] = array_values(array_filter($data['lines'], function ($line) {
            return ((float) ($line['debit'] ?? 0) > 0) || ((float) ($line['credit'] ?? 0) > 0);
        }));

        if (count($data['lines']) < 2) {
            return back()->withInput()->with('error', 'Enter at least two non-zero journal lines.');
        }

        $totals = $this->totals($data);

        if ($totals['debit'] <= 0 || abs($totals['debit'] - $totals['credit']) > 0.01) {
            return back()->withInput()->with('error', 'Total debit must equal total credit.');
        }

        $voucher = DB::transaction(function () use ($data, $request, $totals) {
            $voucher = Voucher::create([
                'type' => 'journal',
                'voucher_no' => $data['voucher_no'] ?: $this->numbers->dated('JRV', Voucher::class, 'voucher_no'),
                'voucher_date' => $data['voucher_date'],
                'currency_rate' => $data['currency_rate'] ?? 1,
                'amount' => $totals['debit'],
                'remarks' => $data['remarks'] ?? null,
                'created_by' => $request->user()?->id,
            ]);

            foreach ($data['lines'] as $line) {
                $account = Account::findOrFail((int) $line['account_id']);
                $voucher->lines()->create([
                    'account_id' => $account->id,
                    'account_code' => $account->code,
                    'account_name' => $account->name,
                    'particulars' => $line['particulars'] ?? null,
                    'post_date' => $line['post_date'] ?? $data['voucher_date'],
                    'debit' => (float) ($line['debit'] ?? 0),
                    'credit' => (float) ($line['credit'] ?? 0),
                ]);
            }

            $this->posting->postVoucher($voucher, $request->user()?->id);

            return $voucher;
        });

        return redirect()->route('v2.journal.show', $voucher)->with('success', 'Journal voucher saved.');
    }

    public function show(Voucher $voucher)
    {
        abort_unless($voucher->type === 'journal', 404);
        $voucher->load('lines.account');

        return view('v2.journal.show', compact('voucher'));
    }

    public function print(Voucher $voucher)
    {
        abort_unless($voucher->type === 'journal', 404);
        $voucher->load('lines.account');

        return view('v2.prints.journal', compact('voucher'));
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'voucher_no' => ['nullable', 'string', 'max:50'],
            'voucher_date' => ['required', 'date'],
            'currency_rate' => ['nullable', 'numeric', 'min:0.0001'],
            'remarks' => ['nullable', 'string'],
            'lines' => ['required', 'array', 'min:2'],
            'lines.*.account_id' => ['required', 'exists:v2_accounts,id'],
            'lines.*.particulars' => ['nullable', 'string'],
            'lines.*.post_date' => ['nullable', 'date'],
            'lines.*.debit' => ['nullable', 'numeric', 'min:0'],
            'lines.*.credit' => ['nullable', 'numeric', 'min:0'],
        ]);
    }

    private function totals(array $data): array
    {
        return [
            'debit' => array_sum(array_map(fn ($line) => (float) ($line['debit'] ?? 0), $data['lines'])),
            'credit' => array_sum(array_map(fn ($line) => (float) ($line['credit'] ?? 0), $data['lines'])),
        ];
    }
}
