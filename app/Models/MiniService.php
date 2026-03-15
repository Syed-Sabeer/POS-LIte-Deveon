<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MiniService extends Model
{
    use HasFactory;
    
    protected $table = 'mini_services';

    protected $fillable = [
        'title',
        'icon',
        'image',
        'visibility',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];


}
