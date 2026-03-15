<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactCmsPage extends Model
{
    protected $table = 'contact_cms_pages';

    protected $fillable = [
        'tab_heading',
        'heading',
        'description',
        'number',
        'email',
        'address',
        'location_link',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
