<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\PartyLedger;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::latest()->paginate(15);

        return view('customers.index', compact('customers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'opening_balance' => ['nullable', 'numeric', 'min:0'],
            'balance_type' => ['nullable', Rule::in(['dr', 'cr'])],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $customer = DB::transaction(function () use ($validated, $request) {
            $customer = Customer::create($validated + [
                'opening_balance' => $validated['opening_balance'] ?? 0,
                'balance_type' => $validated['balance_type'] ?? 'dr',
                'is_active' => $request->boolean('is_active', true),
            ]);

            if ((float) $customer->opening_balance > 0) {
                $debit = $customer->balance_type === 'dr' ? (float) $customer->opening_balance : 0.0;
                $credit = $customer->balance_type === 'cr' ? (float) $customer->opening_balance : 0.0;

                PartyLedger::create([
                    'party_type' => PartyLedger::TYPE_CUSTOMER,
                    'party_id' => $customer->id,
                    'entry_date' => now()->toDateString(),
                    'voucher_type' => 'opening_balance',
                    'reference_no' => 'CUS-OPEN-' . $customer->id,
                    'description' => 'Customer opening balance',
                    'debit' => $debit,
                    'credit' => $credit,
                    'balance' => $debit - $credit,
                    'created_by' => auth()->id(),
                ]);
            }

            return $customer;
        });

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'ok' => true,
                'message' => 'Customer created successfully.',
                'customer' => [
                    'id' => $customer->id,
                    'full_name' => $customer->full_name,
                    'company_name' => $customer->company_name,
                    'phone' => $customer->phone,
                ],
            ]);
        }

        return back()->with('success', 'Customer created successfully.');
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $customer->update($validated + [
            'is_active' => $request->boolean('is_active', false),
        ]);

        return redirect()->route('customers.index')->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer)
    {
        $hasPayments = CustomerPayment::query()->where('customer_id', $customer->id)->exists();
        if ($hasPayments) {
            return back()->withErrors(['delete' => 'Cannot delete customer with payment history.']);
        }

        $hasPartyLedgerEntries = PartyLedger::query()
            ->where('party_type', PartyLedger::TYPE_CUSTOMER)
            ->where('party_id', $customer->id)
            ->exists();

        if ($hasPartyLedgerEntries) {
            return back()->withErrors(['delete' => 'Cannot delete customer with ledger history.']);
        }

        try {
            $customer->delete();
        } catch (QueryException $exception) {
            return back()->withErrors(['delete' => 'Unable to delete customer because it is linked with existing records.']);
        }

        return back()->with('success', 'Customer deleted successfully.');
    }
}
