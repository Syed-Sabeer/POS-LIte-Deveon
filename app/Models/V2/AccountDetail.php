<?php

namespace App\Models\V2;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountDetail extends Model
{
    use SoftDeletes;

    protected $table = 'v2_account_details';

    protected $fillable = [
        'account_id',
        'name',
        'address',
        'city',
        'phone',
        'fax',
        'credit_days',
        'contact',
        'remarks',
        'invoice_limit',
        'ledger_limit',
        'purchase_sale_sms_contacts',
        'payment_receipt_sms_contacts',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'credit_days' => 'integer',
        'invoice_limit' => 'decimal:2',
        'ledger_limit' => 'decimal:2',
        'purchase_sale_sms_contacts' => 'array',
        'payment_receipt_sms_contacts' => 'array',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
