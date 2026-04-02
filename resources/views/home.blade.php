{{-- resources/views/home.blade.php --}}

@extends('layouts.app')
@php
    $hasHero = true;
    $locale = app()->getLocale();
@endphp

@section('meta_title', 'Globaltrding - Home')

@section('content')
    {{-- CMS-driven sections --}}
    @foreach ($homeSections as $section)
        @foreach (($section->blocks ?? []) as $block)
            @include('shared.blocks.home', ['block' => $block])
        @endforeach
    @endforeach

    {{-- Latest News stays dynamic from NewsPosts --}}
    @include('home.partials.latest-news', ['news' => $news, 'locale' => $locale])
@endsection