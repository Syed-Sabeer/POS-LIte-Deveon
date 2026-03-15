<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyWelcome extends Model
{
    protected $table = 'company_welcomes';

    protected $fillable = [
        'tab_heading',
        'heading',
        'description',
        'button_text',
        'button_link',
        'tab_heading_1',
        'tab_value_1',
        'tab_heading_2',
        'tab_value_2',
        'tab_heading_3',
        'tab_value_3',
        'tab_heading_4',
        'tab_value_4',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
