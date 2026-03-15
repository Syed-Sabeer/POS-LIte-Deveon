<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomerPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'exists:customers,id'],
            'pos_order_id' => ['nullable', 'exists:pos_orders,id'],
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
