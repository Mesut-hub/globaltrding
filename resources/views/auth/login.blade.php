{{-- resources/views/auth/login.blade.php --}}
@extends('layouts.app')

@section('meta_title', __('auth.login_title'))

@section('content')
@php $locale = app()->getLocale(); @endphp

<section class="gt-login">
  <div class="gt-login__inner">
    <div class="gt-login__panel">
      <h1 class="gt-login__h1">{{ __('auth.login_title') }}</h1>

      {{-- ── Status alert banners (set by CheckCustomerStatus middleware) ── --}}
      @if(session('product_auth_error') === 'blocked')
        <div class="gt-login__alert gt-login__alert--danger">
          <strong>{{ __('auth.account_blocked_title') }}</strong>
          @if(session('blocked_reason'))
            <p class="mt-1 text-sm">{{ __('auth.reason') }}: {{ session('blocked_reason') }}</p>
          @endif
          <p class="mt-1 text-sm">{{ __('auth.contact_support') }}</p>
        </div>
      @elseif(session('product_auth_error') === 'suspended')
        <div class="gt-login__alert gt-login__alert--warning">
          <strong>{{ __('auth.account_suspended_title') }}</strong>
          @if(session('suspended_until'))
            <p class="mt-1 text-sm">
              {{ __('auth.suspended_until', ['date' => \Carbon\Carbon::parse(session('suspended_until'))->format('d M Y H:i')]) }}
            </p>
          @endif
          @if(session('suspended_reason'))
            <p class="mt-1 text-sm">{{ __('auth.reason') }}: {{ session('suspended_reason') }}</p>
          @endif
        </div>
      @elseif(session('product_auth_error') === 'access_revoked')
        <div class="gt-login__alert gt-login__alert--warning">
          <strong>{{ __('auth.access_revoked_title') }}</strong>
          <p class="mt-1 text-sm">{{ __('auth.contact_support') }}</p>
        </div>
      @elseif(session('success'))
        <div class="gt-login__alert gt-login__alert--success">
          {{ session('success') }}
        </div>
      @endif

      <form method="POST" action="/{{ $locale }}/login" class="gt-login__form">
        @csrf

        <label class="gt-login__label">{{ __('auth.email') }}</label>
        <input
          class="gt-login__input"
          name="email"
          type="email"
          value="{{ old('email') }}"
          autocomplete="email"
          required>

        <label class="gt-login__label">{{ __('auth.password') }}</label>
        <input
          class="gt-login__input"
          name="password"
          type="password"
          autocomplete="current-password"
          required>

        {{-- Remember Me --}}
        <label class="gt-reg__check" style="margin-top:6px;">
          <input type="checkbox" name="remember_me" value="1"
                 {{ old('remember_me') ? 'checked' : '' }}>
          <span style="font-size:13px;color:#475569;">{{ __('auth.remember_me') }}</span>
        </label>

        @if($errors->any())
          <div class="gt-login__errors">
            @foreach($errors->all() as $e)
              <div>{{ $e }}</div>
            @endforeach
          </div>
        @endif

        <button class="gt-login__btn" type="submit">{{ __('auth.login_btn') }}</button>

        <div class="gt-login__links">
          <a href="/{{ $locale }}/register">{{ __('auth.register_link') }}</a>
        </div>
      </form>
    </div>
  </div>
</section>

<style>
.gt-login__alert {
  border-radius: 8px;
  padding: 12px 14px;
  margin-bottom: 16px;
  font-size: 14px;
  font-weight: 600;
}
.gt-login__alert--danger  { background: #fef2f2; border: 1px solid #fca5a5; color: #7f1d1d; }
.gt-login__alert--warning { background: #fffbeb; border: 1px solid #fcd34d; color: #78350f; }
.gt-login__alert--success { background: #f0fdf4; border: 1px solid #86efac; color: #14532d; }
.gt-login__alert p { font-weight: 400; }
</style>
@endsection