@extends('layouts.app')

@section('meta_title', 'Register - Step 2')

@section('content')
@php $locale = app()->getLocale(); @endphp

<section class="gt-reg">
  <div class="gt-reg__inner">
    <div class="gt-reg__cols">
      <div class="gt-reg__main">
        <h1 class="gt-reg__h1">{{ __('auth.reg_title') }}</h1>

        <div class="gt-reg__steps">
          <div class="gt-reg__step">1</div>
          <div class="gt-reg__step is-active">2</div>
          <div class="gt-reg__req">* Required fields</div>
        </div>

        <form method="POST" action="/{{ $locale }}/register/step2" class="gt-reg__form">
          @csrf

          <label class="gt-reg__label">{{ __('auth.reg_company') }}</label>
          <input class="gt-reg__input" name="company" value="{{ old('company') }}" required>

          <label class="gt-reg__label">{{ __('auth.reg_existing_customer') }}</label>
          <div class="gt-reg__radioRow">
            <label><input type="radio" name="existing_customer" value="yes" required @checked(old('existing_customer')==='yes')> {{ __('auth.reg_yes') }}</label>
            <label><input type="radio" name="existing_customer" value="no" required @checked(old('existing_customer')==='no')> {{ __('auth.reg_no') }}</label>
          </div>

          <label class="gt-reg__label">{{ __('auth.reg_location') }}</label>
          <input class="gt-reg__input" name="location" value="{{ old('location') }}" placeholder="Enter Location" required>

          <label class="gt-reg__label">{{ __('auth.reg_city') }}</label>
          <input class="gt-reg__input" name="city" value="{{ old('city') }}" required>

          <label class="gt-reg__label">{{ __('auth.reg_street') }}</label>
          <input class="gt-reg__input" name="street_and_number" value="{{ old('street_and_number') }}" required>

          <label class="gt-reg__label">{{ __('auth.reg_zip') }}</label>
          <input class="gt-reg__input" name="zip_code" value="{{ old('zip_code') }}" required>

          <label class="gt-reg__label">{{ __('auth.reg_industries') }}</label>
          <input class="gt-reg__input" name="industries_operate" value="{{ old('industries_operate') }}" placeholder="In which industries does your company operate?">

          <label class="gt-reg__label">{{ __('auth.reg_message') }}</label>
          <textarea class="gt-reg__textarea" name="message" rows="4" placeholder="Additional Comments">{{ old('message') }}</textarea>

          <div class="gt-reg__captcha">
            @if(!$recaptchaSiteKey)
              <div class="gt-reg__errors">
                reCAPTCHA site key is not configured. Set RECAPTCHA_SITE_KEY in .env and run php artisan optimize:clear
              </div>
            @else
              <div id="recaptchaMount" class="g-recaptcha" data-sitekey="{{ $recaptchaSiteKey }}"></div>
            @endif

            @error('g-recaptcha-response')
              <div class="gt-reg__errors">{{ $message }}</div>
            @enderror
          </div>

          @if($errors->any())
            <div class="gt-reg__errors">
              @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
            </div>
          @endif

          @if($recaptchaSiteKey)
            <script src="https://www.google.com/recaptcha/api.js" async defer></script>
          @endif

          <button class="gt-reg__btn" type="submit">{{ __('auth.reg_confirm') }}</button>
          <script>
            (function(){
                const form = document.querySelector('form[action$="/register/step2"]');
                if(!form) return;

                form.addEventListener('submit', function(e){
                const el = document.querySelector('textarea[name="g-recaptcha-response"]');
                if(!el || !el.value){
                    // If the widget didn't load, this prevents the useless submit
                    e.preventDefault();
                    alert('{{ __('auth.reg_not_robot') }}');
                }
                });
            })();
          </script>
        </form>
      </div>

      <aside class="gt-reg__side">
        <h3 class="gt-reg__sideTitle">{{ __('auth.useful_info') }}</h3>
        <a class="gt-reg__sideLink" href="/{{ $locale }}/pages/terms-of-use">{{ __('auth.terms_link') }}</a>
        <a class="gt-reg__sideLink" href="/{{ $locale }}/pages/privacy-policy">{{ __('auth.privacy_link') }}</a>
      </aside>
    </div>
  </div>
</section>

@push('scripts')
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
@endpush
@endsection