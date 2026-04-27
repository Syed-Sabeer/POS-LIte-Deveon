<?php

namespace App\Models\V2;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes;

    protected $table = 'v2_categories';

    protected $fillable = ['name', 'is_active', 'created_by', 'updated_by'];

    protected $casts = ['is_active' => 'boolean'];
}
