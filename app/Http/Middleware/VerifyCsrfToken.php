<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Illuminate\Support\Facades\Log;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'stripe/webhook',
    ];

    protected function tokensMatch($request)
    {
        $matches = parent::tokensMatch($request);

        if (! $matches && $request->is('billing/*')) {
            Log::warning('Billing CSRF token mismatch', [
                'path' => $request->path(),
                'method' => $request->method(),
                'has_session' => $request->hasSession(),
                'session_id' => $request->hasSession() ? $request->session()->getId() : null,
                'has_csrf_header' => ! empty($request->header('X-CSRF-TOKEN')),
                'has_xsrf_header' => ! empty($request->header('X-XSRF-TOKEN')),
                'has_csrf_input' => $request->has('_token'),
                'user_id' => optional($request->user())->id,
                'ip' => $request->ip(),
            ]);
        }

        return $matches;
    }
}
