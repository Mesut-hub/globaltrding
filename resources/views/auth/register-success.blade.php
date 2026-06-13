@extends('layouts.app')

@section('meta_title', 'Registration in progress')

@section('content')
@php $locale = app()->getLocale(); @endphp

<section class="gt-regsuccess">
  <div class="gt-regsuccess__inner">
    <div class="gt-regsuccess__title">
      <span class="gt-regsuccess__check">✓</span>
      {{ __('auth.reg_success_title') }}
    </div>

    <div class="gt-regsuccess__subtitle">{{ __('auth.reg_success_sub') }}</div>

    <div class="gt-regsuccess__box">
      <div class="gt-regsuccess__row">
        <span class="gt-regsuccess__n">1</span>
        <span>{{ __('auth.reg_success_step1') }}</span>
      </div>
      <div class="gt-regsuccess__row">
        <span class="gt-regsuccess__n">2</span>
        <span>{{ __('auth.reg_success_step2') }}</span>
      </div>
    </div>

    <p class="gt-regsuccess__foot">
      {!! __('auth.reg_success_foot', [
          'link' => '<a class="gt-regsuccess__link" href="/'. app()->getLocale() .'/inquiry">'. __('auth.reg_contact_form') .'</a>'
      ]) !!}
    </p>
  </div>
</section>
@endsection