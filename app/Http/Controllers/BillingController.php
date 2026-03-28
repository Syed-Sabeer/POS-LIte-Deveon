<?php

namespace App\Http\Controllers;

use App\Models\OrganizationSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class BillingController extends Controller
{
    public function index()
    {
        $subscription = OrganizationSubscription::singleton();

        Log::info('Billing page opened', [
            'user_id' => auth()->id(),
            'subscription_id' => $subscription->id,
            'status' => $subscription->status,
            'has_access' => $subscription->hasAccess(),
            'on_trial' => $subscription->onTrial(),
            'trial_ends_at' => optional($subscription->trial_ends_at)?->toDateTimeString(),
            'current_period_end' => optional($subscription->current_period_end)?->toDateTimeString(),
        ]);

        return view('billing.index', [
            'subscription' => $subscription,
            'stripeKey' => config('services.stripe.key'),
            'monthlyAmountUsd' => number_format($subscription->amount_cents / 100, 2),
        ]);
    }

    public function subscribe(Request $request): JsonResponse
    {
        $request->validate([
            'payment_method_id' => ['required', 'string'],
            'organization_name' => ['nullable', 'string', 'max:255'],
            'organization_email' => ['nullable', 'email', 'max:255'],
        ]);

        $subscription = OrganizationSubscription::singleton();
        $stripe = new StripeClient((string) config('services.stripe.secret'));

        Log::info('Billing subscribe started', [
            'user_id' => auth()->id(),
            'subscription_id' => $subscription->id,
            'existing_stripe_customer_id' => $subscription->stripe_customer_id,
            'existing_stripe_subscription_id' => $subscription->stripe_subscription_id,
            'status' => $subscription->status,
        ]);

        try {
            if (! empty($subscription->stripe_subscription_id) && in_array((string) $subscription->status, ['incomplete', 'incomplete_expired', 'past_due'], true)) {
                try {
                    $stripe->subscriptions->cancel($subscription->stripe_subscription_id, []);
                    Log::info('Cancelled previous incomplete Stripe subscription before creating a new one', [
                        'old_stripe_subscription_id' => $subscription->stripe_subscription_id,
                        'old_status' => $subscription->status,
                    ]);
                } catch (ApiErrorException $cancelException) {
                    Log::warning('Could not cancel previous incomplete Stripe subscription', [
                        'old_stripe_subscription_id' => $subscription->stripe_subscription_id,
                        'message' => $cancelException->getMessage(),
                        'stripe_code' => $cancelException->getStripeCode(),
                    ]);
                }
            }

            if (empty($subscription->stripe_customer_id)) {
                $customer = $stripe->customers->create([
                    'name' => $request->input('organization_name') ?: $subscription->organization_name ?: config('app.name'),
                    'email' => $request->input('organization_email') ?: $subscription->organization_email,
                    'metadata' => [
                        'app' => config('app.name'),
                        'scope' => 'organization',
                    ],
                ]);
                $subscription->stripe_customer_id = $customer->id;
            }

            try {
                $stripe->paymentMethods->attach($request->input('payment_method_id'), [
                    'customer' => $subscription->stripe_customer_id,
                ]);
            } catch (ApiErrorException $exception) {
                $alreadyAttached = Str::contains(strtolower($exception->getMessage()), 'already been attached')
                    || $exception->getStripeCode() === 'resource_already_exists';

                if (! $alreadyAttached) {
                    throw $exception;
                }
            }

            $stripe->customers->update($subscription->stripe_customer_id, [
                'invoice_settings' => [
                    'default_payment_method' => $request->input('payment_method_id'),
                ],
                'name' => $request->input('organization_name') ?: $subscription->organization_name ?: config('app.name'),
                'email' => $request->input('organization_email') ?: $subscription->organization_email,
            ]);

            $params = [
                'customer' => $subscription->stripe_customer_id,
                'collection_method' => 'charge_automatically',
                'items' => [[
                    'price_data' => [
                        'currency' => strtolower((string) $subscription->currency),
                        'unit_amount' => (int) $subscription->amount_cents,
                        'recurring' => ['interval' => 'month'],
                        'product' => $this->resolveStripeProductId($stripe),
                    ],
                ]],
                'metadata' => [
                    'app' => config('app.name'),
                    'scope' => 'organization',
                ],
                'payment_settings' => [
                    'payment_method_types' => ['card'],
                    'save_default_payment_method' => 'on_subscription',
                ],
                'payment_behavior' => 'default_incomplete',
                'expand' => ['latest_invoice.payment_intent'],
            ];

            if ($subscription->onTrial()) {
                $params['trial_end'] = $subscription->trial_ends_at?->timestamp;
            }

            $stripeSubscription = $stripe->subscriptions->create($params);

            $subscription->fill([
                'organization_name' => $request->input('organization_name') ?: $subscription->organization_name,
                'organization_email' => $request->input('organization_email') ?: $subscription->organization_email,
                'stripe_subscription_id' => $stripeSubscription->id,
                'status' => (string) $stripeSubscription->status,
                'current_period_start' => isset($stripeSubscription->current_period_start)
                    ? Carbon::createFromTimestamp($stripeSubscription->current_period_start)
                    : null,
                'current_period_end' => $this->resolvePeriodEnd(
                    isset($stripeSubscription->current_period_end) ? Carbon::createFromTimestamp($stripeSubscription->current_period_end) : null,
                    isset($stripeSubscription->current_period_start) ? Carbon::createFromTimestamp($stripeSubscription->current_period_start) : null,
                    (string) $stripeSubscription->status,
                    $subscription->current_period_end
                ),
            ])->save();

            Log::info('Billing subscribe created stripe subscription', [
                'user_id' => auth()->id(),
                'subscription_id' => $subscription->id,
                'stripe_subscription_id' => $subscription->stripe_subscription_id,
                'stripe_status' => $subscription->status,
                'current_period_end' => optional($subscription->current_period_end)?->toDateTimeString(),
            ]);

            $latestInvoice = $stripeSubscription->latest_invoice;
            $intent = $latestInvoice?->payment_intent;

            if (! $intent && $latestInvoice && (string) $latestInvoice->status === 'open') {
                try {
                    $paidInvoice = $stripe->invoices->pay((string) $latestInvoice->id, [
                        'expand' => ['payment_intent'],
                    ]);
                    $latestInvoice = $paidInvoice;
                    $intent = $paidInvoice->payment_intent;

                    Log::info('Billing subscribe attempted immediate invoice pay for open invoice', [
                        'invoice_id' => $paidInvoice->id,
                        'invoice_status' => $paidInvoice->status,
                        'payment_intent_id' => $paidInvoice->payment_intent?->id,
                        'payment_intent_status' => $paidInvoice->payment_intent?->status,
                    ]);
                } catch (ApiErrorException $invoicePayException) {
                    Log::warning('Billing subscribe could not auto-pay open invoice', [
                        'invoice_id' => $latestInvoice->id,
                        'message' => $invoicePayException->getMessage(),
                        'stripe_code' => $invoicePayException->getStripeCode(),
                    ]);
                }
            }

            $requiresAction = $intent && in_array((string) $intent->status, ['requires_action', 'requires_confirmation', 'requires_payment_method'], true);

            Log::info('Billing subscribe Stripe status snapshot', [
                'stripe_subscription_id' => $stripeSubscription->id,
                'subscription_status' => (string) $stripeSubscription->status,
                'invoice_id' => $latestInvoice?->id,
                'invoice_status' => $latestInvoice?->status,
                'payment_intent_id' => $intent?->id,
                'payment_intent_status' => $intent?->status,
                'payment_intent_last_error' => $intent?->last_payment_error?->message,
            ]);

            return response()->json([
                'ok' => true,
                'requires_action' => $requiresAction,
                'client_secret' => $intent?->client_secret,
                'payment_intent_status' => $intent?->status,
                'invoice_status' => $latestInvoice?->status,
                'subscription_id' => $stripeSubscription->id,
                'status' => $stripeSubscription->status,
            ]);
        } catch (ApiErrorException $exception) {
            Log::error('Billing subscribe Stripe API error', [
                'user_id' => auth()->id(),
                'message' => $exception->getMessage(),
                'stripe_code' => $exception->getStripeCode(),
                'http_status' => $exception->getHttpStatus(),
            ]);

            throw ValidationException::withMessages([
                'payment' => $exception->getMessage(),
            ]);
        }
    }

    public function confirm(Request $request): JsonResponse
    {
        $request->validate([
            'subscription_id' => ['required', 'string'],
        ]);

        $subscription = OrganizationSubscription::singleton();
        $stripe = new StripeClient((string) config('services.stripe.secret'));

        Log::info('Billing confirm started', [
            'user_id' => auth()->id(),
            'subscription_id' => $subscription->id,
            'requested_stripe_subscription_id' => $request->string('subscription_id')->toString(),
        ]);

        try {
            $stripeSubscription = $stripe->subscriptions->retrieve($request->string('subscription_id')->toString(), [
                'expand' => ['latest_invoice.payment_intent'],
            ]);

            if ($subscription->stripe_subscription_id && $subscription->stripe_subscription_id !== $stripeSubscription->id) {
                throw ValidationException::withMessages([
                    'subscription' => 'Subscription mismatch. Please try again.',
                ]);
            }

            $subscription->fill([
                'stripe_subscription_id' => $stripeSubscription->id,
                'status' => (string) $stripeSubscription->status,
                'current_period_start' => isset($stripeSubscription->current_period_start)
                    ? Carbon::createFromTimestamp($stripeSubscription->current_period_start)
                    : null,
                'current_period_end' => $this->resolvePeriodEnd(
                    isset($stripeSubscription->current_period_end) ? Carbon::createFromTimestamp($stripeSubscription->current_period_end) : null,
                    isset($stripeSubscription->current_period_start) ? Carbon::createFromTimestamp($stripeSubscription->current_period_start) : null,
                    (string) $stripeSubscription->status,
                    $subscription->current_period_end
                ),
                'last_payment_at' => in_array((string) $stripeSubscription->status, OrganizationSubscription::ACTIVE_STATUSES, true)
                    ? now()
                    : $subscription->last_payment_at,
            ])->save();

            $invoice = $stripeSubscription->latest_invoice;
            $intent = $invoice?->payment_intent;

            if (! $intent && $invoice && (string) $invoice->status === 'open') {
                try {
                    $paidInvoice = $stripe->invoices->pay((string) $invoice->id, [
                        'expand' => ['payment_intent'],
                    ]);
                    $invoice = $paidInvoice;
                    $intent = $paidInvoice->payment_intent;

                    Log::info('Billing confirm attempted immediate invoice pay for open invoice', [
                        'invoice_id' => $paidInvoice->id,
                        'invoice_status' => $paidInvoice->status,
                        'payment_intent_id' => $paidInvoice->payment_intent?->id,
                        'payment_intent_status' => $paidInvoice->payment_intent?->status,
                    ]);
                } catch (ApiErrorException $invoicePayException) {
                    Log::warning('Billing confirm could not auto-pay open invoice', [
                        'invoice_id' => $invoice->id,
                        'message' => $invoicePayException->getMessage(),
                        'stripe_code' => $invoicePayException->getStripeCode(),
                    ]);
                }
            }

            $intentStatus = (string) ($intent?->status ?? 'unknown');
            $invoiceStatus = (string) ($invoice?->status ?? 'unknown');
            $errorMessage = $intent?->last_payment_error?->message;

            Log::info('Billing confirm Stripe status snapshot', [
                'stripe_subscription_id' => $stripeSubscription->id,
                'subscription_status' => (string) $stripeSubscription->status,
                'invoice_id' => $invoice?->id,
                'invoice_status' => $invoiceStatus,
                'payment_intent_id' => $intent?->id,
                'payment_intent_status' => $intentStatus,
                'payment_error' => $errorMessage,
            ]);

            $message = null;
            if (! $subscription->hasAccess()) {
                if ($errorMessage) {
                    $message = 'Payment failed: ' . $errorMessage;
                } elseif ($intentStatus === 'requires_action') {
                    $message = 'Additional card authentication is required. Please complete 3D Secure and retry.';
                } elseif ($intentStatus === 'requires_payment_method') {
                    $message = 'Card was declined or payment method is invalid. Please try another card.';
                } else {
                    $message = 'Subscription created but still pending. Stripe status: ' . $stripeSubscription->status . ', invoice: ' . $invoiceStatus . ', payment: ' . $intentStatus . '.';
                }
            }

            Log::info('Billing confirm completed', [
                'user_id' => auth()->id(),
                'subscription_id' => $subscription->id,
                'stripe_subscription_id' => $subscription->stripe_subscription_id,
                'status' => $subscription->status,
                'has_access' => $subscription->hasAccess(),
                'current_period_end' => optional($subscription->current_period_end)?->toDateTimeString(),
            ]);

            return response()->json([
                'ok' => true,
                'active' => $subscription->hasAccess(),
                'status' => $subscription->status,
                'invoice_status' => $invoiceStatus,
                'payment_intent_status' => $intentStatus,
                'message' => $message,
            ]);
        } catch (ApiErrorException $exception) {
            Log::error('Billing confirm Stripe API error', [
                'user_id' => auth()->id(),
                'message' => $exception->getMessage(),
                'stripe_code' => $exception->getStripeCode(),
                'http_status' => $exception->getHttpStatus(),
            ]);

            throw ValidationException::withMessages([
                'subscription' => $exception->getMessage(),
            ]);
        }
    }

    public function webhook(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $signature = (string) $request->header('Stripe-Signature');
        $secret = (string) config('services.stripe.webhook_secret');

        Log::info('Billing webhook received', [
            'has_signature' => ! empty($signature),
            'payload_bytes' => strlen($payload),
        ]);

        try {
            if (! empty($secret)) {
                $event = \Stripe\Webhook::constructEvent($payload, $signature, $secret);
            } else {
                $event = json_decode($payload);
            }
        } catch (\Throwable $e) {
            Log::warning('Billing webhook invalid payload/signature', [
                'message' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Invalid webhook'], 400);
        }

        if (! $event || empty($event->type) || empty($event->data->object)) {
            return response()->json(['received' => true]);
        }

        $object = $event->data->object;

        if (isset($object->id) && str_starts_with((string) $object->id, 'sub_')) {
            $subscription = OrganizationSubscription::singleton();
            if (! $subscription->stripe_subscription_id || $subscription->stripe_subscription_id === $object->id) {
                $subscription->fill([
                    'stripe_subscription_id' => (string) $object->id,
                    'status' => (string) ($object->status ?? $subscription->status),
                    'current_period_start' => isset($object->current_period_start) ? Carbon::createFromTimestamp($object->current_period_start) : $subscription->current_period_start,
                    'current_period_end' => $this->resolvePeriodEnd(
                        isset($object->current_period_end) ? Carbon::createFromTimestamp($object->current_period_end) : null,
                        isset($object->current_period_start) ? Carbon::createFromTimestamp($object->current_period_start) : $subscription->current_period_start,
                        (string) ($object->status ?? $subscription->status),
                        $subscription->current_period_end
                    ),
                    'last_payment_at' => in_array((string) ($object->status ?? ''), OrganizationSubscription::ACTIVE_STATUSES, true) ? now() : $subscription->last_payment_at,
                ])->save();

                Log::info('Billing webhook subscription synced', [
                    'subscription_id' => $subscription->id,
                    'stripe_subscription_id' => $subscription->stripe_subscription_id,
                    'status' => $subscription->status,
                    'current_period_end' => optional($subscription->current_period_end)?->toDateTimeString(),
                ]);
            }
        }

        return response()->json(['received' => true]);
    }

    private function resolveStripeProductId(StripeClient $stripe): string
    {
        $targetName = config('app.name') . ' Organization Subscription';

        $products = $stripe->products->all(['limit' => 100]);
        foreach ($products->data as $product) {
            if (($product->name ?? null) === $targetName) {
                return $product->id;
            }
        }

        $created = $stripe->products->create([
            'name' => $targetName,
            'metadata' => [
                'app' => config('app.name'),
                'scope' => 'organization',
            ],
        ]);

        return $created->id;
    }

    private function resolvePeriodEnd(?Carbon $stripePeriodEnd, ?Carbon $periodStart, string $status, ?Carbon $existingPeriodEnd): ?Carbon
    {
        if ($stripePeriodEnd instanceof Carbon) {
            return $stripePeriodEnd;
        }

        if ($existingPeriodEnd instanceof Carbon) {
            return $existingPeriodEnd;
        }

        if ($status === 'active') {
            $base = $periodStart instanceof Carbon ? $periodStart : now();
            return $base->copy()->addMonth();
        }

        return null;
    }
}
