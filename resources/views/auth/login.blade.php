@extends('layouts.auth.master')

@section('title', __('Login'))
@section('description', '')
@section('keywords', '')
@section('author', '')

@section('css')
<style>
  body {
    min-height: 100vh;
    background:
      radial-gradient(circle at 12% 10%, rgba(247, 148, 30, 0.18), transparent 38%),
      radial-gradient(circle at 90% 85%, rgba(255, 120, 40, 0.14), transparent 34%),
      linear-gradient(135deg, #fffaf3 0%, #fff5e8 40%, #fff8f1 100%);
  }

  .auth-wrap {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px;
  }

  .auth-card {
    width: 100%;
    max-width: 920px;
    border: 0;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(16, 24, 40, 0.12);
  }

  .auth-side {
    background: linear-gradient(150deg, #cc6f00 0%, #f7941e 55%, #ffb458 100%);
    color: #fff;
    padding: 40px;
    position: relative;
  }

  .auth-side h3 {
    color: #fff;
    margin-bottom: 10px;
  }

  .auth-side p {
    color: rgba(255, 255, 255, 0.9);
    margin-bottom: 0;
  }

  .auth-panel {
    padding: 36px;
    background: #fff;
  }

  .auth-title {
    margin-bottom: 4px;
    font-weight: 700;
    color: #111827;
  }

  .auth-subtitle {
    color: #6b7280;
    margin-bottom: 24px;
  }

  .form-label {
    font-weight: 600;
    color: #374151;
    margin-bottom: 6px;
  }

  .form-control {
    border-radius: 10px;
    border: 1px solid #d7dbe2;
    min-height: 44px;
  }

  .form-control:focus {
    border-color: #f7941e;
    box-shadow: 0 0 0 0.2rem rgba(247, 148, 30, 0.2);
  }

  .auth-submit {
    min-height: 46px;
    border-radius: 10px;
    font-weight: 600;
  }

  .auth-submit[disabled] {
    opacity: 0.9;
    cursor: not-allowed;
  }

  .btn-loader {
    display: none;
    width: 1rem;
    height: 1rem;
    border: 2px solid rgba(255, 255, 255, 0.5);
    border-top-color: #fff;
    border-radius: 50%;
    animation: spin 0.7s linear infinite;
  }

  .auth-submit.loading .btn-loader {
    display: inline-block;
  }

  .auth-submit.loading .btn-text {
    opacity: 0.95;
  }

  @keyframes spin {
    to { transform: rotate(360deg); }
  }

  .helper-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
  }

  .invalid-feedback {
    display: block;
    font-size: 0.84rem;
  }

  @media (max-width: 991.98px) {
    .auth-side {
      display: none;
    }

    .auth-panel {
      padding: 28px;
    }
  }
</style>
@endsection

@section('content')
<div class="auth-wrap">
  <div class="card auth-card">
    <div class="row g-0">
      <div class="col-lg-5 auth-side">
        <h3>Welcome Back</h3>
        <p>Sign in to access your POS, inventory, receivables, payables, and accounting dashboard.</p>
      </div>

      <div class="col-lg-7 auth-panel">
        <h4 class="auth-title">Sign in to your account</h4>
        <p class="auth-subtitle">Enter your email or username and password.</p>

        @if(session('success'))
          <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
          <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <form id="loginForm" action="{{ route('login.attempt') }}" method="POST" novalidate>
          @csrf

          <div class="mb-3">
            <label class="form-label">Email or Username</label>
            <input
              class="form-control @error('email_username') is-invalid @enderror"
              name="email_username"
              value="{{ old('email_username') }}"
              placeholder="Enter email or username"
              autofocus
              required
              type="text"
            >
            @error('email_username')
              <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
            @enderror
          </div>

          <div class="mb-3">
            <label class="form-label">Password</label>
            <div class="form-input position-relative">
              <input
                type="password"
                class="form-control @error('password') is-invalid @enderror"
                name="password"
                placeholder="Enter password"
                required
              >
              <div class="show-hide"><span class="show"></span></div>
            </div>
            @error('password')
              <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
            @enderror
          </div>

          <div class="helper-row mb-3">
            <div class="form-check mb-0">
              <input class="form-check-input" id="rememberMe" name="remember" type="checkbox" value="1">
              <label class="form-check-label text-muted" for="rememberMe">Remember me</label>
            </div>
          </div>

          <button id="loginSubmitBtn" class="btn btn-primary w-100 auth-submit d-inline-flex align-items-center justify-content-center gap-2" type="submit">
            <span class="btn-loader" aria-hidden="true"></span>
            <span class="btn-text">Sign in</span>
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

@endsection

@section('script')
<script>
  (function () {
    const form = document.getElementById('loginForm');
    const submitBtn = document.getElementById('loginSubmitBtn');
    if (!form || !submitBtn) {
      return;
    }

    form.addEventListener('submit', function () {
      submitBtn.classList.add('loading');
      submitBtn.setAttribute('disabled', 'disabled');
      const textEl = submitBtn.querySelector('.btn-text');
      if (textEl) {
        textEl.textContent = 'Signing in...';
      }
    });
  })();
</script>
@endsection
