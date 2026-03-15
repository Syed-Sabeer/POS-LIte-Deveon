<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSubscriptionPlan extends Model
{
    use HasFactory;

    protected $table = 'user_subscription_plans';

    protected $fillable = [
        'title',
        'price',
        'duration',
        'duration_months',
        'is_unlimitedstreaming',
        'is_ads',
        'is_offline',
        'is_highquality',
        'is_unlimitedplaylist',
        'is_exclusivecontent',
        'is_prioritysupport',
        'is_family',
        'family_limit',
        'is_parentalcontrol',
    ];

    protected $casts = [
        'is_unlimitedstreaming' => 'boolean',
        'is_ads'               => 'boolean',
        'is_offline'           => 'boolean',
        'is_highquality'       => 'boolean',
        'is_unlimitedplaylist' => 'boolean',
        'is_exclusivecontent'  => 'boolean',
        'is_prioritysupport'   => 'boolean',
        'is_family'            => 'boolean',
        'is_parentalcontrol'   => 'boolean',
        'family_limit'         => 'integer',
        'duration_months'      => 'integer',
        'price'                => 'string', 
        'duration'             => 'string',
    ];
}
