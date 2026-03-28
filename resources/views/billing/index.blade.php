@extends('layouts.app.master')

@section('title', 'Organization Billing')

@section('content')
<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">Organization Billing</h4>
            <h6>7-day free trial, then USD 20/month subscription</h6>
        </div>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="row g-3">
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header"><h6 class="mb-0">Current Plan</h6></div>
            <div class="card-body">
                <h3 class="mb-2">USD {{ $monthlyAmountUsd }} / month</h3>
                <p class="text-muted mb-3">This subscription is organization-wise. Individual POS customers are not charged.</p>

                <p class="mb-2"><strong>Status:</strong> <span class="badge bg-{{ $subscription->hasAccess() ? 'success' : 'warning' }}">{{ strtoupper($subscription->status) }}</span></p>
                <p class="mb-2"><strong>Trial Ends:</strong> {{ $subscription->trial_ends_at?->format('Y-m-d H:i') ?: '-' }}</p>
                <p class="mb-2"><strong>Current Period End:</strong> {{ $subscription->current_period_end?->format('Y-m-d H:i') ?: '-' }}</p>
                <p class="mb-2"><strong>Last Payment Done:</strong> {{ $subscription->last_payment_at?->format('Y-m-d H:i') ?: '-' }}</p>
                <p class="mb-0"><strong>Access:</strong> {{ $subscription->hasAccess() ? 'Active' : 'Payment Pending' }}</p>

                @if($subscription->onTrial())
                    <div class="alert alert-info mt-3 mb-0">
                        Free trial active: {{ $subscription->trialDaysRemaining() }} day(s) remaining.
                    </div>
                @elseif(!$subscription->hasAccess())
                    <div class="alert alert-warning mt-3 mb-0">
                        Payment pending. Please subscribe to continue using the software.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card">
            <div class="card-header"><h6 class="mb-0">Subscribe with Stripe</h6></div>
            <div class="card-body">
                <form id="subscriptionForm">
                    @csrf
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Organization Name</label>
                            <input type="text" name="organization_name" class="form-control" value="{{ old('organization_name', $subscription->organization_name ?? config('app.name')) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Billing Email</label>
                            <input type="email" name="organization_email" class="form-control" value="{{ old('organization_email', $subscription->organization_email) }}">
                        </div>
                    </div>

                    <label class="form-label">Card Details</label>
                    <div id="card-element" class="form-control" style="height: 44px; padding-top: 12px;"></div>
                    <div id="card-errors" class="text-danger small mt-2"></div>

                    <button id="subscribeBtn" type="submit" class="btn btn-primary mt-3">
                        <span id="subscribeBtnText">Subscribe USD {{ $monthlyAmountUsd }} / month</span>
                        <span id="subscribeBtnLoader" class="spinner-border spinner-border-sm ms-2 d-none" role="status" aria-hidden="true"></span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="https://js.stripe.com/v3/"></script>
<script>
(function () {
    const stripeKey = @json($stripeKey);
    const csrfToken = '{{ csrf_token() }}';
    if (!stripeKey) {
        document.getElementById('card-errors').textContent = 'Stripe key is missing. Set STRIPE_KEY in .env.';
        return;
    }

    const stripe = Stripe(stripeKey);
    const elements = stripe.elements();
    const card = elements.create('card', {
        hidePostalCode: true,
        style: {
            base: {
                fontSize: '14px',
                color: '#212529',
            },
        },
    });
    card.mount('#card-element');

    const form = document.getElementById('subscriptionForm');
    const button = document.getElementById('subscribeBtn');
    const buttonText = document.getElementById('subscribeBtnText');
    const buttonLoader = document.getElementById('subscribeBtnLoader');
    const errorEl = document.getElementById('card-errors');

    function setLoadingState(loading) {
        button.disabled = loading;
        buttonLoader.classList.toggle('d-none', !loading);
        buttonText.textContent = loading
            ? 'Processing payment...'
            : 'Subscribe USD {{ $monthlyAmountUsd }} / month';
    }

    async function parseJsonSafe(response) {
        const contentType = (response.headers.get('content-type') || '').toLowerCase();
        const raw = await response.text();

        if (!raw) {
            return {};
        }

        if (contentType.includes('application/json')) {
            try {
                return JSON.parse(raw);
            } catch (e) {
                return { message: 'Invalid JSON response from server.' };
            }
        }

        // HTML/text response usually means redirect, 419, or server error.
        return {
            message: 'Server returned a non-JSON response. Your session may have expired. Please refresh and try again.',
            raw,
        };
    }

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        errorEl.textContent = '';
        setLoadingState(true);

        try {
            const { paymentMethod, error } = await stripe.createPaymentMethod({
                type: 'card',
                card,
            });

            if (error) {
                errorEl.textContent = error.message || 'Payment method creation failed.';
                setLoadingState(false);
                return;
            }

            const payload = {
                payment_method_id: paymentMethod.id,
                organization_name: form.organization_name.value,
                organization_email: form.organization_email.value,
                _token: csrfToken,
            };

            const subscribeResp = await fetch('{{ route('billing.subscribe') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                },
                credentials: 'same-origin',
                body: JSON.stringify(payload),
            });

            const subscribeData = await parseJsonSafe(subscribeResp);

            if (!subscribeResp.ok || !subscribeData.ok) {
                const firstError = subscribeData?.errors
                    ? Object.values(subscribeData.errors)[0]?.[0]
                    : null;
                errorEl.textContent = firstError || subscribeData?.message || 'Subscription failed.';
                setLoadingState(false);
                return;
            }

            if (subscribeData.client_secret) {
                const confirmResult = await stripe.confirmCardPayment(subscribeData.client_secret);
                if (confirmResult.error) {
                    errorEl.textContent = confirmResult.error.message || 'Card confirmation failed.';
                    setLoadingState(false);
                    return;
                }
            }

            const confirmResp = await fetch('{{ route('billing.confirm') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                },
                credentials: 'same-origin',
                body: JSON.stringify({ subscription_id: subscribeData.subscription_id, _token: csrfToken }),
            });

            const confirmData = await parseJsonSafe(confirmResp);
            if (confirmResp.ok && confirmData.ok && confirmData.active) {
                window.location.href = '{{ route('home') }}';
                return;
            }

            errorEl.textContent = confirmData?.message || 'Subscription created, but payment is pending. Please check your card and try again.';
            setLoadingState(false);
        } catch (error) {
            errorEl.textContent = 'Something went wrong while processing payment. Please refresh and try again.';
            setLoadingState(false);
        }
    });
})();
</script>
@endsection
