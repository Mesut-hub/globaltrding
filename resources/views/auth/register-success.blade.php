@extends('layouts.app')

@section('meta_title', 'Registration in progress')

@section('content')
@php $locale = app()->getLocale(); @endphp

<section class="gt-regsuccess">
  <div class="gt-regsuccess__inner">
    <div class="gt-regsuccess__title">
      <span class="gt-regsuccess__check">✓</span>
      Registration in progress!
    </div>

    <div class="gt-regsuccess__subtitle">Thank you for registering on our platform</div>

    <div class="gt-regsuccess__box">
      <div class="gt-regsuccess__row">
        <span class="gt-regsuccess__n">1</span>
        <span>Your registration request has been submitted for review and you will receive a response soon.</span>
      </div>
      <div class="gt-regsuccess__row">
        <span class="gt-regsuccess__n">2</span>
        <span>If approved, an email notification will be sent with a link to set up a personal password to complete the registration process.</span>
      </div>
    </div>

    <p class="gt-regsuccess__foot">
      In case of further questions, please contact us using the
      <a class="gt-regsuccess__link" href="/{{ $locale }}/inquiry">contact form</a>.
    </p>
  </div>
</section>
@endsection