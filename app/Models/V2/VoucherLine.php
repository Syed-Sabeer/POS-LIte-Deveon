<?php

namespace App\Models\V2;

use Illuminate\Database\Eloquent\Model;

class VoucherLine extends Model
{
    protected $table = 'v2_voucher_lines';

    protected $fillable = [
        'voucher_id',
        'account_id',
        'account_code',
        'account_name',
        'particulars',
        'post_date',
        'debit',
        'credit',
    ];

    protected $casts = [
        'post_date' => 'date',
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
    ];

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
