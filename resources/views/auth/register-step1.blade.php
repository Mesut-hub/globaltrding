@extends('layouts.app')

@section('meta_title', __('auth.reg_title') . ' - Globaltrding')

@section('content')
@php $locale = app()->getLocale(); @endphp

<section class="gt-reg">
  <div class="gt-reg__inner">
    <div class="gt-reg__cols">
      <div class="gt-reg__main">
        <h1 class="gt-reg__h1">{{ __('auth.reg_title') }}</h1>
        <p class="gt-reg__sub">
          {!! __('auth.reg_subtitle', ['link' => '<a href="/'. app()->getLocale() .'/inquiry" class="gt-reg__link">'. __('auth.reg_contact_us') .'</a>']) !!}
        </p>

        <div class="gt-reg__steps">
          <div class="gt-reg__step is-active">1</div>
          <div class="gt-reg__step">2</div>
          <div class="gt-reg__req">* Required fields</div>
        </div>

        <form method="POST" action="/{{ $locale }}/register/step1" class="gt-reg__form">
          @csrf

          <label class="gt-reg__label">{{ __('auth.reg_first_name') }}</label>
          <input class="gt-reg__input" name="first_name" value="{{ old('first_name') }}" required>

          <label class="gt-reg__label">{{ __('auth.reg_last_name') }}</label>
          <input class="gt-reg__input" name="last_name" value="{{ old('last_name') }}" required>

          <label class="gt-reg__label">{{ __('auth.reg_email') }}</label>
          <input class="gt-reg__input" type="email" name="email" value="{{ old('email') }}" required>

          <label class="gt-reg__label">{{ __('auth.reg_occupation') }}</label>
          <input class="gt-reg__input" name="occupation" value="{{ old('occupation') }}">

          <label class="gt-reg__label">{{ __('auth.reg_mobile') }}</label>
          <input class="gt-reg__input" name="mobile_phone" value="{{ old('mobile_phone') }}" required>

          <label class="gt-reg__label">{{ __('auth.reg_interest') }}</label>
          <div class="gt-reg__searchRow">
            <input class="gt-reg__input gt-reg__input--search" name="primary_product_interest" value="{{ old('primary_product_interest') }}" required>
            <span class="gt-reg__searchIcon" aria-hidden="true"></span>
          </div>

          <label class="gt-reg__label">{{ __('auth.reg_language') }}</label>
          <select class="gt-reg__select" name="preferred_language" required>
            @foreach(['English','Turkish','German','French','Arabic'] as $lang)
              <option value="{{ $lang }}" @selected(old('preferred_language','English')===$lang)>{{ $lang }}</option>
            @endforeach
          </select>

          <label class="gt-reg__check">
            <input type="checkbox" name="accepted_terms" value="1" required @checked(old('accepted_terms'))>
            <span>{{ __('auth.reg_terms') }}</span>
          </label>

          @if($errors->any())
            <div class="gt-reg__errors">
              @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
            </div>
          @endif

          <button class="gt-reg__btn" type="submit">{{ __('auth.reg_continue') }}</button>
        </form>

        <div class="gt-reg__already">
          <div class="gt-reg__alreadyTitle">{{ __('auth.reg_already_title') }}</div>
          <a class="gt-reg__btn gt-reg__btn--ghost" href="/{{ $locale }}/login">{{ __('auth.login_title') }}</a>
        </div>
      </div>

      <aside class="gt-reg__side">
        <h3 class="gt-reg__sideTitle">{{ __('auth.useful_info') }}</h3>
        <a class="gt-reg__sideLink" href="/{{ $locale }}/pages/terms-of-use">{{ __('auth.terms_link') }}</a>
        <a class="gt-reg__sideLink" href="/{{ $locale }}/pages/privacy-policy">{{ __('auth.privacy_link') }}</a>
      </aside>
    </div>
  </div>
</section>
@endsection