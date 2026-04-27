<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Models\V2\Account;
use App\Models\V2\AccountDetail;
use Illuminate\Http\Request;

class AccountDetailController extends Controller
{
    public function index(Request $request)
    {
        $details = AccountDetail::with('account')
            ->when($request->search, fn ($q, $search) => $q->where('name', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%"))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $accounts = Account::where('is_active', true)->orderBy('name')->get();

        return view('v2.account-details.index', compact('details', 'accounts'));
    }

    public function store(Request $request)
    {
        AccountDetail::create($this->validated($request) + [
            'created_by' => $request->user()?->id,
        ]);

        return back()->with('success', 'Account details saved.');
    }

    public function edit(AccountDetail $accountDetail)
    {
        $accounts = Account::where('is_active', true)->orderBy('name')->get();

        return view('v2.account-details.edit', compact('accountDetail', 'accounts'));
    }

    public function update(Request $request, AccountDetail $accountDetail)
    {
        $accountDetail->update($this->validated($request) + [
            'updated_by' => $request->user()?->id,
        ]);

        return redirect()->route('v2.account-details.index')->with('success', 'Account details updated.');
    }

    public function destroy(AccountDetail $accountDetail)
    {
        $accountDetail->delete();

        return back()->with('success', 'Account details deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'account_id' => ['required', 'exists:v2_accounts,id'],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:100'],
            'fax' => ['nullable', 'string', 'max:100'],
            'credit_days' => ['nullable', 'integer', 'min:0'],
            'contact' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string'],
            'invoice_limit' => ['nullable', 'numeric', 'min:0'],
            'ledger_limit' => ['nullable', 'numeric', 'min:0'],
            'purchase_sale_sms_contacts' => ['nullable', 'array'],
            'payment_receipt_sms_contacts' => ['nullable', 'array'],
        ]);

        $data['purchase_sale_sms_contacts'] = array_values(array_filter($data['purchase_sale_sms_contacts'] ?? []));
        $data['payment_receipt_sms_contacts'] = array_values(array_filter($data['payment_receipt_sms_contacts'] ?? []));

        return $data;
    }
}
