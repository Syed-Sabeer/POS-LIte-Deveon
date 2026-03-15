<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessSetting extends Model
{
    protected $table = 'business_settings';

    protected $fillable = [
        'light_logo_image',
        'dark_logo_image',
        'email',
        'address',
        'phone',
        'facebook_link',
        'youtube_link',
        'tiktok_link',
        'instagram_link',
        'footer_copyright_text',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
