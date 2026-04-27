<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Models\V2\Account;
use App\Services\V2\NumberService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    public function __construct(private readonly NumberService $numbers)
    {
    }

    public function index(Request $request)
    {
        $accounts = Account::with('detail')
            ->when($request->search, fn ($q, $search) => $q->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%")))
            ->when($request->account_type, fn ($q, $type) => $q->where('account_type', $type))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('v2.accounts.index', ['accounts' => $accounts, 'types' => Account::types()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['code'] = $this->numbers->accountCode(Account::prefixForType($data['account_type']), Account::class);
        $data['created_by'] = $request->user()?->id;
        $data['is_active'] = $request->boolean('is_active', true);

        $account = Account::create($data);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Account saved.',
                'account' => $account,
            ]);
        }

        return back()->with('success', 'Account saved.');
    }

    public function edit(Account $account)
    {
        return view('v2.accounts.edit', ['account' => $account, 'types' => Account::types()]);
    }

    public function update(Request $request, Account $account)
    {
        $data = $this->validated($request);
        $data['updated_by'] = $request->user()?->id;
        $data['is_active'] = $request->boolean('is_active');

        $account->update($data);

        return redirect()->route('v2.accounts.index')->with('success', 'Account updated.');
    }

    public function destroy(Account $account)
    {
        if ($account->is_system) {
            return back()->with('error', 'System accounts cannot be deleted.');
        }

        $account->delete();

        return back()->with('success', 'Account deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'account_type' => ['required', Rule::in(array_keys(Account::types()))],
            'name' => ['required', 'string', 'max:255'],
            'opening_date' => ['required', 'date'],
            'opening_amount' => ['nullable', 'numeric'],
            'currency_rate' => ['nullable', 'numeric', 'min:0.0001'],
        ]) + [
            'opening_amount' => 0,
            'currency_rate' => 1,
        ];
    }
}
