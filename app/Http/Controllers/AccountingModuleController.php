<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Services\Accounting\ManualAccountingEntryService;
use App\Support\FinanceNumber;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use RuntimeException;
use Throwable;

class AccountingModuleController extends Controller
{
    private const MODULES = [
        'sales' => [
            'title' => 'Sales',
            'heading' => 'Sales Accounting',
            'subtitle' => 'Create standalone sales entries.',
            'route' => 'accounting.sales',
            'store_route' => 'accounting.sales.store',
            'icon' => 'ti ti-report-money',
            'tone' => 'primary',
            'prefix' => 'SAL',
            'voucher_types' => ['manual_sales'],
            'type_labels' => ['manual_sales' => 'Sale'],
            'party_label' => 'Customer / Party',
            'amount_label' => 'Sale Amount',
            'paid_label' => 'Received Amount',
            'show_paid_amount' => true,
            'show_payment_account' => true,
            'show_purchase_account' => false,
            'empty' => 'No sales entries found.',
        ],
        'purchase' => [
            'title' => 'Purchase',
            'heading' => 'Purchase Accounting',
            'subtitle' => 'Create standalone purchase entries.',
            'route' => 'accounting.purchase',
            'store_route' => 'accounting.purchase.store',
            'icon' => 'ti ti-file-invoice',
            'tone' => 'warning',
            'prefix' => 'PUR',
            'voucher_types' => ['manual_purchase'],
            'type_labels' => ['manual_purchase' => 'Purchase'],
            'party_label' => 'Supplier / Party',
            'amount_label' => 'Purchase Amount',
            'paid_label' => 'Paid Amount',
            'show_paid_amount' => true,
            'show_payment_account' => true,
            'show_purchase_account' => true,
            'empty' => 'No purchase entries found.',
        ],
        'receivable' => [
            'title' => 'Receivable',
            'heading' => 'Receivable Accounting',
            'subtitle' => 'Create standalone customer receivable entries.',
            'route' => 'accounting.receivable',
            'store_route' => 'accounting.receivable.store',
            'icon' => 'ti ti-receipt-2',
            'tone' => 'info',
            'prefix' => 'REC',
            'voucher_types' => ['manual_receivable_invoice', 'manual_receivable_receipt'],
            'type_labels' => [
                'manual_receivable_invoice' => 'Invoice',
                'manual_receivable_receipt' => 'Receipt',
            ],
            'actions' => [
                'invoice' => 'Invoice',
                'receipt' => 'Receipt',
            ],
            'action_visibility' => [
                'invoice' => ['payment' => false, 'purchase' => false],
                'receipt' => ['payment' => true, 'purchase' => false],
            ],
            'party_label' => 'Customer / Party',
            'amount_label' => 'Amount',
            'show_paid_amount' => false,
            'show_payment_account' => false,
            'show_purchase_account' => false,
            'empty' => 'No receivable entries found.',
        ],
        'payable' => [
            'title' => 'Payable',
            'heading' => 'Payable Accounting',
            'subtitle' => 'Create standalone supplier payable entries.',
            'route' => 'accounting.payable',
            'store_route' => 'accounting.payable.store',
            'icon' => 'ti ti-file-dollar',
            'tone' => 'danger',
            'prefix' => 'PAY',
            'voucher_types' => ['manual_payable_bill', 'manual_payable_payment'],
            'type_labels' => [
                'manual_payable_bill' => 'Bill',
                'manual_payable_payment' => 'Payment',
            ],
            'actions' => [
                'bill' => 'Bill',
                'payment' => 'Payment',
            ],
            'action_visibility' => [
                'bill' => ['payment' => false, 'purchase' => true],
                'payment' => ['payment' => true, 'purchase' => false],
            ],
            'party_label' => 'Supplier / Party',
            'amount_label' => 'Amount',
            'show_paid_amount' => false,
            'show_payment_account' => false,
            'show_purchase_account' => false,
            'empty' => 'No payable entries found.',
        ],
    ];

    public function __construct(private readonly ManualAccountingEntryService $manualPostingService)
    {
    }

    public function sales(): View
    {
        return $this->module('sales');
    }

    public function storeSales(Request $request): RedirectResponse
    {
        return $this->storeModule($request, 'sales');
    }

    public function purchase(): View
    {
        return $this->module('purchase');
    }

    public function storePurchase(Request $request): RedirectResponse
    {
        return $this->storeModule($request, 'purchase');
    }

    public function receivable(): View
    {
        return $this->module('receivable');
    }

    public function storeReceivable(Request $request): RedirectResponse
    {
        return $this->storeModule($request, 'receivable');
    }

    public function payable(): View
    {
        return $this->module('payable');
    }

    public function storePayable(Request $request): RedirectResponse
    {
        return $this->storeModule($request, 'payable');
    }

    private function module(string $key): View
    {
        $module = self::MODULES[$key];
        $baseQuery = $this->moduleEntryQuery($module);
        $entries = (clone $baseQuery)
            ->with('lines.account')
            ->latest('entry_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $totalAmount = JournalEntryLine::query()
            ->whereHas('journalEntry', fn ($query) => $this->applyModuleScope($query, $module))
            ->sum('debit');

        return view('accounting.module', [
            'module' => $module,
            'modules' => self::MODULES,
            'activeKey' => $key,
            'entries' => $entries,
            'paymentAccounts' => $this->paymentAccounts(),
            'purchaseAccounts' => $this->purchaseAccounts(),
            'summaryItems' => [
                ['label' => 'Total Amount', 'value' => 'PKR ' . number_format((float) $totalAmount, 2), 'icon' => 'ti ti-cash'],
                ['label' => 'Documents', 'value' => (string) (clone $baseQuery)->count(), 'icon' => 'ti ti-file-text'],
                ['label' => 'Posted Entries', 'value' => (string) (clone $baseQuery)->where('status', 'posted')->count(), 'icon' => 'ti ti-circle-check'],
                ['label' => 'Current Page', 'value' => (string) $entries->count(), 'icon' => 'ti ti-list'],
            ],
        ]);
    }

    private function storeModule(Request $request, string $key): RedirectResponse
    {
        $module = self::MODULES[$key];
        $data = $this->validateModuleRequest($request, $module);

        try {
            $voucherType = $this->voucherType($key, $data);
            $referenceNo = $data['reference_no'] ?: FinanceNumber::next((string) $module['prefix'], JournalEntry::class);
            $description = $this->entryDescription($module, $data, $voucherType);

            $journal = $this->manualPostingService->post(
                $data['entry_date'],
                $referenceNo,
                $voucherType,
                $description,
                $this->buildLines($key, $data),
                $request->user()?->id
            );
        } catch (Throwable $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route($module['route'])
            ->with('success', $module['title'] . ' entry ' . $journal->reference_no . ' saved and posted.');
    }

    private function validateModuleRequest(Request $request, array $module): array
    {
        return $request->validate([
            'entry_date' => ['required', 'date'],
            'reference_no' => ['nullable', 'string', 'max:50'],
            'entry_action' => isset($module['actions'])
                ? ['required', Rule::in(array_keys($module['actions']))]
                : ['nullable'],
            'party_name' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'payment_account_id' => ['nullable', 'exists:accounts,id'],
            'purchase_account_id' => ['nullable', 'exists:accounts,id'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
    }

    private function buildLines(string $key, array $data): array
    {
        $amount = round((float) $data['amount'], 2);
        $paid = min(round((float) ($data['paid_amount'] ?? 0), 2), $amount);
        $due = round($amount - $paid, 2);
        $paymentAccountId = (int) ($data['payment_account_id'] ?: $this->systemAccountId(Account::CODE_CASH));
        $purchaseAccountId = (int) ($data['purchase_account_id'] ?: $this->systemAccountId(Account::CODE_INVENTORY));
        $receivableAccountId = $this->systemAccountId(Account::CODE_RECEIVABLE);
        $payableAccountId = $this->systemAccountId(Account::CODE_PAYABLE);
        $salesAccountId = $this->systemAccountId(Account::CODE_SALES_REVENUE);

        if ($key === 'sales') {
            return array_values(array_filter([
                $paid > 0 ? ['account_id' => $paymentAccountId, 'debit' => $paid, 'credit' => 0, 'description' => 'Sales receipt'] : null,
                $due > 0 ? ['account_id' => $receivableAccountId, 'debit' => $due, 'credit' => 0, 'description' => 'Sales receivable'] : null,
                ['account_id' => $salesAccountId, 'debit' => 0, 'credit' => $amount, 'description' => 'Sales revenue'],
            ]));
        }

        if ($key === 'purchase') {
            return array_values(array_filter([
                ['account_id' => $purchaseAccountId, 'debit' => $amount, 'credit' => 0, 'description' => 'Purchase booked'],
                $paid > 0 ? ['account_id' => $paymentAccountId, 'debit' => 0, 'credit' => $paid, 'description' => 'Purchase payment'] : null,
                $due > 0 ? ['account_id' => $payableAccountId, 'debit' => 0, 'credit' => $due, 'description' => 'Purchase payable'] : null,
            ]));
        }

        if ($key === 'receivable') {
            if (($data['entry_action'] ?? 'invoice') === 'receipt') {
                return [
                    ['account_id' => $paymentAccountId, 'debit' => $amount, 'credit' => 0, 'description' => 'Customer receipt'],
                    ['account_id' => $receivableAccountId, 'debit' => 0, 'credit' => $amount, 'description' => 'Receivable settlement'],
                ];
            }

            return [
                ['account_id' => $receivableAccountId, 'debit' => $amount, 'credit' => 0, 'description' => 'Receivable invoice'],
                ['account_id' => $salesAccountId, 'debit' => 0, 'credit' => $amount, 'description' => 'Revenue against receivable'],
            ];
        }

        if (($data['entry_action'] ?? 'bill') === 'payment') {
            return [
                ['account_id' => $payableAccountId, 'debit' => $amount, 'credit' => 0, 'description' => 'Payable settlement'],
                ['account_id' => $paymentAccountId, 'debit' => 0, 'credit' => $amount, 'description' => 'Supplier payment'],
            ];
        }

        return [
            ['account_id' => $purchaseAccountId, 'debit' => $amount, 'credit' => 0, 'description' => 'Payable bill'],
            ['account_id' => $payableAccountId, 'debit' => 0, 'credit' => $amount, 'description' => 'Payable recognized'],
        ];
    }

    private function voucherType(string $key, array $data): string
    {
        return match ($key) {
            'sales' => 'manual_sales',
            'purchase' => 'manual_purchase',
            'receivable' => ($data['entry_action'] ?? 'invoice') === 'receipt'
                ? 'manual_receivable_receipt'
                : 'manual_receivable_invoice',
            'payable' => ($data['entry_action'] ?? 'bill') === 'payment'
                ? 'manual_payable_payment'
                : 'manual_payable_bill',
            default => throw new RuntimeException('Unknown accounting module.'),
        };
    }

    private function entryDescription(array $module, array $data, string $voucherType): string
    {
        $typeLabel = $module['type_labels'][$voucherType] ?? $module['title'];
        $description = $module['title'] . ' ' . $typeLabel . ' - ' . $data['party_name'];

        if (! empty($data['notes'])) {
            $description .= ' - ' . $data['notes'];
        }

        return $description;
    }

    private function moduleEntryQuery(array $module)
    {
        return $this->applyModuleScope(JournalEntry::query(), $module);
    }

    private function applyModuleScope($query, array $module)
    {
        return $query
            ->whereIn('voucher_type', $module['voucher_types'])
            ->when(request('from_date'), fn ($query, $date) => $query->whereDate('entry_date', '>=', $date))
            ->when(request('to_date'), fn ($query, $date) => $query->whereDate('entry_date', '<=', $date))
            ->when(request('search'), function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('reference_no', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%');
                });
            });
    }

    private function paymentAccounts()
    {
        $accounts = Account::where('is_active', true)
            ->whereIn('code', [Account::CODE_CASH, Account::CODE_BANK])
            ->orderBy('code')
            ->get();

        return $accounts->isNotEmpty()
            ? $accounts
            : Account::where('is_active', true)->where('type', 'asset')->orderBy('code')->get();
    }

    private function purchaseAccounts()
    {
        $accounts = Account::where('is_active', true)
            ->whereIn('type', ['asset', 'expense'])
            ->whereNotIn('code', [Account::CODE_CASH, Account::CODE_BANK, Account::CODE_RECEIVABLE])
            ->orderBy('code')
            ->get();

        return $accounts->isNotEmpty()
            ? $accounts
            : Account::where('is_active', true)->orderBy('code')->get();
    }

    private function systemAccountId(string $code): int
    {
        $id = Account::where('code', $code)->value('id');

        if (! $id) {
            throw new RuntimeException('System account not found for code: ' . $code);
        }

        return (int) $id;
    }
}
