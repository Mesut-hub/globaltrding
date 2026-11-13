{{-- resources/views/pages/contact.blade.php --}}
@extends('layouts.app')

@section('meta_title', __('contact.meta_title'))
@section('meta_description', __('contact.meta_description'))

@php
$hasHero = false;
$locale  = app()->getLocale();
$seoMeta = [
    'title'       => __('contact.meta_title'),
    'description' => __('contact.meta_description'),
    'canonical'   => config('app.url') . '/' . $locale . '/pages/contact',
    'ogType'      => 'website',
    'robots'      => 'index,follow',
    'schema'      => [
        '@context' => 'https://schema.org',
        '@type'    => 'ContactPage',
        '@id'      => config('app.url') . '/' . $locale . '/pages/contact',
        'name'     => __('contact.meta_title'),
        'url'      => config('app.url') . '/' . $locale . '/pages/contact',
        'inLanguage' => $locale,
    ],
];
@endphp

@section('content')
<style>
{{-- ─── All contact page styles scoped to .gt-contact ─────── --}}
.gt-contact *{box-sizing:border-box}
.gt-contact{font-family:'Segoe UI',-apple-system,system-ui,sans-serif;color:#111827}

/* Top accent */
.gt-contact .top-accent{height:3px;background:linear-gradient(90deg,#0A0E1A 0%,#C8A96E 60%,#0A0E1A 100%)}

/* Breadcrumb */
.gt-contact .bc{background:#F8F7F5;border-bottom:1px solid #E5E2DC;padding:.6rem 0}
.gt-contact .bci{max-width:1200px;margin:0 auto;padding:0 48px;display:flex;align-items:center;gap:.5rem}
.gt-contact .bcs{font-size:12px;color:#9CA3AF}.gt-contact .bcs a{color:#9CA3AF}.gt-contact .bcs a:hover{color:#C8A96E}
.gt-contact .bcsep{color:#D1CEC8;font-size:11px}.gt-contact .bcs.cur{color:#4B5563;font-weight:500}

/* Page opening */
.gt-contact .page-open{padding:52px 0 0;border-bottom:1px solid #E5E2DC;background:#fff}
.gt-contact .po-inner{max-width:1200px;margin:0 auto;padding:0 48px 48px;display:grid;grid-template-columns:1fr 400px;gap:4rem;align-items:end}
.gt-contact .po-kicker{font-size:11px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;font-family:monospace;color:#C8A96E;margin-bottom:1rem}
.gt-contact .po-h1{font-family:Georgia,'Times New Roman',serif;font-size:clamp(32px,3.5vw,48px);font-weight:400;color:#0A0E1A;line-height:1.1;margin-bottom:.9rem}
.gt-contact .po-sub{font-size:15px;color:#4B5563;line-height:1.8;max-width:480px}

/* Phone panel */
.gt-contact .phone-panel{background:#F8F7F5;border:1px solid #E5E2DC;border-radius:8px;padding:1.75rem}
.gt-contact .pp-label{font-size:10.5px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;font-family:monospace;color:#9CA3AF;margin-bottom:1rem}
.gt-contact .pp-number{font-family:Georgia,serif;font-size:24px;color:#0A0E1A;font-weight:400;display:flex;align-items:center;gap:.6rem;margin-bottom:.4rem;text-decoration:none}
.gt-contact .pp-number:hover{color:#C8A96E}
.gt-contact .pp-hours{font-size:12.5px;color:#9CA3AF;margin-bottom:1.25rem;padding-bottom:1.25rem;border-bottom:1px solid #E5E2DC}
.gt-contact .pp-rows{display:flex;flex-direction:column;gap:.5rem}
.gt-contact .pp-row{display:flex;align-items:center;justify-content:space-between}
.gt-contact .pp-rowlabel{font-size:12.5px;color:#4B5563}
.gt-contact .pp-rowval{font-size:12px;font-weight:700;color:#111827;font-family:monospace}
.gt-contact .pp-badge{font-size:10px;font-family:monospace;font-weight:700;padding:.18rem .5rem;border-radius:3px;background:rgba(5,150,105,.06);color:#065f46;border:1px solid rgba(5,150,105,.2)}

/* Category section */
.gt-contact .cat-section{max-width:1200px;margin:0 auto;padding:48px 48px 0}
.gt-contact .cat-label{font-size:11px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;font-family:monospace;color:#9CA3AF;margin-bottom:.4rem}
.gt-contact .cat-heading{font-size:19px;font-weight:600;color:#111827;letter-spacing:-.02em;margin-bottom:1.75rem}
.gt-contact .cat-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:.75rem;margin-bottom:2.5rem}
.gt-contact .cat-card{position:relative;padding:1.5rem 1.25rem 1.1rem;border:1.5px solid #E5E2DC;border-radius:6px;cursor:pointer;transition:all .18s;background:#fff;user-select:none}
.gt-contact .cat-card:hover{border-color:#C8A96E;background:rgba(200,169,110,.06)}
.gt-contact .cat-card.active{border-color:#0A0E1A;background:#0A0E1A}
.gt-contact .cat-card.active .cat-num,.gt-contact .cat-card.active .cat-desc{color:rgba(255,255,255,.4)}
.gt-contact .cat-card.active .cat-name{color:#fff}
.gt-contact .cat-card.active .cat-arrow{color:#C8A96E}
.gt-contact .cat-num{font-size:10px;font-family:monospace;font-weight:700;color:#9CA3AF;letter-spacing:.1em;margin-bottom:.65rem;display:block}
.gt-contact .cat-icon{font-size:20px;margin-bottom:.65rem;display:block}
.gt-contact .cat-name{font-size:13px;font-weight:700;color:#111827;margin-bottom:.3rem;line-height:1.3}
.gt-contact .cat-desc{font-size:11px;color:#9CA3AF;line-height:1.5}
.gt-contact .cat-arrow{position:absolute;bottom:.85rem;right:.85rem;font-size:13px;color:#D1CEC8}

/* Form panel */
.gt-contact .form-panel{max-width:1200px;margin:0 auto;padding:0 48px 72px}
.gt-contact .fc{display:none;background:#F8F7F5;border:1px solid #E5E2DC;border-radius:8px;overflow:hidden;margin-top:.5rem;animation:gtFadeSlide .22s ease}
.gt-contact .fc.active{display:block}
@keyframes gtFadeSlide{from{opacity:0;transform:translateY(6px)}to{opacity:1;transform:translateY(0)}}
.gt-contact .fh{padding:1.75rem 2.25rem;border-bottom:1px solid #E5E2DC;display:flex;align-items:flex-start;justify-content:space-between;gap:2rem;background:#fff}
.gt-contact .fh-left{}
.gt-contact .fh-title{font-size:17px;font-weight:600;color:#0A0E1A;margin-bottom:.3rem;letter-spacing:-.02em}
.gt-contact .fh-sub{font-size:13px;color:#4B5563;line-height:1.65;max-width:600px}
.gt-contact .fh-close{width:30px;height:30px;border-radius:50%;border:1px solid #E5E2DC;background:#fff;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:14px;color:#9CA3AF;flex-shrink:0;transition:all .15s}
.gt-contact .fh-close:hover{background:#F2F0EB;color:#111827}
.gt-contact .fb{padding:2rem 2.25rem}

/* Form elements */
.gt-contact .fg{display:grid;gap:1rem;margin-bottom:1rem}
.gt-contact .fg.g2{grid-template-columns:1fr 1fr}
.gt-contact .fg.g3{grid-template-columns:1fr 1fr 1fr}
.gt-contact .fgroup{display:flex;flex-direction:column;gap:.28rem}
.gt-contact .flabel{font-size:12px;font-weight:600;color:#111827;letter-spacing:.01em}
.gt-contact .flabel em{color:#DC2626;font-style:normal}
.gt-contact .finput,.gt-contact .fsel,.gt-contact .ftarea{width:100%;padding:.6rem .8rem;border:1.5px solid #D1CEC8;border-radius:5px;font-size:13.5px;color:#111827;background:#fff;transition:border-color .15s,box-shadow .15s;outline:none;-webkit-appearance:none;font-family:'Segoe UI',system-ui,sans-serif}
.gt-contact .finput:focus,.gt-contact .fsel:focus,.gt-contact .ftarea:focus{border-color:#0A0E1A;box-shadow:0 0 0 3px rgba(10,14,26,.07)}
.gt-contact .finput::placeholder,.gt-contact .ftarea::placeholder{color:#9CA3AF;font-size:13px}
.gt-contact .ftarea{resize:vertical;min-height:110px;line-height:1.65}
.gt-contact .fsel{cursor:pointer;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='none' stroke='%239CA3AF' stroke-width='1.8'%3E%3Cpath d='M5 8l5 5 5-5'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right .65rem center;background-size:16px;padding-right:2rem}
.gt-contact .fhint{font-size:11.5px;color:#9CA3AF;line-height:1.55;margin-top:.2rem}
.gt-contact .fdivider{height:1px;background:#E5E2DC;margin:1.25rem 0}
.gt-contact .fsec-label{font-size:10.5px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;font-family:monospace;color:#9CA3AF;margin-bottom:.85rem}
.gt-contact .finput.invalid,.gt-contact .fsel.invalid,.gt-contact .ftarea.invalid{border-color:#DC2626;box-shadow:0 0 0 3px rgba(220,38,38,.07)}
.gt-contact .ferr{font-size:11px;color:#DC2626;margin-top:.2rem;display:none}
.gt-contact .ferr.show{display:block}

/* Form footer */
.gt-contact .ff{padding:1.35rem 2.25rem;background:#fff;border-top:1px solid #E5E2DC;display:flex;align-items:center;justify-content:space-between;gap:2rem;flex-wrap:wrap}
.gt-contact .fsubmit{display:inline-flex;align-items:center;gap:.55rem;padding:.7rem 1.6rem;background:#0A0E1A;color:#fff;border:none;font-size:13.5px;font-weight:700;border-radius:5px;cursor:pointer;transition:all .18s;letter-spacing:.01em;font-family:inherit}
.gt-contact .fsubmit:hover{background:#1a2035;transform:translateY(-1px);box-shadow:0 4px 12px rgba(0,0,0,.12)}
.gt-contact .fsubmit:disabled{opacity:.65;cursor:not-allowed;transform:none}
.gt-contact .flegend{font-size:11.5px;color:#9CA3AF;line-height:1.6}
.gt-contact .flegend a{color:#C8A96E;text-decoration:underline}

/* Banners */
.gt-contact .success-banner{display:none;margin:0 2.25rem 1.25rem;padding:1.1rem 1.35rem;background:rgba(5,150,105,.06);border:1px solid rgba(5,150,105,.2);border-radius:5px;gap:.65rem;align-items:flex-start}
.gt-contact .success-banner.show{display:flex}
.gt-contact .sb-icon{font-size:17px;flex-shrink:0;margin-top:.1rem}
.gt-contact .sb-title{font-size:13.5px;font-weight:700;color:#065f46;margin-bottom:.15rem}
.gt-contact .sb-text{font-size:13px;color:#047857;line-height:1.65}
.gt-contact .error-banner{display:none;margin:0 2.25rem 1.25rem;padding:1rem 1.35rem;background:rgba(220,38,38,.05);border:1px solid rgba(220,38,38,.18);border-radius:5px;font-size:13px;color:#DC2626;line-height:1.65}
.gt-contact .error-banner.show{display:block}

/* Offices */
.gt-contact .offices-sec{background:#F8F7F5;border-top:1px solid #E5E2DC;border-bottom:1px solid #E5E2DC;padding:52px 0}
.gt-contact .offices-inner{max-width:1200px;margin:0 auto;padding:0 48px}
.gt-contact .sec-label{font-size:11px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;font-family:monospace;color:#9CA3AF;margin-bottom:.35rem}
.gt-contact .sec-h2{font-size:22px;font-weight:600;color:#0A0E1A;letter-spacing:-.02em;margin-bottom:2rem}
.gt-contact .offices-grid{display:grid;grid-template-columns:2fr 1fr 1fr;gap:2rem}
.gt-contact .ocard{padding:1.75rem;background:#fff;border:1px solid #E5E2DC;border-radius:8px}
.gt-contact .o-num{font-size:10px;font-family:monospace;font-weight:700;letter-spacing:.1em;color:#C8A96E;text-transform:uppercase;margin-bottom:.5rem}
.gt-contact .o-name{font-size:15px;font-weight:600;color:#0A0E1A;margin-bottom:.9rem}
.gt-contact .o-row{display:flex;gap:.55rem;margin-bottom:.45rem;align-items:flex-start;font-size:13px;color:#4B5563;line-height:1.55}
.gt-contact .o-row-icon{opacity:.5;flex-shrink:0;margin-top:.15rem}
.gt-contact .o-map{display:inline-flex;align-items:center;gap:.4rem;font-size:11.5px;font-weight:700;font-family:monospace;color:#C8A96E;letter-spacing:.04em;margin-top:.9rem;text-decoration:none}
.gt-contact .o-map:hover{color:#0A0E1A}
.gt-contact .hours-card,.gt-contact .langs-card{padding:1.75rem;background:#fff;border:1px solid #E5E2DC;border-radius:8px}
.gt-contact .card-title{font-size:10.5px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;font-family:monospace;color:#9CA3AF;margin-bottom:1rem}
.gt-contact .h-row{display:flex;justify-content:space-between;align-items:center;padding:.42rem 0;border-bottom:1px solid #E5E2DC;font-size:12.5px}
.gt-contact .h-row:last-child{border-bottom:none}
.gt-contact .h-day{color:#4B5563}
.gt-contact .h-val{font-weight:600;color:#111827;font-family:monospace;font-size:12px}
.gt-contact .h-tag{font-size:10px;font-family:monospace;font-weight:700;padding:.15rem .45rem;border-radius:3px;background:rgba(5,150,105,.06);color:#065f46;border:1px solid rgba(5,150,105,.2)}
.gt-contact .h-tag.off{background:#F8F7F5;color:#9CA3AF;border-color:#E5E2DC}
.gt-contact .l-row{display:flex;align-items:center;gap:.55rem;padding:.42rem 0;border-bottom:1px solid #E5E2DC;font-size:13px}
.gt-contact .l-row:last-child{border-bottom:none}
.gt-contact .l-flag{font-size:16px;flex-shrink:0}
.gt-contact .l-name{font-weight:600;color:#111827;flex:1}
.gt-contact .l-native{font-size:11.5px;color:#9CA3AF}

@media(max-width:960px){
  .gt-contact .po-inner,.gt-contact .offices-grid{grid-template-columns:1fr}
  .gt-contact .cat-grid{grid-template-columns:1fr 1fr}
  .gt-contact .fg.g2,.gt-contact .fg.g3{grid-template-columns:1fr}
  .gt-contact .ff{flex-direction:column;align-items:flex-start}
  .gt-contact .po-inner,.gt-contact .cat-section,.gt-contact .form-panel,.gt-contact .offices-inner,.gt-contact .bci{padding-left:20px;padding-right:20px}
}
html:lang(ar) .gt-contact .cat-arrow { right: auto; left: .85rem; transform: scaleX(-1); }
html:lang(ar) .gt-contact .fsel { background-position: left .65rem center; padding-right: .8rem; padding-left: 2rem; }
</style>

@php
$cats = ['rfq' => ['01', '📋'], 'technical' => ['02', '🔬'], 'partnership' => ['03', '🤝'], 'media' => ['04', '📰'], 'other' => ['05', '💬']];
$privacyUrl = '/' . $locale . '/pages/privacy-policy';
$sendIcon = '<svg width="15" height="15" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 2L9 11M18 2L13 18l-4-7-7-4 16-5z"/></svg>';
@endphp

<div class="gt-contact">

  <div class="top-accent"></div>

  <nav class="bc" aria-label="Breadcrumb">
    <div class="bci">
      <span class="bcs"><a href="/{{ $locale }}">{{ __('contact.home') }}</a></span>
      <span class="bcsep">›</span>
      <span class="bcs cur" aria-current="page">{{ __('contact.contact') }}</span>
    </div>
  </nav>

  <section class="page-open">
    <div class="po-inner">
      <div>
        <div class="po-kicker">{{ __('contact.kicker') }}</div>
        <h1 class="po-h1">{{ __('contact.h1_a') }}<br>{{ __('contact.h1_b') }}</h1>
        <p class="po-sub">{{ __('contact.intro') }}</p>
      </div>
      <div class="phone-panel">
        <div class="pp-label">{{ __('contact.phone_label') }}</div>
        <a href="tel:+902166292029" class="pp-number" dir="ltr">☎ +90 216 629 20 29</a>
        <div class="pp-hours">{{ __('contact.phone_hours') }}</div>
        <div class="pp-rows">
          <div class="pp-row"><span class="pp-rowlabel">{{ __('contact.rfq_response') }}</span><span class="pp-badge">⚡ {{ __('contact.within_24h') }}</span></div>
          <div class="pp-row"><span class="pp-rowlabel">{{ __('contact.languages') }}</span><span class="pp-rowval">EN · TR · AR · FR</span></div>
          <div class="pp-row"><span class="pp-rowlabel">{{ __('contact.countries_served') }}</span><span class="pp-rowval">40+</span></div>
          <div class="pp-row"><span class="pp-rowlabel">{{ __('contact.verticals') }}</span><span class="pp-rowval">7</span></div>
        </div>
      </div>
    </div>
  </section>

  <section class="cat-section">
    <div class="cat-label">{{ __('contact.cat_label') }}</div>
    <div class="cat-heading">{{ __('contact.cat_heading') }}</div>
    <div class="cat-grid">
      @foreach ($cats as $key => [$num, $icon])
        <div class="cat-card" id="cat-{{ $key }}" onclick="gtSelectCat('{{ $key }}')" tabindex="0" role="button" aria-label="{{ __('contact.cat.'.$key.'.name') }}">
          <span class="cat-num">{{ $num }}</span>
          <span class="cat-icon">{{ $icon }}</span>
          <div class="cat-name">{{ __('contact.cat.'.$key.'.name') }}</div>
          <div class="cat-desc">{{ __('contact.cat.'.$key.'.desc') }}</div>
          <span class="cat-arrow">→</span>
        </div>
      @endforeach
    </div>
  </section>

  <div class="form-panel">

    {{-- ═════════ RFQ ═════════ --}}
    <div class="fc" id="fc-rfq">
      <div class="fh">
        <div class="fh-left">
          <div class="fh-title">{{ __('contact.rfq.title') }}</div>
          <div class="fh-sub">{{ __('contact.rfq.sub') }}</div>
        </div>
        <button class="fh-close" type="button" onclick="gtCloseForm()" aria-label="{{ __('contact.close') }}">✕</button>
      </div>
      <form id="gtf-rfq" novalidate>
        @csrf
        <input type="hidden" name="form_type" value="rfq">
        <div class="success-banner" id="sb-rfq"><div class="sb-icon">✅</div><div><div class="sb-title">{{ __('contact.rfq.ok_title') }}</div><div class="sb-text">{{ __('contact.rfq.ok_text') }}</div></div></div>
        <div class="error-banner" id="eb-rfq">{{ __('contact.error_generic') }}</div>
        <div class="fb">
          <div class="fsec-label">{{ __('contact.rfq.sec_details') }}</div>
          <div class="fg g3">
            <div class="fgroup"><label class="flabel" for="rfq-name">{{ __('contact.f.name') }} <em>*</em></label><input class="finput" type="text" id="rfq-name" name="name" required placeholder="{{ __('contact.f.name_ph') }}"><span class="ferr" id="e-rfq-name">{{ __('contact.err.name') }}</span></div>
            <div class="fgroup"><label class="flabel" for="rfq-company">{{ __('contact.f.company') }} <em>*</em></label><input class="finput" type="text" id="rfq-company" name="company" required placeholder="{{ __('contact.f.company_ph') }}"><span class="ferr" id="e-rfq-company">{{ __('contact.err.company') }}</span></div>
            <div class="fgroup"><label class="flabel" for="rfq-email">{{ __('contact.f.work_email') }} <em>*</em></label><input class="finput" type="email" id="rfq-email" name="email" required placeholder="you@company.com" dir="ltr"><span class="ferr" id="e-rfq-email">{{ __('contact.err.email') }}</span></div>
          </div>
          <div class="fg g3">
            <div class="fgroup"><label class="flabel" for="rfq-phone">{{ __('contact.f.phone') }}</label><input class="finput" type="tel" id="rfq-phone" name="phone" placeholder="+1 555 000 0000" dir="ltr"></div>
            <div class="fgroup"><label class="flabel" for="rfq-country">{{ __('contact.f.country') }} <em>*</em></label>
              <select class="fsel" id="rfq-country" name="country" required>
                <option value="" disabled selected>{{ __('contact.f.select_country') }}</option>
                @foreach (__('contact.countries') as $group => $items)
                  <optgroup label="{{ __('contact.country_groups.'.$group) }}">
                    @foreach ($items as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach
                  </optgroup>
                @endforeach
                <option value="other">{{ __('contact.other_option') }}</option>
              </select>
              <span class="ferr" id="e-rfq-country">{{ __('contact.err.country_select') }}</span>
            </div>
            <div class="fgroup"><label class="flabel" for="rfq-urgency">{{ __('contact.rfq.timeline') }}</label>
              <select class="fsel" id="rfq-urgency" name="urgency">
                <option value="" disabled selected>{{ __('contact.rfq.select_timeline') }}</option>
                @foreach (__('contact.timelines') as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach
              </select>
            </div>
          </div>
          <div class="fdivider"></div>
          <div class="fsec-label">{{ __('contact.rfq.sec_product') }}</div>
          <div class="fg g3">
            <div class="fgroup"><label class="flabel" for="rfq-category">{{ __('contact.rfq.category') }} <em>*</em></label>
              <select class="fsel" id="rfq-category" name="category" required>
                <option value="" disabled selected>{{ __('contact.rfq.select_category') }}</option>
                @foreach (__('contact.rfq_categories') as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach
              </select>
              <span class="ferr" id="e-rfq-category">{{ __('contact.err.category') }}</span>
            </div>
            <div class="fgroup"><label class="flabel" for="rfq-incoterm">{{ __('contact.rfq.incoterm') }}</label>
              <select class="fsel" id="rfq-incoterm" name="incoterm">
                <option value="" disabled selected>{{ __('contact.rfq.select_incoterm') }}</option>
                @foreach (__('contact.incoterms') as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach
              </select>
            </div>
            <div class="fgroup"><label class="flabel" for="rfq-qty">{{ __('contact.rfq.quantity') }}</label><input class="finput" type="text" id="rfq-qty" name="quantity" placeholder="{{ __('contact.rfq.quantity_ph') }}"></div>
          </div>
          <div class="fg">
            <div class="fgroup">
              <label class="flabel" for="rfq-spec">{{ __('contact.rfq.spec') }} <em>*</em></label>
              <textarea class="ftarea" id="rfq-spec" name="specification" required style="min-height:130px" placeholder="{{ __('contact.rfq.spec_ph') }}"></textarea>
              <span class="fhint">{{ __('contact.rfq.spec_hint') }}</span>
              <span class="ferr" id="e-rfq-spec">{{ __('contact.err.spec') }}</span>
            </div>
          </div>
        </div>
        <div class="ff">
          <button class="fsubmit" type="submit">{!! $sendIcon !!} {{ __('contact.rfq.submit') }}</button>
          <div class="flegend">{{ __('contact.privacy_prefix') }} <a href="{{ $privacyUrl }}">{{ __('contact.privacy') }}</a>. {{ __('contact.required_note') }}</div>
        </div>
      </form>
    </div>

    {{-- ═════════ TECHNICAL ═════════ --}}
    <div class="fc" id="fc-technical">
      <div class="fh">
        <div class="fh-left"><div class="fh-title">{{ __('contact.technical.title') }}</div><div class="fh-sub">{{ __('contact.technical.sub') }}</div></div>
        <button class="fh-close" type="button" onclick="gtCloseForm()" aria-label="{{ __('contact.close') }}">✕</button>
      </div>
      <form id="gtf-technical" novalidate>
        @csrf
        <input type="hidden" name="form_type" value="technical">
        <div class="success-banner" id="sb-technical"><div class="sb-icon">✅</div><div><div class="sb-title">{{ __('contact.technical.ok_title') }}</div><div class="sb-text">{{ __('contact.technical.ok_text') }}</div></div></div>
        <div class="error-banner" id="eb-technical">{{ __('contact.error_generic') }}</div>
        <div class="fb">
          <div class="fg g2">
            <div class="fgroup"><label class="flabel" for="te-name">{{ __('contact.f.name') }} <em>*</em></label><input class="finput" type="text" id="te-name" name="name" required placeholder="{{ __('contact.f.name_ph') }}"><span class="ferr" id="e-te-name">{{ __('contact.err.name') }}</span></div>
            <div class="fgroup"><label class="flabel" for="te-company">{{ __('contact.f.company') }}</label><input class="finput" type="text" id="te-company" name="company" placeholder="{{ __('contact.f.company_ph') }}"></div>
          </div>
          <div class="fg g2">
            <div class="fgroup"><label class="flabel" for="te-email">{{ __('contact.f.email') }} <em>*</em></label><input class="finput" type="email" id="te-email" name="email" required placeholder="you@company.com" dir="ltr"><span class="ferr" id="e-te-email">{{ __('contact.err.email') }}</span></div>
            <div class="fgroup"><label class="flabel" for="te-product">{{ __('contact.technical.product') }} <em>*</em></label><input class="finput" type="text" id="te-product" name="product" required placeholder="{{ __('contact.technical.product_ph') }}"><span class="ferr" id="e-te-product">{{ __('contact.err.product') }}</span></div>
          </div>
          <div class="fg">
            <div class="fgroup"><label class="flabel" for="te-question">{{ __('contact.technical.question') }} <em>*</em></label><textarea class="ftarea" id="te-question" name="question" required placeholder="{{ __('contact.technical.question_ph') }}"></textarea><span class="ferr" id="e-te-question">{{ __('contact.err.question') }}</span></div>
          </div>
        </div>
        <div class="ff">
          <button class="fsubmit" type="submit">{!! $sendIcon !!} {{ __('contact.technical.submit') }}</button>
          <div class="flegend">{{ __('contact.privacy_prefix') }} <a href="{{ $privacyUrl }}">{{ __('contact.privacy') }}</a>. {{ __('contact.required_note') }}</div>
        </div>
      </form>
    </div>

    {{-- ═════════ PARTNERSHIP ═════════ --}}
    <div class="fc" id="fc-partnership">
      <div class="fh">
        <div class="fh-left"><div class="fh-title">{{ __('contact.partnership.title') }}</div><div class="fh-sub">{{ __('contact.partnership.sub') }}</div></div>
        <button class="fh-close" type="button" onclick="gtCloseForm()" aria-label="{{ __('contact.close') }}">✕</button>
      </div>
      <form id="gtf-partnership" novalidate>
        @csrf
        <input type="hidden" name="form_type" value="partnership">
        <div class="success-banner" id="sb-partnership"><div class="sb-icon">✅</div><div><div class="sb-title">{{ __('contact.partnership.ok_title') }}</div><div class="sb-text">{{ __('contact.partnership.ok_text') }}</div></div></div>
        <div class="error-banner" id="eb-partnership">{{ __('contact.error_generic') }}</div>
        <div class="fb">
          <div class="fg g3">
            <div class="fgroup"><label class="flabel" for="pa-name">{{ __('contact.f.name') }} <em>*</em></label><input class="finput" type="text" id="pa-name" name="name" required placeholder="{{ __('contact.f.name_ph') }}"><span class="ferr" id="e-pa-name">{{ __('contact.err.name') }}</span></div>
            <div class="fgroup"><label class="flabel" for="pa-title">{{ __('contact.partnership.job_title') }}</label><input class="finput" type="text" id="pa-title" name="title" placeholder="{{ __('contact.partnership.job_title_ph') }}"></div>
            <div class="fgroup"><label class="flabel" for="pa-email">{{ __('contact.f.work_email') }} <em>*</em></label><input class="finput" type="email" id="pa-email" name="email" required placeholder="you@company.com" dir="ltr"><span class="ferr" id="e-pa-email">{{ __('contact.err.email') }}</span></div>
          </div>
          <div class="fg g3">
            <div class="fgroup"><label class="flabel" for="pa-company">{{ __('contact.f.company_name') }} <em>*</em></label><input class="finput" type="text" id="pa-company" name="company" required placeholder="{{ __('contact.f.company_name_ph') }}"><span class="ferr" id="e-pa-company">{{ __('contact.err.company') }}</span></div>
            <div class="fgroup"><label class="flabel" for="pa-country">{{ __('contact.partnership.country_op') }} <em>*</em></label><input class="finput" type="text" id="pa-country" name="country" required placeholder="{{ __('contact.partnership.country_op_ph') }}"><span class="ferr" id="e-pa-country">{{ __('contact.err.country_text') }}</span></div>
            <div class="fgroup"><label class="flabel" for="pa-type">{{ __('contact.partnership.type') }} <em>*</em></label>
              <select class="fsel" id="pa-type" name="type" required>
                <option value="" disabled selected>{{ __('contact.partnership.select_type') }}</option>
                @foreach (__('contact.partner_types') as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach
              </select>
              <span class="ferr" id="e-pa-type">{{ __('contact.err.type') }}</span>
            </div>
          </div>
          <div class="fg">
            <div class="fgroup"><label class="flabel" for="pa-message">{{ __('contact.partnership.proposal') }} <em>*</em></label><textarea class="ftarea" id="pa-message" name="message" required placeholder="{{ __('contact.partnership.proposal_ph') }}"></textarea><span class="ferr" id="e-pa-message">{{ __('contact.err.proposal') }}</span></div>
          </div>
        </div>
        <div class="ff">
          <button class="fsubmit" type="submit">{!! $sendIcon !!} {{ __('contact.partnership.submit') }}</button>
          <div class="flegend">{{ __('contact.privacy_prefix') }} <a href="{{ $privacyUrl }}">{{ __('contact.privacy') }}</a>. {{ __('contact.required_note') }}</div>
        </div>
      </form>
    </div>

    {{-- ═════════ MEDIA ═════════ --}}
    <div class="fc" id="fc-media">
      <div class="fh">
        <div class="fh-left"><div class="fh-title">{{ __('contact.media.title') }}</div><div class="fh-sub">{{ __('contact.media.sub') }}</div></div>
        <button class="fh-close" type="button" onclick="gtCloseForm()" aria-label="{{ __('contact.close') }}">✕</button>
      </div>
      <form id="gtf-media" novalidate>
        @csrf
        <input type="hidden" name="form_type" value="media">
        <div class="success-banner" id="sb-media"><div class="sb-icon">✅</div><div><div class="sb-title">{{ __('contact.media.ok_title') }}</div><div class="sb-text">{{ __('contact.media.ok_text') }}</div></div></div>
        <div class="error-banner" id="eb-media">{{ __('contact.error_generic') }}</div>
        <div class="fb">
          <div class="fg g3">
            <div class="fgroup"><label class="flabel" for="me-name">{{ __('contact.f.name') }} <em>*</em></label><input class="finput" type="text" id="me-name" name="name" required placeholder="{{ __('contact.f.name_ph') }}"><span class="ferr" id="e-me-name">{{ __('contact.err.name') }}</span></div>
            <div class="fgroup"><label class="flabel" for="me-pub">{{ __('contact.media.publication') }} <em>*</em></label><input class="finput" type="text" id="me-pub" name="publication" required placeholder="{{ __('contact.media.publication_ph') }}"><span class="ferr" id="e-me-pub">{{ __('contact.err.publication') }}</span></div>
            <div class="fgroup"><label class="flabel" for="me-email">{{ __('contact.f.email') }} <em>*</em></label><input class="finput" type="email" id="me-email" name="email" required placeholder="you@publication.com" dir="ltr"><span class="ferr" id="e-me-email">{{ __('contact.err.email') }}</span></div>
          </div>
          <div class="fg">
            <div class="fgroup"><label class="flabel" for="me-request">{{ __('contact.media.request') }} <em>*</em></label><textarea class="ftarea" id="me-request" name="request" required placeholder="{{ __('contact.media.request_ph') }}"></textarea><span class="ferr" id="e-me-request">{{ __('contact.err.request') }}</span></div>
          </div>
        </div>
        <div class="ff">
          <button class="fsubmit" type="submit">{!! $sendIcon !!} {{ __('contact.media.submit') }}</button>
          <div class="flegend">{{ __('contact.privacy_prefix') }} <a href="{{ $privacyUrl }}">{{ __('contact.privacy') }}</a>. {{ __('contact.required_note') }}</div>
        </div>
      </form>
    </div>

    {{-- ═════════ OTHER ═════════ --}}
    <div class="fc" id="fc-other">
      <div class="fh">
        <div class="fh-left"><div class="fh-title">{{ __('contact.other.title') }}</div><div class="fh-sub">{{ __('contact.other.sub') }}</div></div>
        <button class="fh-close" type="button" onclick="gtCloseForm()" aria-label="{{ __('contact.close') }}">✕</button>
      </div>
      <form id="gtf-other" novalidate>
        @csrf
        <input type="hidden" name="form_type" value="other">
        <div class="success-banner" id="sb-other"><div class="sb-icon">✅</div><div><div class="sb-title">{{ __('contact.other.ok_title') }}</div><div class="sb-text">{{ __('contact.other.ok_text') }}</div></div></div>
        <div class="error-banner" id="eb-other">{{ __('contact.error_generic') }}</div>
        <div class="fb">
          <div class="fg g2">
            <div class="fgroup"><label class="flabel" for="ot-name">{{ __('contact.f.name') }} <em>*</em></label><input class="finput" type="text" id="ot-name" name="name" required placeholder="{{ __('contact.f.name_ph') }}"><span class="ferr" id="e-ot-name">{{ __('contact.err.name') }}</span></div>
            <div class="fgroup"><label class="flabel" for="ot-email">{{ __('contact.f.email') }} <em>*</em></label><input class="finput" type="email" id="ot-email" name="email" required placeholder="you@company.com" dir="ltr"><span class="ferr" id="e-ot-email">{{ __('contact.err.email') }}</span></div>
          </div>
          <div class="fg">
            <div class="fgroup"><label class="flabel" for="ot-subject">{{ __('contact.other.subject') }} <em>*</em></label><input class="finput" type="text" id="ot-subject" name="subject" required placeholder="{{ __('contact.other.subject_ph') }}"><span class="ferr" id="e-ot-subject">{{ __('contact.err.subject') }}</span></div>
          </div>
          <div class="fg">
            <div class="fgroup"><label class="flabel" for="ot-message">{{ __('contact.other.message') }} <em>*</em></label><textarea class="ftarea" id="ot-message" name="message" required placeholder="{{ __('contact.other.message_ph') }}"></textarea><span class="ferr" id="e-ot-message">{{ __('contact.err.message') }}</span></div>
          </div>
        </div>
        <div class="ff">
          <button class="fsubmit" type="submit">{!! $sendIcon !!} {{ __('contact.other.submit') }}</button>
          <div class="flegend">{{ __('contact.privacy_prefix') }} <a href="{{ $privacyUrl }}">{{ __('contact.privacy') }}</a>. {{ __('contact.required_note') }}</div>
        </div>
      </form>
    </div>

  </div>{{-- /form-panel --}}

  {{-- ═════════ Offices ═════════ --}}
  <section class="offices-sec">
    <div class="offices-inner">
      <div class="sec-label">{{ __('contact.loc_label') }}</div>
      <div class="sec-h2">{{ __('contact.loc_heading') }}</div>
      <div class="offices-grid">
        <div class="ocard">
          <div class="o-num">{{ __('contact.hq') }}</div>
          <div class="o-name">Varyap Meridian — Ataşehir</div>
          <div class="o-row"><span class="o-row-icon">📍</span><span>Al Zambak Sokağı, Varyap Meridian A Blok<br>34746 Ataşehir / İstanbul, Türkiye</span></div>
          <div class="o-row"><span class="o-row-icon">☎</span><a href="tel:+902166292029" style="color:#0A0E1A" dir="ltr">+90 216 629 20 29</a></div>
          <div class="o-row"><span class="o-row-icon">✉</span><a href="mailto:info@globaltrding.com" style="color:#0A0E1A" dir="ltr">info@globaltrding.com</a></div>
          <a class="o-map" href="https://maps.google.com/?q=Varyap+Meridian+A+Blok+Atasehir+Istanbul" target="_blank" rel="noopener">↗ {{ __('contact.open_maps') }}</a>
        </div>
        <div class="hours-card">
          <div class="card-title">{{ __('contact.hours_title') }}</div>
          <div class="h-row"><span class="h-day">{{ __('contact.mon_fri') }}</span><div style="display:flex;align-items:center;gap:.5rem"><span class="h-val" dir="ltr">09:00 – 18:00</span><span class="h-tag">TRT</span></div></div>
          <div class="h-row"><span class="h-day">{{ __('contact.saturday') }}</span><span class="h-val">{{ __('contact.by_arrangement') }}</span></div>
          <div class="h-row"><span class="h-day">{{ __('contact.sunday') }}</span><span class="h-val">{{ __('contact.closed') }}</span></div>
          <div class="h-row" style="background:rgba(200,169,110,.04)"><span class="h-day" style="font-weight:600;color:#0A0E1A">{{ __('contact.rfq_inbox') }}</span><span class="h-tag">{{ __('contact.h24') }}</span></div>
        </div>
        <div class="langs-card">
          <div class="card-title">{{ __('contact.langs_title') }}</div>
          <div class="l-row"><div class="l-flag">🇬🇧</div><div class="l-name">{{ __('contact.lang_names.en') }}</div><div class="l-native">{{ __('contact.primary') }}</div></div>
          <div class="l-row"><div class="l-flag">🇹🇷</div><div class="l-name">{{ __('contact.lang_names.tr') }}</div><div class="l-native">Türkçe</div></div>
          <div class="l-row"><div class="l-flag">🇸🇦</div><div class="l-name">{{ __('contact.lang_names.ar') }}</div><div class="l-native">العربية</div></div>
          <div class="l-row"><div class="l-flag">🇫🇷</div><div class="l-name">{{ __('contact.lang_names.fr') }}</div><div class="l-native">Français</div></div>
        </div>
      </div>
    </div>
  </section>

</div>{{-- /gt-contact --}}

<script>
  const GT_I18N = { sending: @json(__('contact.sending')) };
  // ── Category / form switching ────────────────────────────────────
  function gtSelectCat(id) {
    document.querySelectorAll('.gt-contact .cat-card').forEach(c => c.classList.remove('active'));
    document.querySelectorAll('.gt-contact .fc').forEach(f => f.classList.remove('active'));
    const cat = document.getElementById('cat-' + id);
    if (cat) cat.classList.add('active');
    const fc = document.getElementById('fc-' + id);
    if (fc) {
      fc.classList.add('active');
      setTimeout(() => fc.scrollIntoView({ behavior: 'smooth', block: 'start' }), 60);
    }
  }

  function gtCloseForm() {
    document.querySelectorAll('.gt-contact .cat-card').forEach(c => c.classList.remove('active'));
    document.querySelectorAll('.gt-contact .fc').forEach(f => f.classList.remove('active'));
  }

  // Keyboard support on category cards
  document.querySelectorAll('.gt-contact .cat-card').forEach(card => {
    card.addEventListener('keydown', e => {
      if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); card.click(); }
    });
  });

  // ── Validation ──────────────────────────────────────────────────
  function gtValidEmail(v) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v.trim()); }

  function gtValidateField(el, errId) {
    const ok = el.type === 'email' ? gtValidEmail(el.value) : el.value.trim() !== '';
    el.classList.toggle('invalid', !ok);
    const errEl = document.getElementById(errId);
    if (errEl) errEl.classList.toggle('show', !ok);
    return ok;
  }

  // ── Form configs → endpoint mapping ────────────────────────────
  const GT_FORMS = {
    rfq: {
      required: [
        ['rfq-name','e-rfq-name'],['rfq-company','e-rfq-company'],
        ['rfq-email','e-rfq-email'],['rfq-country','e-rfq-country'],
        ['rfq-category','e-rfq-category'],['rfq-spec','e-rfq-spec'],
      ],
      endpoint: '{{ route("contact.submit", ["locale" => $locale]) }}',
    },
    technical: {
      required: [['te-name','e-te-name'],['te-email','e-te-email'],['te-product','e-te-product'],['te-question','e-te-question']],
      endpoint: '{{ route("contact.submit", ["locale" => $locale]) }}',
    },
    partnership: {
      required: [['pa-name','e-pa-name'],['pa-email','e-pa-email'],['pa-company','e-pa-company'],['pa-country','e-pa-country'],['pa-type','e-pa-type'],['pa-message','e-pa-message']],
      endpoint: '{{ route("contact.submit", ["locale" => $locale]) }}',
    },
    media: {
      required: [['me-name','e-me-name'],['me-pub','e-me-pub'],['me-email','e-me-email'],['me-request','e-me-request']],
      endpoint: '{{ route("contact.submit", ["locale" => $locale]) }}',
    },
    other: {
      required: [['ot-name','e-ot-name'],['ot-email','e-ot-email'],['ot-subject','e-ot-subject'],['ot-message','e-ot-message']],
      endpoint: '{{ route("contact.submit", ["locale" => $locale]) }}',
    },
  };

  Object.entries(GT_FORMS).forEach(([key, cfg]) => {
    const form = document.getElementById('gtf-' + key);
    if (!form) return;

    // Blur validation
    cfg.required.forEach(([id, errId]) => {
      const el = document.getElementById(id);
      if (!el) return;
      el.addEventListener('blur', () => gtValidateField(el, errId));
      el.addEventListener('input', () => { if (el.classList.contains('invalid')) gtValidateField(el, errId); });
    });

    form.addEventListener('submit', async e => {
      e.preventDefault();

      // Hide previous banners
      document.getElementById('sb-' + key)?.classList.remove('show');
      document.getElementById('eb-' + key)?.classList.remove('show');

      // Validate
      let ok = true;
      cfg.required.forEach(([id, errId]) => {
        const el = document.getElementById(id);
        if (el && !gtValidateField(el, errId)) ok = false;
      });
      if (!ok) return;

      const btn = form.querySelector('.fsubmit');
      const origText = btn?.innerHTML;
      if (btn) { btn.disabled = true; btn.textContent = GT_I18N.sending; }

      try {
        const res = await fetch(cfg.endpoint, {
          method: 'POST',
          body: new FormData(form),
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });

        if (res.ok) {
          const sb = document.getElementById('sb-' + key);
          if (sb) { sb.classList.add('show'); sb.scrollIntoView({ behavior:'smooth', block:'center' }); }
          form.reset();
          form.querySelectorAll('.invalid').forEach(el => el.classList.remove('invalid'));
          form.querySelectorAll('.ferr.show').forEach(el => el.classList.remove('show'));
        } else {
          document.getElementById('eb-' + key)?.classList.add('show');
        }
      } catch {
        document.getElementById('eb-' + key)?.classList.add('show');
      } finally {
        if (btn) { btn.disabled = false; btn.innerHTML = origText; }
      }
    });
  });
</script>
@endsection