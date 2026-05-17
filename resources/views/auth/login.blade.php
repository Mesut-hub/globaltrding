@extends('layouts.app')

@section('meta_title', 'Login')

@section('content')
@php $locale = app()->getLocale(); @endphp

<section class="gt-login">
  <div class="gt-login__inner">
    <div class="gt-login__panel">
      <h1 class="gt-login__h1">Login</h1>

      <form method="POST" action="/{{ $locale }}/login" class="gt-login__form">
        @csrf

        <label class="gt-login__label">Email</label>
        <input class="gt-login__input" name="email" type="email" value="{{ old('email') }}" required>

        <label class="gt-login__label">Password</label>
        <input class="gt-login__input" name="password" type="password" required>

        @if($errors->any())
          <div class="gt-login__errors">
            @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
          </div>
        @endif

        <button class="gt-login__btn" type="submit">Login</button>

        <div class="gt-login__links">
          <a href="/{{ $locale }}/register">Register</a>
        </div>
      </form>
    </div>
  </div>
</section>
@endsection