@extends('layouts.app')

@php
  $locale = app()->getLocale();
  $data = $block['data'] ?? [];

  $name = $product->display_name ?: ($product->slug ?? '');
  $prd = $product->prd_number;
  $industry = $product->industry_label;

  $metaTitle = data_get($product->seo, "title.$locale") ?: data_get($product->seo, "title.en") ?: $name;
  $metaDescription = data_get($product->seo, "description.$locale") ?: data_get($product->seo, "description.en") ?: '';

  $hasAccess = auth()->guard('product')->check() && auth()->guard('product')->user()?->has_product_access;

  $showOverview = $hasAccess || (bool)($product->pdp_public_overview ?? true);
  $showProps = $hasAccess || (bool)($product->pdp_public_properties ?? false);
  $showDocs = $hasAccess || (bool)($product->pdp_public_documents ?? false);
  $showSustain = $hasAccess || (bool)($product->pdp_public_sustainability ?? true);

  $docMode = (string)($product->pdp_documents_logged_out_mode ?? 'list_disabled');
  $publicDocsEnabled = (bool)($product->pdp_public_documents ?? false);
  $disableDocLinks = (!$hasAccess && !$publicDocsEnabled && $docMode === 'list_disabled');

  $overviewBlocks = is_array($product->pdp_overview_blocks ?? null) ? $product->pdp_overview_blocks : [];
  $propsBlocks = is_array($product->pdp_properties_blocks ?? null) ? $product->pdp_properties_blocks : [];
  $docsBlocks = is_array($product->pdp_documents_blocks ?? null) ? $product->pdp_documents_blocks : [];
  $sustainBlocks = is_array($product->pdp_sustainability_blocks ?? null) ? $product->pdp_sustainability_blocks : [];

  $overviewHtml = (string)($product->pdp_overview_html ?? '');
  $propertiesHtml = (string)($product->pdp_properties_html ?? '');
  $sustainHtml = (string)($product->pdp_sustainability_html ?? '');
  $documents = is_array($product->pdp_documents ?? null) ? $product->pdp_documents : [];

  $rows = is_array($data['rows'] ?? null) ? $data['rows'] : [];

  $uid = 'docdd_' . substr(md5(json_encode($data)), 0, 10);
  $languages = collect($rows)->map(fn($r) => (string)($r['language'] ?? ''))->filter()->unique()->sort()->values();

  $loginUrl = "/{$locale}/login";
  $registerUrl = "/{$locale}/register";
@endphp

@section('meta_title', $metaTitle)
@section('meta_description', $metaDescription)
@section('og_type', 'product')
@section('og_title', $metaTitle)
@section('og_description', $metaDescription)

@section('content')
<section class="gt-pdp">
  <div class="gt-pdp__inner">
    <a href="/{{ $locale }}/products" class="gt-pdp__back">‹ Go Back</a>

    <div class="gt-pdp__top">
      <div>
        <h1 class="gt-pdp__h1">{{ $name }}</h1>
        <div class="gt-pdp__metaLine">
          @if($prd)<span class="gt-pdp__meta"><span class="gt-pdp__metaKey">PRD Number:</span> {{ $prd }}</span>@endif
          @if($industry)<span class="gt-pdp__meta"><span class="gt-pdp__metaKey">Industry</span> {{ $industry }}</span>@endif
        </div>
      </div>

      <div class="gt-pdp__share">
        <button type="button" class="gt-pdp__shareBtn" onclick="navigator.share ? navigator.share({title: document.title, url: location.href}) : navigator.clipboard.writeText(location.href)">
          Share
        </button>
      </div>
    </div>

    @if(!$hasAccess)
      <div class="gt-pdp__gate">
        <div class="gt-pdp__gateIcon" aria-hidden="true">🔒</div>
        <div class="gt-pdp__gateText">
          You are not logged in. Log in to access more information such as additional product details, pricing, properties, documents.
          <a class="gt-pdp__gateLink" href="{{ $loginUrl }}">Login</a>
          <a class="gt-pdp__gateLink" href="{{ $registerUrl }}">Register</a>
        </div>
      </div>
    @endif

    <nav class="gt-pdp__tabs" aria-label="Product tabs">
      <a class="gt-pdp__tab is-active" href="#overview">Overview</a>
      <a class="gt-pdp__tab {{ $hasAccess ? '' : 'is-locked' }}" href="#properties">Properties</a>
      <a class="gt-pdp__tab {{ $hasAccess ? '' : 'is-locked' }}" href="#documents">Documents</a>
      <a class="gt-pdp__tab {{ $hasAccess ? '' : 'is-locked' }}" href="#sustainability">Sustainability</a>
    </nav>

    <div class="gt-pdp__section" id="overview">
        <h2 class="gt-pdp__h2">Overview</h2>

        @if(!$showOverview)
            <div class="gt-pdp__notice">Please login to access overview.</div>
        @else
            @foreach($overviewBlocks as $block)
                @include('shared.blocks.render', ['block' => $block, 'hasProductAccess' => $hasAccess])
            @endforeach
        @endif
    </div>

    <div class="gt-pdp__section" id="properties">
        <h2 class="gt-pdp__h2">Properties</h2>

        @if(!$showProps)
            <div class="gt-pdp__notice">Please login to access properties.</div>
        @else
            @foreach($propsBlocks as $block)
                @include('shared.blocks.render', ['block' => $block, 'hasProductAccess' => $hasAccess])
            @endforeach
        @endif
    </div>

    <div class="gt-pdp__section" id="documents">
        <h2 class="gt-pdp__h2">Documents</h2>

        @if(!$hasAccess)
            @if($publicDocsEnabled)
                <div class="gt-pdp__notice gt-pdp__notice--warn">
                    Some documents may require login to download.
                </div>
                <div class="gt-docdd__toolbar" data-docdd="{{ $uid }}">
                    <button type="button" class="gt-docdd__toolLink" data-docdd-expand>Expand All</button>

                    <select class="gt-docdd__toolSelect" data-docdd-lang>
                        <option value="">Language</option>
                        @foreach($languages as $lang)
                            <option value="{{ $lang }}">{{ $lang }}</option>
                        @endforeach
                    </select>

                    <input class="gt-docdd__toolSearch" type="search" placeholder="Search" data-docdd-search>
                </div>
                @foreach($docsBlocks as $block)
                    @include('shared.blocks.render', [
                    'block' => $block,
                    'disableDocLinks' => $disableDocLinks,
                    'publicDocsEnabled' => $publicDocsEnabled,
                    'hasProductAccess' => $hasAccess,
                    ])
                @endforeach                                                             
            @else
            <div class="gt-pdp__notice gt-pdp__notice--warn">
                Please login to download documents
            </div>
            @endif
        @endif
    </div>

    <div class="gt-pdp__section" id="sustainability">
        <h2 class="gt-pdp__h2">Sustainability</h2>

        @if(!$showSustain)
            <div class="gt-pdp__notice">Please login to access sustainability information.</div>
        @else
            @foreach($sustainBlocks as $block)
                @include('shared.blocks.render', ['block' => $block, 'hasProductAccess' => $hasAccess])
            @endforeach
        @endif
    </div>

  </div>
</section>
@endsection