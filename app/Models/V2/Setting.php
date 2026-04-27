<?php

namespace App\Models\V2;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'v2_settings';

    protected $fillable = ['key', 'value'];
}
