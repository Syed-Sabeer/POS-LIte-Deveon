<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSupplierPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'purchase_invoice_id' => ['nullable', 'exists:purchase_invoices,id'],
            'payment_date' => ['required', 'date'],
            'reference_no' => ['nullable', 'string', 'max:100'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', Rule::in(['cash', 'bank', 'card', 'upi'])],
            'account_id' => ['nullable', 'exists:accounts,id'],
            'allow_advance' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
