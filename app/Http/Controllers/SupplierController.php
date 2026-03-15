<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Models\PartyLedger;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::query()
            ->when(request('q'), fn ($q, $value) => $q->where('full_name', 'like', "%{$value}%")->orWhere('company_name', 'like', "%{$value}%"))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store(StoreSupplierRequest $request)
    {
        DB::transaction(function () use ($request) {
            $supplier = Supplier::create($request->validated() + ['is_active' => $request->boolean('is_active', true)]);

            if ((float) $supplier->opening_balance > 0) {
                $debit = $supplier->balance_type === 'dr' ? (float) $supplier->opening_balance : 0.0;
                $credit = $supplier->balance_type === 'cr' ? (float) $supplier->opening_balance : 0.0;

                PartyLedger::create([
                    'party_type' => PartyLedger::TYPE_SUPPLIER,
                    'party_id' => $supplier->id,
                    'entry_date' => now()->toDateString(),
                    'voucher_type' => 'opening_balance',
                    'reference_no' => 'SUP-OPEN-' . $supplier->id,
                    'description' => 'Supplier opening balance',
                    'debit' => $debit,
                    'credit' => $credit,
                    'balance' => $debit - $credit,
                    'created_by' => auth()->id(),
                ]);
            }
        });

        return redirect()->route('suppliers.index')->with('success', 'Supplier created successfully.');
    }

    public function show(Supplier $supplier)
    {
        $supplier->loadCount(['purchaseInvoices', 'supplierPayments']);

        return view('suppliers.show', compact('supplier'));
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(UpdateSupplierRequest $request, Supplier $supplier)
    {
        $supplier->update($request->validated() + ['is_active' => $request->boolean('is_active', true)]);

        return redirect()->route('suppliers.index')->with('success', 'Supplier updated successfully.');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();

        return redirect()->route('suppliers.index')->with('success', 'Supplier deleted successfully.');
    }
}
