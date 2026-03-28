<?php

namespace App\Http\Middleware;

use App\Models\OrganizationSubscription;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOrganizationSubscriptionActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isAllowedRoute($request)) {
            return $next($request);
        }

        $subscription = OrganizationSubscription::singleton();

        if ($subscription->hasAccess()) {
            return $next($request);
        }

        return redirect()->route('billing.index')->withErrors([
            'subscription' => 'Payment pending. Your organization subscription is inactive. Please subscribe to continue using the software.',
        ]);
    }

    private function isAllowedRoute(Request $request): bool
    {
        return $request->routeIs('billing.*')
            || $request->routeIs('logout')
            || $request->routeIs('verification.*')
            || $request->routeIs('login.verification')
            || $request->routeIs('verify.account')
            || $request->routeIs('resend.code');
    }
}
