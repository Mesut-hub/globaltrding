@extends('layouts.app')

@section('meta_title', 'Set your password')

@section('content')
@php $locale = app()->getLocale(); @endphp

<section class="gt-login">
  <div class="gt-login__inner">
    <div class="gt-login__panel">
      <h1 class="gt-login__h1">Set your password</h1>

      @if(session('success'))
        <div class="gt-login__errors" style="border-color:#bbf7d0;background:#ecfdf5;color:#065f46;">
          {{ session('success') }}
        </div>
      @endif

      <form method="POST" action="/{{ $locale }}/reset-password" class="gt-login__form">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <label class="gt-login__label">Email</label>
        <input class="gt-login__input" name="email" type="email" value="{{ old('email', $email) }}" required>

        <label class="gt-login__label">New password</label>
        <input class="gt-login__input" name="password" type="password" required>

        <label class="gt-login__label">Confirm password</label>
        <input class="gt-login__input" name="password_confirmation" type="password" required>

        @if($errors->any())
          <div class="gt-login__errors">
            @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
          </div>
        @endif

        <button class="gt-login__btn" type="submit">Update password</button>
      </form>
    </div>
  </div>
</section>
@endsection