{{-- resources/views/home.blade.php --}}

@extends('layouts.app')
@php
    $hasHero = true;
    $locale = app()->getLocale();
@endphp

@section('meta_title', 'Globaltrding - Home')
@section('meta_description', 'Industrial equipment & raw materials supplier supporting Oil & Gas, Petrochemical, Refinery, and Chemical industries with trusted sourcing and multilingual experience.')

@section('og_type', 'website')
@section('og_title', 'Globaltrding - Home')
@section('og_description', 'Industrial equipment & raw materials supplier supporting Oil & Gas, Petrochemical, Refinery, and Chemical industries with trusted sourcing and multilingual experience.')

@section('og_image', 'https://globaltrding.com/og-home.jpg')

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