{{-- resources/views/components/promotion-overlay.blade.php --}}
@php
    $promotions = $promotionPayload ?? [];
    $locale     = app()->getLocale();
    $isRtl      = $locale === 'ar';
@endphp

{{--
    Promotion payload — ALWAYS emitted, even when empty ([]).
    The JS reads this to know whether to show the badge / schedule auto-show.
    type=application/json ensures search engines ignore this content (SEO-safe).
--}}
<script type="application/json" id="gt-promo-payload">
    {!! json_encode($promotions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}
</script>

{{--
    Overlay shell — ALWAYS rendered so getElementById() never returns null.
    Content is injected by JS at runtime.
    aria-hidden="true" by default; JS sets it to "false" when opened.
    No promotional text is visible to search engines in the HTML source.
--}}
<div
    id="gtPromoOverlay"
    class="gt-promo-overlay"
    role="dialog"
    aria-modal="true"
    aria-labelledby="gtPromoTitle"
    aria-hidden="true"
    dir="{{ $isRtl ? 'rtl' : 'ltr' }}"
>
    {{-- Backdrop ──────────────────────────────────────────────────── --}}
    <div
        class="gt-promo-backdrop"
        id="gtPromoBackdrop"
        aria-hidden="true"
    ></div>

    {{-- Panel ─────────────────────────────────────────────────────── --}}
    <div
        class="gt-promo-panel"
        id="gtPromoPanel"
        tabindex="-1"
    >
        {{-- Close button ────────────────────────────────────────── --}}
        <button
            type="button"
            class="gt-promo-close"
            id="gtPromoClose"
            aria-label="{{ __('ui.close') }}"
        >
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="2.5"
                 stroke-linecap="round" stroke-linejoin="round"
                 aria-hidden="true">
                <path d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        {{-- Dynamic content — populated by JS ─────────────────── --}}
        <div
            class="gt-promo-content"
            id="gtPromoContent"
            aria-live="polite"
            aria-atomic="true"
        ></div>
    </div>
</div>