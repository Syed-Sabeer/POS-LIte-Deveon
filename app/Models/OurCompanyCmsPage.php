<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OurCompanyCmsPage extends Model
{
    use HasFactory;
    protected $table = 'our_company_cms_pages';

    protected $fillable = [
        'tab_title',
        'heading',
        'description',
        'button_text',
        'button_link',
        'image',
        'card_title_1',
        'card_value_1',
        'card_title_2',
        'card_value_2',
        'card_title_3',
        'card_value_3',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
