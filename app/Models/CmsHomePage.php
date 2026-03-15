<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CmsHomePage extends Model
{
    use HasFactory;

    protected $table = 'cms_home_pages';

    protected $fillable = [
        'feature_heading',
        'feature_icon_1', 'feature_title_1', 'feature_detail_1',
        'feature_icon_2', 'feature_title_2', 'feature_detail_2',
        'feature_icon_3', 'feature_title_3', 'feature_detail_3',
        'feature_icon_4', 'feature_title_4', 'feature_detail_4',
        'feature_icon_5', 'feature_title_5', 'feature_detail_5',
        'feature_icon_6', 'feature_title_6', 'feature_detail_6',
        'feature_image',
        'play_store_app_link', 'app_store_app_link',
        'service_heading', 'service_description', 'service_image',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
