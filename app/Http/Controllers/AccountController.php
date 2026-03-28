<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    public function index()
    {
        $accounts = Account::with('parent')
            ->orderByRaw('COALESCE(account_type, type)')
            ->orderBy('code')
            ->paginate(30);

        $parents = Account::whereNull('parent_id')->orderBy('name')->get();

        return view('accounts.index', compact('accounts', 'parents'));
    }

    public function tree()
    {
        $accounts = Account::where('is_active', true)->orderBy('code')->get();

        $nodes = $accounts->map(function (Account $account) {
            return [
                'id' => $account->id,
                'parent_id' => $account->parent_id,
                'code' => $account->code,
                'name' => $account->name,
                'type' => $account->account_type ?: $account->type,
            ];
        })->all();

        $childrenMap = [];
        foreach ($nodes as $node) {
            $childrenMap[(int) ($node['parent_id'] ?? 0)][] = $node;
        }

        $build = function (int $parentId) use (&$build, $childrenMap): array {
            $items = $childrenMap[$parentId] ?? [];
            foreach ($items as &$item) {
                $item['children'] = $build((int) $item['id']);
            }
            return $items;
        };

        $tree = $build(0);

        return view('accounts.tree', compact('tree'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:accounts,code'],
            'account_type' => ['required', Rule::in(['asset', 'liability', 'equity', 'income', 'expense'])],
            'account_subtype' => ['nullable', 'string', 'max:100'],
            'normal_balance' => ['required', Rule::in(['debit', 'credit'])],
            'parent_id' => ['nullable', 'exists:accounts,id'],
            'is_active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        Account::create([
            'name' => $validated['name'],
            'code' => $validated['code'],
            'type' => $validated['account_type'],
            'account_type' => $validated['account_type'],
            'account_subtype' => $validated['account_subtype'] ?? null,
            'normal_balance' => $validated['normal_balance'],
            'parent_id' => $validated['parent_id'] ?? null,
            'is_system' => false,
            'is_active' => $request->boolean('is_active', true),
            'notes' => $validated['notes'] ?? null,
        ]);

        return back()->with('success', 'Account created successfully.');
    }

    public function update(Request $request, Account $account)
    {
        if ($account->is_system) {
            return back()->with('error', 'System account cannot be modified.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:accounts,code,' . $account->id],
            'account_type' => ['required', Rule::in(['asset', 'liability', 'equity', 'income', 'expense'])],
            'account_subtype' => ['nullable', 'string', 'max:100'],
            'normal_balance' => ['required', Rule::in(['debit', 'credit'])],
            'parent_id' => ['nullable', 'exists:accounts,id'],
            'is_active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        $account->update([
            'name' => $validated['name'],
            'code' => $validated['code'],
            'type' => $validated['account_type'],
            'account_type' => $validated['account_type'],
            'account_subtype' => $validated['account_subtype'] ?? null,
            'normal_balance' => $validated['normal_balance'],
            'parent_id' => $validated['parent_id'] ?? null,
            'is_active' => $request->boolean('is_active', true),
            'notes' => $validated['notes'] ?? null,
        ]);

        return back()->with('success', 'Account updated successfully.');
    }
}
