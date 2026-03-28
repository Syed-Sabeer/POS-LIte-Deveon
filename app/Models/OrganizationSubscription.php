<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class OrganizationSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_name',
        'organization_email',
        'stripe_customer_id',
        'stripe_subscription_id',
        'status',
        'trial_starts_at',
        'trial_ends_at',
        'current_period_start',
        'current_period_end',
        'amount_cents',
        'currency',
        'plan_interval',
        'last_payment_at',
    ];

    protected $casts = [
        'trial_starts_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'current_period_start' => 'datetime',
        'current_period_end' => 'datetime',
        'last_payment_at' => 'datetime',
    ];

    public const ACTIVE_STATUSES = ['active'];

    public static function singleton(): self
    {
        return self::firstOrCreate([
            'id' => 1,
        ], [
            'organization_name' => config('app.name'),
            'status' => 'trialing',
            'trial_starts_at' => now(),
            'trial_ends_at' => now()->addDays(7),
            'amount_cents' => 2000,
            'currency' => 'usd',
            'plan_interval' => 'month',
        ]);
    }

    public function onTrial(): bool
    {
        return $this->trial_ends_at instanceof Carbon
            && now()->lte($this->trial_ends_at);
    }

    public function isActivePaid(): bool
    {
        if (! in_array((string) $this->status, self::ACTIVE_STATUSES, true)) {
            return false;
        }

        if ($this->current_period_end instanceof Carbon) {
            return now()->lte($this->current_period_end);
        }

        // Some Stripe account configurations may not return period_end immediately.
        // Treat recently paid active subscriptions as accessible.
        if ($this->last_payment_at instanceof Carbon) {
            return now()->diffInDays($this->last_payment_at) <= 35;
        }

        return true;
    }

    public function hasAccess(): bool
    {
        return $this->onTrial() || $this->isActivePaid();
    }

    public function trialDaysRemaining(): int
    {
        if (! $this->onTrial()) {
            return 0;
        }

        return max(0, now()->startOfDay()->diffInDays($this->trial_ends_at->copy()->startOfDay(), false) + 1);
    }
}
