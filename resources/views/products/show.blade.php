@extends('layouts.app')

@php
    $locale   = app()->getLocale();
    $fallback = config('locales.default', 'en');

    // ── Resolve translatable fields to current locale ──────────────────────

    // BUG FIX: display_name is cast 'array'. Using it directly as string prints "Array".
    $name = data_get($product->display_name, $locale)
        ?: data_get($product->display_name, $fallback)
        ?: ($product->slug ?? '');

    $prd = $product->prd_number;

    // BUG FIX: industry_label is cast 'array'. Same locale-resolution required.
    $industry = data_get($product->industry_label, $locale)
        ?: data_get($product->industry_label, $fallback)
        ?: '';

    // SEO — already correctly uses data_get with locale
    $metaTitle       = data_get($product->seo, "title.{$locale}")
        ?: data_get($product->seo, "title.{$fallback}")
        ?: $name;
    $metaDescription = data_get($product->seo, "description.{$locale}")
        ?: data_get($product->seo, "description.{$fallback}")
        ?: '';

    // ── Access control flags ───────────────────────────────────────────────
    $hasAccess   = auth()->guard('product')->check()
        && auth()->guard('product')->user()?->has_product_access;

    $showOverview = $hasAccess || (bool) ($product->pdp_public_overview   ?? true);
    $showProps    = $hasAccess || (bool) ($product->pdp_public_properties  ?? false);
    $showDocs     = $hasAccess || (bool) ($product->pdp_public_documents   ?? false);
    $showSustain  = $hasAccess || (bool) ($product->pdp_public_sustainability ?? true);

    $docMode          = (string) ($product->pdp_documents_logged_out_mode ?? 'list_disabled');
    $publicDocsEnabled = (bool) ($product->pdp_public_documents ?? false);
    $disableDocLinks  = (! $hasAccess && ! $publicDocsEnabled && $docMode === 'list_disabled');

    // ── Builder block arrays ───────────────────────────────────────────────
    $overviewBlocks = is_array($product->pdp_overview_blocks    ?? null) ? $product->pdp_overview_blocks    : [];
    $propsBlocks    = is_array($product->pdp_properties_blocks  ?? null) ? $product->pdp_properties_blocks  : [];
    $docsBlocks     = is_array($product->pdp_documents_blocks   ?? null) ? $product->pdp_documents_blocks   : [];
    $sustainBlocks  = is_array($product->pdp_sustainability_blocks ?? null) ? $product->pdp_sustainability_blocks : [];

    $loginUrl    = "/{$locale}/login";
    $registerUrl = "/{$locale}/register";
@endphp

@section('meta_title',       $metaTitle)
@section('meta_description', $metaDescription)
@section('og_type',          'product')
@section('og_title',         $metaTitle)
@section('og_description',   $metaDescription)

@section('content')
    <section class="gt-pdp">
        <div class="gt-pdp__inner">

            <a href="/{{ $locale }}/products" class="gt-pdp__back">‹ {{ __('pdp.back_default') }}</a>

            <div class="gt-pdp__top">
                <div>
                    <h1 class="gt-pdp__h1">{{ $name }}</h1>
                    <div class="gt-pdp__metaLine">
                        @if ($prd)
                            <span class="gt-pdp__meta">
                                <span class="gt-pdp__metaKey">{{ __('pdp.prd_number') }}:</span> {{ $prd }}
                            </span>
                        @endif
                        @if ($industry)
                            <span class="gt-pdp__meta">
                                <span class="gt-pdp__metaKey">{{ __('pdp.industry') }}</span> {{ $industry }}
                            </span>
                        @endif
                    </div>
                </div>

                <div class="gt-pdp__share">
                    <button type="button" class="gt-pdp__shareBtn"
                        onclick="navigator.share
                            ? navigator.share({title: document.title, url: location.href})
                            : navigator.clipboard.writeText(location.href)">
                        {{ __('pdp.share') }}
                    </button>
                </div>
            </div>

            @if (! $hasAccess)
                <div class="gt-pdp__gate">
                    <div class="gt-pdp__gateIcon" aria-hidden="true">🔒</div>
                    <div class="gt-pdp__gateText">
                        {{ __('pdp.gate_notice') }}
                        <a class="gt-pdp__gateLink" href="{{ $loginUrl }}">{{ __('pdp.login') }}</a>
                        <a class="gt-pdp__gateLink" href="{{ $registerUrl }}">{{ __('pdp.register') }}</a>
                    </div>
                </div>
            @endif

            <nav class="gt-pdp__tabs" aria-label="{{ __('Product tabs') }}">
                <a class="gt-pdp__tab is-active" href="#overview">{{ __('pdp.tab_overview') }}</a>
                <a class="gt-pdp__tab {{ $hasAccess ? '' : 'is-locked' }}" href="#properties">{{ __('pdp.tab_properties') }}</a>
                <a class="gt-pdp__tab {{ $hasAccess ? '' : 'is-locked' }}" href="#documents">{{ __('pdp.tab_documents') }}</a>
                <a class="gt-pdp__tab {{ $hasAccess ? '' : 'is-locked' }}" href="#sustainability">{{ __('pdp.tab_sustainability') }}</a>
            </nav>

            {{-- ── Overview ── --}}
            <div class="gt-pdp__section" id="overview">
                <h2 class="gt-pdp__h2">{{ __('pdp.tab_overview') }}</h2>
                @if (! $showOverview)
                    <div class="gt-pdp__notice">{{ __('pdp.locked_overview') }}</div>
                @else
                    @foreach ($overviewBlocks as $block)
                        @include('shared.blocks.render', ['block' => $block, 'hasProductAccess' => $hasAccess])
                    @endforeach
                @endif
            </div>

            {{-- ── Properties ── --}}
            <div class="gt-pdp__section" id="properties">
                <h2 class="gt-pdp__h2">{{ __('pdp.tab_properties') }}</h2>
                @if (! $showProps)
                    <div class="gt-pdp__notice">{{ __('pdp.locked_properties') }}</div>
                @else
                    @foreach ($propsBlocks as $block)
                        @include('shared.blocks.render', ['block' => $block, 'hasProductAccess' => $hasAccess])
                    @endforeach
                @endif
            </div>

            {{-- ── Documents ── --}}
            <div class="gt-pdp__section" id="documents">
                <h2 class="gt-pdp__h2">{{ __('pdp.tab_documents') }}</h2>

                @if (! $hasAccess)
                    @if ($publicDocsEnabled)
                        <div class="gt-pdp__notice gt-pdp__notice--warn">
                            {{ __('pdp.docs_partial_notice') }}
                        </div>

                        <div class="gt-docdd__toolbar" data-docdd-toolbar>
                            <button type="button" class="gt-docdd__toolLink"
                                    data-docdd-toggle aria-pressed="false">
                                {{ __('pdp.expand_all') }}
                            </button>
                            <select class="gt-docdd__toolSelect" data-docdd-lang>
                                <option value="">{{ __('pdp.documents_language') }}</option>
                            </select>
                            <input class="gt-docdd__toolSearch" type="search"
                                placeholder="{{ __('pdp.documents_search') }}"
                                data-docdd-search>
                        </div>

                        <div data-docdd-scope>
                            @foreach ($docsBlocks as $block)
                                @include('shared.blocks.render', [
                                    'block'             => $block,
                                    'disableDocLinks'   => $disableDocLinks,
                                    'publicDocsEnabled' => $publicDocsEnabled,
                                    'hasProductAccess'  => $hasAccess,
                                ])
                            @endforeach
                        </div>
                    @else
                        <div class="gt-pdp__notice gt-pdp__notice--warn">
                            {{ __('pdp.locked_documents') }}
                        </div>
                    @endif
                @else
                    {{-- Logged-in user sees full document blocks --}}
                    <div class="gt-docdd__toolbar" data-docdd-toolbar>
                        <button type="button" class="gt-docdd__toolLink"
                                data-docdd-toggle aria-pressed="false">
                            {{ __('pdp.expand_all') }}
                        </button>
                        <select class="gt-docdd__toolSelect" data-docdd-lang>
                            <option value="">{{ __('pdp.documents_language') }}</option>
                        </select>
                        <input class="gt-docdd__toolSearch" type="search"
                            placeholder="{{ __('pdp.documents_search') }}"
                            data-docdd-search>
                    </div>

                    <div data-docdd-scope>
                        @foreach ($docsBlocks as $block)
                            @include('shared.blocks.render', [
                                'block'             => $block,
                                'disableDocLinks'   => false,
                                'publicDocsEnabled' => true,
                                'hasProductAccess'  => $hasAccess,
                            ])
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- ── Sustainability ── --}}
            <div class="gt-pdp__section" id="sustainability">
                <h2 class="gt-pdp__h2">{{ __('pdp.tab_sustainability') }}</h2>
                @if (! $showSustain)
                    <div class="gt-pdp__notice">{{ __('pdp.locked_sustainability') }}</div>
                @else
                    @foreach ($sustainBlocks as $block)
                        @include('shared.blocks.render', ['block' => $block, 'hasProductAccess' => $hasAccess])
                    @endforeach
                @endif
            </div>

        </div>
    </section>
    @push('structured_data')
        <script type="application/ld+json">
        {!! json_encode(array_filter([
            '@context'    => 'https://schema.org',
            '@type'       => 'Product',
            '@id'         => rtrim(config('app.url'), '/') . "/{$locale}/products/{$product->slug}#product",
            'name'        => $name,
            'description' => $metaDescription,
            'sku'         => $prd ?: null,
            'brand'       => $product->brand ? [
                '@type' => 'Brand',
                'name'  => is_array($product->brand->name)
                    ? (data_get($product->brand->name, $locale) ?: data_get($product->brand->name, $fallback) ?: '')
                    : (string)($product->brand->name ?? ''),
            ] : null,
            'manufacturer' => $product->brand ? [
                '@type' => 'Organization',
                'name'  => is_array($product->brand->name)
                    ? (data_get($product->brand->name, $locale) ?: data_get($product->brand->name, $fallback) ?: '')
                    : (string)($product->brand->name ?? ''),
            ] : null,
            'url'         => rtrim(config('app.url'), '/') . "/{$locale}/products/{$product->slug}",
        ]), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
        </script>
    @endpush
@endsection