@php
    $payload = $cookiePayload ?? [];
    $locale  = app()->getLocale();
    $isRtl   = $locale === 'ar';
    $pos     = $payload['position'] ?? 'bottom';
@endphp

{{-- Payload JSON for JS (must come before the banner markup) --}}
<script type="application/json" id="gt-cookie-payload">
{!! json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}
</script>

{{-- ── Banner ────────────────────────────────────────────────────── --}}
<div
    id="gtCookieBanner"
    class="gt-cookie-banner gt-cookie-banner--{{ $pos }}"
    role="dialog"
    aria-modal="false"
    aria-label="{{ e($payload['title'] ?? 'Cookie consent') }}"
    aria-live="polite"
    aria-atomic="true"
    dir="{{ $isRtl ? 'rtl' : 'ltr' }}"
>
    <div class="gt-cookie-banner__inner">
        <div class="gt-cookie-banner__content">
            @if ($payload['banner_title'] ?? false)
                <p class="gt-cookie-banner__title">{{ $payload['banner_title'] }}</p>
            @endif
            <p class="gt-cookie-banner__desc">
                {{ $payload['banner_description'] ?? '' }}
                @if ($payload['policyUrl'] ?? false)
                    <a
                        href="{{ $payload['policyUrl'] }}"
                        class="gt-cookie-banner__link"
                        target="_blank"
                        rel="noopener noreferrer"
                    >{{ __('cookie.learn_more') }}</a>
                @endif
            </p>
        </div>

        <div class="gt-cookie-banner__actions" role="group" aria-label="Cookie consent options">
            @if ($payload['showManage'] ?? false)
                <button
                    type="button"
                    id="gtCookieManageBtn"
                    class="gt-cookie-btn gt-cookie-btn--ghost"
                    aria-haspopup="dialog"
                >{{ __('cookie.manage') }}</button>
            @endif

            @if ($payload['showReject'] ?? false)
                <button
                    type="button"
                    id="gtCookieRejectBtn"
                    class="gt-cookie-btn gt-cookie-btn--outline"
                >{{ __('cookie.reject_all') }}</button>
            @endif

            <button
                type="button"
                id="gtCookieAcceptBtn"
                class="gt-cookie-btn gt-cookie-btn--primary"
            >{{ __('cookie.accept_all') }}</button>
        </div>
    </div>
</div>

{{-- ── Preferences Modal ────────────────────────────────────────── --}}
<div
    id="gtCookieModal"
    class="gt-cookie-modal"
    role="dialog"
    aria-modal="true"
    aria-labelledby="gtCookieModalTitle"
    dir="{{ $isRtl ? 'rtl' : 'ltr' }}"
>
    <div
        class="gt-cookie-modal__backdrop"
        id="gtCookieModalBackdrop"
        aria-hidden="true"
    ></div>

    <div class="gt-cookie-modal__panel" tabindex="-1" id="gtCookieModalPanel">
        <div class="gt-cookie-modal__header">
            <h2 class="gt-cookie-modal__title" id="gtCookieModalTitle">
                {{ __('cookie.preferences_title') }}
            </h2>
            <button
                type="button"
                class="gt-cookie-modal__close"
                id="gtCookieModalClose"
                aria-label="{{ __('cookie.close') }}"
            >
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2"
                     class="w-5 h-5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="gt-cookie-modal__body">
            <p class="gt-cookie-modal__intro">{{ __('cookie.preferences_intro') }}</p>
            {{-- Categories injected by JS --}}
            <div id="gtCookieCategories" aria-live="polite"></div>
        </div>

        <div class="gt-cookie-modal__footer">
            <button
                type="button"
                id="gtCookieSaveBtn"
                class="gt-cookie-btn gt-cookie-btn--outline"
            >{{ __('cookie.save_preferences') }}</button>
            <button
                type="button"
                id="gtCookieAcceptAllModalBtn"
                class="gt-cookie-btn gt-cookie-btn--primary"
            >{{ __('cookie.accept_all') }}</button>
        </div>
    </div>
</div>