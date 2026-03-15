<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CmsServicePage extends Model
{
    use HasFactory;

    protected $table = 'cms_service_pages';

    protected $fillable = [
        'banner_image', 'banner_heading', 'banner_description', 'banner_button_link',
        'company_heading', 'company_description', 'company_video', 'company_subheading', 
        'company_button_title', 'company_button_link',
        'blog_tab_title', 'blog_heading', 'blog_description',
        'service_main_image', 'choose_image', 'choose_heading',
        'choose_tab_title_1', 'choose_tab_value_1', 'choose_tab_title_2', 'choose_tab_value_2',
        'choose_button_title', 'choose_button_link',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
