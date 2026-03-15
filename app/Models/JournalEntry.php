<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'entry_date',
        'reference_no',
        'voucher_type',
        'voucher_id',
        'description',
        'created_by',
    ];

    protected $casts = [
        'entry_date' => 'date',
    ];

    public function lines()
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
