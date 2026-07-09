{{-- resources/views/home.blade.php --}}

@extends('layouts.app')
@php
    $hasHero = true;
    $locale = app()->getLocale();
@endphp

@section('meta_title', 'Globaltrding - Home')
@section('meta_description', 'Istanbul-based industrial equipment & raw materials supplier since 2001. Oil & gas, boron products, solar, NDT and security systems. Serving 40+ countries.')

@section('og_type', 'website')
@section('og_title', 'Globaltrding - Home')
@section('og_description', 'Istanbul-based industrial equipment & raw materials supplier since 2001. Oil & gas, boron products, solar, NDT and security systems. Serving 40+ countries.')

@section('og_image', 'https://globaltrding.com/og-home.jpg')

@section('content')
    {{-- CMS-driven sections --}}
    @foreach ($homeSections as $section)
        @foreach (($section->blocks ?? []) as $block)
            @include('shared.blocks.home', ['block' => $block])
        @endforeach
    @endforeach
@endsection