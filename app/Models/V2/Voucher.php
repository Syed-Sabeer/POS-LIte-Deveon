<?php

namespace App\Models\V2;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Voucher extends Model
{
    use SoftDeletes;

    protected $table = 'v2_vouchers';

    protected $fillable = [
        'type',
        'voucher_no',
        'voucher_date',
        'post_date',
        'account_id',
        'contra_account_id',
        'particulars',
        'currency_rate',
        'amount',
        'remarks',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'voucher_date' => 'date',
        'post_date' => 'date',
        'currency_rate' => 'decimal:4',
        'amount' => 'decimal:2',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function contraAccount()
    {
        return $this->belongsTo(Account::class, 'contra_account_id');
    }

    public function lines()
    {
        return $this->hasMany(VoucherLine::class);
    }
}
