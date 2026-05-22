@extends('layouts.app')

@php
    $locale   = app()->getLocale();
    $fallback = config('locales.default', 'en');
@endphp

@section('meta_title',       __('Products') . ' - Globaltrding')
@section('meta_description', __('Use Product Finder to discover the right product for your needs.'))
@section('og_type',          'website')
@section('og_title',         __('Products') . ' - Globaltrding')
@section('og_description',   __('Use Product Finder to discover the right product for your needs.'))

@section('content')
@php
    $resultsCount = $products->total();

    $filterLabels = [
        'industries'       => __('Industries'),
        'applications'     => __('Application'),
        'product_groups'   => __('Products Group'),
        'processes'        => __('Processes'),
        'sustainability_tags' => __('Sustainability'),
        'regulatory_tags'  => __('Regulatory'),
    ];
@endphp

<section class="gt-pf">
    <div class="gt-pf__inner">

        <div class="gt-pf__header">
            <h1 class="gt-pf__title">{{ __('Product Finder') }}</h1>

            <form method="GET" action="/{{ $locale }}/products" class="gt-pf__topSearch">
                <input class="gt-pf__topInput"
                       name="q"
                       value="{{ $q }}"
                       placeholder="{{ __('Enter keyword, Product ...') }}"
                       aria-label="{{ __('Enter keyword, Product') }}" />

                {{-- Preserve active filters when submitting the search form --}}
                <input type="hidden" name="brand" value="{{ $brandSlug }}">
                <input type="hidden" name="sort"  value="{{ $sort }}">
                @foreach ($filters as $k => $vals)
                    @foreach ($vals as $v)
                        <input type="hidden" name="{{ $k }}[]" value="{{ $v }}">
                    @endforeach
                @endforeach

                <button class="gt-pf__topBtn" type="submit" aria-label="{{ __('Search') }}"></button>
            </form>
        </div>

        <div class="gt-pf__grid">

            {{-- ── Left: filters ── --}}
            <aside class="gt-pf__filters" aria-label="{{ __('Filters') }}">
                <form method="GET" action="/{{ $locale }}/products" class="gt-pf__filtersForm">
                    <input type="hidden" name="q"    value="{{ $q }}">
                    <input type="hidden" name="sort" value="{{ $sort }}">

                    <div class="gt-pf__filterSearchWrap">
                        <input class="gt-pf__filterSearch" type="text"
                               placeholder="{{ __('Search within filters ...') }}"
                               data-filter-search>
                    </div>

                    {{-- Brand --}}
                    <div class="gt-pf__filterBlock">
                        <div class="gt-pf__filterTitle">{{ __('Brand') }}</div>
                        <select name="brand" class="gt-pf__select">
                            <option value="">{{ __('All brands') }}</option>
                            @foreach ($brands as $b)
                                <option value="{{ $b->slug }}" @selected($brandSlug === $b->slug)>
                                    {{ data_get($b->name, $locale)
                                        ?: data_get($b->name, $fallback)
                                        ?: $b->slug }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Facet filters --}}
                    @foreach ($filterLabels as $key => $label)
                        @php $selected = $filters[$key] ?? []; @endphp
                        <details class="gt-pf__facet" open>
                            <summary class="gt-pf__facetSummary">
                                <span>{{ $label }}</span>
                                <span class="gt-pf__chev" aria-hidden="true"></span>
                            </summary>
                            <div class="gt-pf__facetBody" data-facet-body>
                                @foreach (($facets[$key] ?? []) as $val)
                                    <label class="gt-pf__checkRow" data-filter-item>
                                        <input type="checkbox"
                                               name="{{ $key }}[]"
                                               value="{{ $val }}"
                                               @checked(in_array($val, $selected, true))>
                                        <span class="gt-pf__checkText">{{ $val }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </details>
                    @endforeach

                    <div class="gt-pf__filterActions">
                        <button class="gt-pf__apply" type="submit">{{ __('Apply') }}</button>
                        <a class="gt-pf__reset" href="/{{ $locale }}/products">{{ __('Reset') }}</a>
                    </div>
                </form>
            </aside>

            {{-- ── Right: results ── --}}
            <main class="gt-pf__results" aria-label="{{ __('Results') }}">

                <div class="gt-pf__resultsTop">
                    <div class="gt-pf__resultsCount">{{ __('Results') }}: {{ $resultsCount }}</div>

                    <form method="GET" action="/{{ $locale }}/products" class="gt-pf__sort">
                        <input type="hidden" name="q"     value="{{ $q }}">
                        <input type="hidden" name="brand" value="{{ $brandSlug }}">
                        @foreach ($filters as $k => $vals)
                            @foreach ($vals as $v)
                                <input type="hidden" name="{{ $k }}[]" value="{{ $v }}">
                            @endforeach
                        @endforeach

                        <label class="gt-pf__sortLabel">{{ __('Sort by') }}:</label>
                        <select class="gt-pf__sortSelect" name="sort" onchange="this.form.submit()">
                            <option value="relevance" @selected($sort === 'relevance')>{{ __('Most relevant') }}</option>
                            <option value="newest"    @selected($sort === 'newest')>{{ __('Newest') }}</option>
                            <option value="name_asc"  @selected($sort === 'name_asc')>{{ __('Name (A–Z)') }}</option>
                        </select>
                    </form>
                </div>

                <div class="gt-pf__list">
                    @forelse ($products as $product)
                        @php
                            // BUG FIX: display_name is 'array' cast — resolve locale
                            $title = data_get($product->display_name, $locale)
                                ?: data_get($product->display_name, $fallback)
                                ?: ($product->slug ?? '');

                            $url = "/{$locale}/products/{$product->slug}";
                            $prd = $product->prd_number;
                        @endphp

                        <article class="gt-pf__row">
                            <a class="gt-pf__rowTitle" href="{{ $url }}">{{ $title }}</a>
                            @if ($prd)
                                <div class="gt-pf__rowMeta">
                                    <span class="gt-pf__metaKey">{{ __('PRD Number') }}:</span> {{ $prd }}
                                </div>
                            @endif
                        </article>

                    @empty
                        <div class="gt-pf__empty">{{ __('No products found.') }}</div>
                    @endforelse
                </div>

                <div class="gt-pf__pager">
                    {{ $products->links() }}
                </div>
            </main>

        </div>
    </div>
</section>

@push('scripts')
<script>
(function () {
    const input = document.querySelector('[data-filter-search]');
    if (! input) return;
    input.addEventListener('input', () => {
        const q = input.value.trim().toLowerCase();
        document.querySelectorAll('[data-filter-item]').forEach(el => {
            const t = (el.textContent || '').toLowerCase();
            el.style.display = (! q || t.includes(q)) ? '' : 'none';
        });
    });
})();
</script>
@endpush

@endsection