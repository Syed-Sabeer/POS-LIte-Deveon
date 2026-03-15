<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactSubmission extends Model
{
    // If you're not using guarded or timestamps
    protected $table = 'contact_submissions';

    protected $fillable = [
        'fullname',
        'phone',
        'email',
        'subject',
        'message',
    ];

    public $timestamps = true;
}
