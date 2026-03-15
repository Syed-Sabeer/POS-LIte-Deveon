<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HeroSectionCrud extends Model
{
    protected $table = 'hero_section_cruds';

    protected $fillable = [
        'tab_heading',
        'main_heading',
        'banner_image',
        'car_image',
        'car_name',
        'car_quantity',
        'visibility'
    ];
}
