<?php

namespace App\Models\V2;

use Illuminate\Database\Eloquent\Model;

class UserPermission extends Model
{
    protected $table = 'v2_user_permissions';

    protected $fillable = [
        'user_id',
        'permission_key',
        'can_view',
        'can_insert',
        'can_edit',
        'can_delete',
    ];

    protected $casts = [
        'can_view' => 'boolean',
        'can_insert' => 'boolean',
        'can_edit' => 'boolean',
        'can_delete' => 'boolean',
    ];
}
