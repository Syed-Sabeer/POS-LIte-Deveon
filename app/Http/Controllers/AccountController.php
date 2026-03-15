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
            ->orderBy('type')
            ->orderBy('code')
            ->paginate(30);

        $parents = Account::whereNull('parent_id')->orderBy('name')->get();

        return view('accounts.index', compact('accounts', 'parents'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:accounts,code'],
            'type' => ['required', Rule::in(['asset', 'liability', 'equity', 'income', 'expense'])],
            'parent_id' => ['nullable', 'exists:accounts,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        Account::create($validated + ['is_active' => $request->boolean('is_active', true)]);

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
            'type' => ['required', Rule::in(['asset', 'liability', 'equity', 'income', 'expense'])],
            'parent_id' => ['nullable', 'exists:accounts,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $account->update($validated + ['is_active' => $request->boolean('is_active', true)]);

        return back()->with('success', 'Account updated successfully.');
    }
}
