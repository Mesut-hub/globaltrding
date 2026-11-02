{{-- resources/views/pages/contact.blade.php --}}
@extends('layouts.app')

@section('meta_title', 'Contact | Global Trading Ltd. Co. — Istanbul')
@section('meta_description', 'Contact Global Trading Ltd. Co. Submit an RFQ, technical enquiry, or partnership proposal. 24-hour response. English, Turkish, Arabic, French.')

@php
$hasHero = false;
$locale  = app()->getLocale();
$seoMeta = [
    'title'       => 'Contact | Global Trading Ltd. Co. — Istanbul',
    'description' => 'Submit an RFQ, technical enquiry, or partnership proposal. Our sourcing team responds within 24 hours. Istanbul, Turkey.',
    'canonical'   => config('app.url') . '/' . $locale . '/pages/contact',
    'ogType'      => 'website',
    'robots'      => 'index,follow',
    'schema'      => [
        '@context' => 'https://schema.org',
        '@type'    => 'ContactPage',
        '@id'      => config('app.url') . '/' . $locale . '/pages/contact',
        'name'     => 'Contact Global Trading Ltd. Co.',
        'url'      => config('app.url') . '/' . $locale . '/pages/contact',
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
</style>

<div class="gt-contact">

  {{-- Top accent --}}
  <div class="top-accent"></div>

  {{-- Breadcrumb --}}
  <nav class="bc" aria-label="Breadcrumb">
    <div class="bci">
      <span class="bcs"><a href="/{{ $locale }}">Home</a></span>
      <span class="bcsep">›</span>
      <span class="bcs cur" aria-current="page">Contact</span>
    </div>
  </nav>

  {{-- Page opening --}}
  <section class="page-open">
    <div class="po-inner">
      <div>
        <div class="po-kicker">Get in touch</div>
        <h1 class="po-h1">Contact<br>Global Trading.</h1>
        <p class="po-sub">Select the nature of your enquiry below. Every request is reviewed by the person responsible for that product area — not a shared inbox or automated routing system.</p>
      </div>
      <div class="phone-panel">
        <div class="pp-label">Direct telephone</div>
        <a href="tel:+902166292029" class="pp-number">☎ +90 216 629 20 29</a>
        <div class="pp-hours">Istanbul, Türkiye · Monday – Friday · 09:00 – 18:00 TRT</div>
        <div class="pp-rows">
          <div class="pp-row"><span class="pp-rowlabel">RFQ response</span><span class="pp-badge">⚡ Within 24 hours</span></div>
          <div class="pp-row"><span class="pp-rowlabel">Languages</span><span class="pp-rowval">EN · TR · AR · FR</span></div>
          <div class="pp-row"><span class="pp-rowlabel">Countries served</span><span class="pp-rowval">40+</span></div>
          <div class="pp-row"><span class="pp-rowlabel">Product verticals</span><span class="pp-rowval">7</span></div>
        </div>
      </div>
    </div>
  </section>

  {{-- Category selector --}}
  <section class="cat-section">
    <div class="cat-label">My enquiry is related to</div>
    <div class="cat-heading">Select a category to open the right form</div>
    <div class="cat-grid">
      <div class="cat-card" id="cat-rfq" onclick="gtSelectCat('rfq')" tabindex="0" role="button" aria-label="Request for Quotation">
        <span class="cat-num">01</span>
        <span class="cat-icon">📋</span>
        <div class="cat-name">Request for Quotation</div>
        <div class="cat-desc">Products, materials, BOM, pricing, lead time, Incoterms</div>
        <span class="cat-arrow">→</span>
      </div>
      <div class="cat-card" id="cat-technical" onclick="gtSelectCat('technical')" tabindex="0" role="button" aria-label="Technical Enquiry">
        <span class="cat-num">02</span>
        <span class="cat-icon">🔬</span>
        <div class="cat-name">Technical Enquiry</div>
        <div class="cat-desc">Specifications, standards, approvals, documentation</div>
        <span class="cat-arrow">→</span>
      </div>
      <div class="cat-card" id="cat-partnership" onclick="gtSelectCat('partnership')" tabindex="0" role="button" aria-label="Partnership and Distribution">
        <span class="cat-num">03</span>
        <span class="cat-icon">🤝</span>
        <div class="cat-name">Partnership &amp; Distribution</div>
        <div class="cat-desc">Manufacturer representation, distribution, co-supply</div>
        <span class="cat-arrow">→</span>
      </div>
      <div class="cat-card" id="cat-media" onclick="gtSelectCat('media')" tabindex="0" role="button" aria-label="Press and Media">
        <span class="cat-num">04</span>
        <span class="cat-icon">📰</span>
        <div class="cat-name">Press &amp; Media</div>
        <div class="cat-desc">Editorial requests, interviews, company background</div>
        <span class="cat-arrow">→</span>
      </div>
      <div class="cat-card" id="cat-other" onclick="gtSelectCat('other')" tabindex="0" role="button" aria-label="Other enquiry">
        <span class="cat-num">05</span>
        <span class="cat-icon">💬</span>
        <div class="cat-name">Other</div>
        <div class="cat-desc">Anything that does not fit the above categories</div>
        <span class="cat-arrow">→</span>
      </div>
    </div>
  </section>

  {{-- Form panels --}}
  <div class="form-panel">

    {{-- RFQ --}}
    <div class="fc" id="fc-rfq">
      <div class="fh">
        <div class="fh-left">
          <div class="fh-title">Request for Quotation (RFQ)</div>
          <div class="fh-sub">Provide your specification and quantity. We respond with an itemised quotation — price, lead time, Incoterm, and documentation list — within 24 hours.</div>
        </div>
        <button class="fh-close" onclick="gtCloseForm()" aria-label="Close">✕</button>
      </div>
      <form id="gtf-rfq" novalidate>
        @csrf
        <input type="hidden" name="form_type" value="rfq">
        <div class="success-banner" id="sb-rfq"><div class="sb-icon">✅</div><div><div class="sb-title">RFQ received — we will respond within 24 hours.</div><div class="sb-text">Our technical sourcing team will review your specification. For urgent requirements, call +90 216 629 20 29 directly.</div></div></div>
        <div class="error-banner" id="eb-rfq">Something went wrong. Please try again or call us on +90 216 629 20 29.</div>
        <div class="fb">
          <div class="fsec-label">Your details</div>
          <div class="fg g3">
            <div class="fgroup"><label class="flabel" for="rfq-name">Full name <em>*</em></label><input class="finput" type="text" id="rfq-name" name="name" required placeholder="First and last name"><span class="ferr" id="e-rfq-name">Please enter your name.</span></div>
            <div class="fgroup"><label class="flabel" for="rfq-company">Company <em>*</em></label><input class="finput" type="text" id="rfq-company" name="company" required placeholder="Company or organisation"><span class="ferr" id="e-rfq-company">Please enter your company name.</span></div>
            <div class="fgroup"><label class="flabel" for="rfq-email">Work email <em>*</em></label><input class="finput" type="email" id="rfq-email" name="email" required placeholder="you@company.com"><span class="ferr" id="e-rfq-email">Please enter a valid email address.</span></div>
          </div>
          <div class="fg g3">
            <div class="fgroup"><label class="flabel" for="rfq-phone">Phone / WhatsApp</label><input class="finput" type="tel" id="rfq-phone" name="phone" placeholder="+1 555 000 0000"></div>
            <div class="fgroup"><label class="flabel" for="rfq-country">Destination country <em>*</em></label>
              <select class="fsel" id="rfq-country" name="country" required>
                <option value="" disabled selected>Select country</option>
                <optgroup label="Middle East &amp; N. Africa"><option>Iraq</option><option>UAE</option><option>Saudi Arabia</option><option>Kuwait</option><option>Qatar</option><option>Oman</option><option>Egypt</option><option>Algeria</option><option>Libya</option><option>Morocco</option></optgroup>
                <optgroup label="Central Asia"><option>Kazakhstan</option><option>Azerbaijan</option><option>Uzbekistan</option><option>Turkmenistan</option><option>Georgia</option></optgroup>
                <optgroup label="Africa"><option>Nigeria</option><option>Ghana</option><option>Kenya</option><option>Tanzania</option><option>Angola</option></optgroup>
                <optgroup label="South &amp; SE Asia"><option>India</option><option>Pakistan</option><option>Bangladesh</option><option>Malaysia</option><option>Indonesia</option></optgroup>
                <optgroup label="Europe"><option>Türkiye</option><option>Romania</option><option>Bulgaria</option><option>Germany</option><option>France</option><option>United Kingdom</option></optgroup>
                <option value="other">Other</option>
              </select>
              <span class="ferr" id="e-rfq-country">Please select a country.</span>
            </div>
            <div class="fgroup"><label class="flabel" for="rfq-urgency">Timeline</label>
              <select class="fsel" id="rfq-urgency" name="urgency">
                <option value="" disabled selected>Select timeline</option>
                <option>Urgent — within 72 hours</option><option>Short — within 2 weeks</option><option>Standard — 2 to 8 weeks</option><option>Project — 2 to 6 months</option><option>Framework agreement — ongoing</option>
              </select>
            </div>
          </div>
          <div class="fdivider"></div>
          <div class="fsec-label">Product requirement</div>
          <div class="fg g3">
            <div class="fgroup"><label class="flabel" for="rfq-category">Product category <em>*</em></label>
              <select class="fsel" id="rfq-category" name="category" required>
                <option value="" disabled selected>Select category</option>
                <option>Valves &amp; Flow Control</option><option>Instrumentation &amp; Transmitters</option><option>Actuators &amp; Control Systems</option><option>Rotating Equipment &amp; Pumps</option><option>NDT &amp; Inspection Equipment</option><option>Boron Products (Eti Maden)</option><option>Industrial Chemicals</option><option>Critical Chemicals — TDI / MDI / ADN / LiPF₆ / Helium / HF</option><option>Polymers — PE &amp; PP</option><option>Lubricants &amp; Process Chemicals</option><option>Paraffin Wax &amp; Petroleum Jelly</option><option>Solar Energy Systems</option><option>Security &amp; Screening Systems</option><option>Multiple categories / project BOM</option>
              </select>
              <span class="ferr" id="e-rfq-category">Please select a product category.</span>
            </div>
            <div class="fgroup"><label class="flabel" for="rfq-incoterm">Preferred Incoterm</label>
              <select class="fsel" id="rfq-incoterm" name="incoterm"><option value="" disabled selected>Select Incoterm</option><option>EXW</option><option>FOB</option><option>CFR</option><option>CIF</option><option>DAP</option><option>DDP</option><option>Not yet decided</option></select>
            </div>
            <div class="fgroup"><label class="flabel" for="rfq-qty">Quantity / volume</label><input class="finput" type="text" id="rfq-qty" name="quantity" placeholder="e.g. 50 units, 20 MT"></div>
          </div>
          <div class="fg">
            <div class="fgroup">
              <label class="flabel" for="rfq-spec">Full specification <em>*</em></label>
              <textarea class="ftarea" id="rfq-spec" name="specification" required style="min-height:130px" placeholder="Include: product name · grade or standard (API 6D, ASME B16.34, PE100 ISO 9080) · size range · required documentation (COA, MTC, ATEX, SIL PFD) · brand preferences or approved equivalents · destination port."></textarea>
              <span class="fhint">You may also email a datasheet to rfq@globaltrding.com after submitting this form.</span>
              <span class="ferr" id="e-rfq-spec">Please describe your requirement.</span>
            </div>
          </div>
        </div>
        <div class="ff">
          <button class="fsubmit" type="submit">
            <svg width="15" height="15" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 2L9 11M18 2L13 18l-4-7-7-4 16-5z"/></svg>
            Submit RFQ — 24-hour response
          </button>
          <div class="flegend">By submitting you accept our <a href="/{{ $locale }}/pages/privacy-policy">Privacy Policy</a>. Fields marked <em style="color:#DC2626;font-style:normal">*</em> are required.</div>
        </div>
      </form>
    </div>

    {{-- TECHNICAL --}}
    <div class="fc" id="fc-technical">
      <div class="fh">
        <div class="fh-left"><div class="fh-title">Technical Enquiry</div><div class="fh-sub">Questions about specifications, standards, certification requirements, or approved equivalencies. Our technical team responds within one business day.</div></div>
        <button class="fh-close" onclick="gtCloseForm()" aria-label="Close">✕</button>
      </div>
      <form id="gtf-technical" novalidate>
        @csrf
        <input type="hidden" name="form_type" value="technical">
        <div class="success-banner" id="sb-technical"><div class="sb-icon">✅</div><div><div class="sb-title">Technical enquiry received.</div><div class="sb-text">Our technical team will respond with datasheets and references within one business day.</div></div></div>
        <div class="error-banner" id="eb-technical">Something went wrong. Please try again or call +90 216 629 20 29.</div>
        <div class="fb">
          <div class="fg g2">
            <div class="fgroup"><label class="flabel" for="te-name">Full name <em>*</em></label><input class="finput" type="text" id="te-name" name="name" required placeholder="First and last name"><span class="ferr" id="e-te-name">Please enter your name.</span></div>
            <div class="fgroup"><label class="flabel" for="te-company">Company</label><input class="finput" type="text" id="te-company" name="company" placeholder="Company name"></div>
          </div>
          <div class="fg g2">
            <div class="fgroup"><label class="flabel" for="te-email">Email address <em>*</em></label><input class="finput" type="email" id="te-email" name="email" required placeholder="you@company.com"><span class="ferr" id="e-te-email">Please enter a valid email address.</span></div>
            <div class="fgroup"><label class="flabel" for="te-product">Product or category <em>*</em></label><input class="finput" type="text" id="te-product" name="product" required placeholder="e.g. Boric acid, API 6D valve, PAUT system"><span class="ferr" id="e-te-product">Please specify the product.</span></div>
          </div>
          <div class="fg">
            <div class="fgroup"><label class="flabel" for="te-question">Your technical question <em>*</em></label><textarea class="ftarea" id="te-question" name="question" required placeholder="Describe your technical question. Reference the applicable standard, approval, or documentation requirement where relevant."></textarea><span class="ferr" id="e-te-question">Please describe your question.</span></div>
          </div>
        </div>
        <div class="ff">
          <button class="fsubmit" type="submit"><svg width="15" height="15" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 2L9 11M18 2L13 18l-4-7-7-4 16-5z"/></svg> Send technical enquiry</button>
          <div class="flegend">By submitting you accept our <a href="/{{ $locale }}/pages/privacy-policy">Privacy Policy</a>.</div>
        </div>
      </form>
    </div>

    {{-- PARTNERSHIP --}}
    <div class="fc" id="fc-partnership">
      <div class="fh">
        <div class="fh-left"><div class="fh-title">Partnership &amp; Distribution Enquiry</div><div class="fh-sub">For manufacturers seeking regional distribution, distributors seeking Turkish-origin supply, and EPC contractors looking for a structured supply partnership. A senior team member responds within 2–3 business days.</div></div>
        <button class="fh-close" onclick="gtCloseForm()" aria-label="Close">✕</button>
      </div>
      <form id="gtf-partnership" novalidate>
        @csrf
        <input type="hidden" name="form_type" value="partnership">
        <div class="success-banner" id="sb-partnership"><div class="sb-icon">✅</div><div><div class="sb-title">Partnership enquiry received.</div><div class="sb-text">A senior member of our commercial team will review your proposal and respond within 2–3 business days.</div></div></div>
        <div class="error-banner" id="eb-partnership">Something went wrong. Please try again or call +90 216 629 20 29.</div>
        <div class="fb">
          <div class="fg g3">
            <div class="fgroup"><label class="flabel" for="pa-name">Full name <em>*</em></label><input class="finput" type="text" id="pa-name" name="name" required placeholder="First and last name"><span class="ferr" id="e-pa-name">Please enter your name.</span></div>
            <div class="fgroup"><label class="flabel" for="pa-title">Job title</label><input class="finput" type="text" id="pa-title" name="title" placeholder="e.g. Export Manager"></div>
            <div class="fgroup"><label class="flabel" for="pa-email">Work email <em>*</em></label><input class="finput" type="email" id="pa-email" name="email" required placeholder="you@company.com"><span class="ferr" id="e-pa-email">Please enter a valid email address.</span></div>
          </div>
          <div class="fg g3">
            <div class="fgroup"><label class="flabel" for="pa-company">Company name <em>*</em></label><input class="finput" type="text" id="pa-company" name="company" required placeholder="Your company"><span class="ferr" id="e-pa-company">Please enter your company name.</span></div>
            <div class="fgroup"><label class="flabel" for="pa-country">Country of operation <em>*</em></label><input class="finput" type="text" id="pa-country" name="country" required placeholder="Primary country"><span class="ferr" id="e-pa-country">Please enter your country.</span></div>
            <div class="fgroup"><label class="flabel" for="pa-type">Partnership type <em>*</em></label>
              <select class="fsel" id="pa-type" name="type" required>
                <option value="" disabled selected>Select type</option>
                <option>Manufacturer — seeking distribution in MENA / CIS / Africa</option>
                <option>Distributor — seeking Turkish-origin supply</option>
                <option>EPC contractor — seeking supply partner</option>
                <option>Agent or representative enquiry</option>
                <option>Joint venture or co-supply proposal</option>
                <option>Other collaboration</option>
              </select>
              <span class="ferr" id="e-pa-type">Please select a partnership type.</span>
            </div>
          </div>
          <div class="fg">
            <div class="fgroup"><label class="flabel" for="pa-message">Describe your proposal <em>*</em></label><textarea class="ftarea" id="pa-message" name="message" required placeholder="Tell us about your company, the products or categories involved, markets in scope, and what you are looking for from a supply partnership with Global Trading."></textarea><span class="ferr" id="e-pa-message">Please describe your proposal.</span></div>
          </div>
        </div>
        <div class="ff">
          <button class="fsubmit" type="submit"><svg width="15" height="15" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 2L9 11M18 2L13 18l-4-7-7-4 16-5z"/></svg> Send partnership enquiry</button>
          <div class="flegend">By submitting you accept our <a href="/{{ $locale }}/pages/privacy-policy">Privacy Policy</a>. Fields marked <em style="color:#DC2626;font-style:normal">*</em> are required.</div>
        </div>
      </form>
    </div>

    {{-- MEDIA --}}
    <div class="fc" id="fc-media">
      <div class="fh">
        <div class="fh-left"><div class="fh-title">Press &amp; Media Enquiry</div><div class="fh-sub">Editorial requests, background information, and interview requests. We respond within one business day.</div></div>
        <button class="fh-close" onclick="gtCloseForm()" aria-label="Close">✕</button>
      </div>
      <form id="gtf-media" novalidate>
        @csrf
        <input type="hidden" name="form_type" value="media">
        <div class="success-banner" id="sb-media"><div class="sb-icon">✅</div><div><div class="sb-title">Media enquiry received.</div><div class="sb-text">We will respond within one business day.</div></div></div>
        <div class="error-banner" id="eb-media">Something went wrong. Please try again.</div>
        <div class="fb">
          <div class="fg g3">
            <div class="fgroup"><label class="flabel" for="me-name">Full name <em>*</em></label><input class="finput" type="text" id="me-name" name="name" required placeholder="Your name"><span class="ferr" id="e-me-name">Please enter your name.</span></div>
            <div class="fgroup"><label class="flabel" for="me-pub">Publication <em>*</em></label><input class="finput" type="text" id="me-pub" name="publication" required placeholder="Publication or media outlet"><span class="ferr" id="e-me-pub">Please enter your publication.</span></div>
            <div class="fgroup"><label class="flabel" for="me-email">Email address <em>*</em></label><input class="finput" type="email" id="me-email" name="email" required placeholder="you@publication.com"><span class="ferr" id="e-me-email">Please enter a valid email address.</span></div>
          </div>
          <div class="fg">
            <div class="fgroup"><label class="flabel" for="me-request">Your request <em>*</em></label><textarea class="ftarea" id="me-request" name="request" required placeholder="Describe your editorial request and deadline if applicable."></textarea><span class="ferr" id="e-me-request">Please describe your request.</span></div>
          </div>
        </div>
        <div class="ff">
          <button class="fsubmit" type="submit"><svg width="15" height="15" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 2L9 11M18 2L13 18l-4-7-7-4 16-5z"/></svg> Send media enquiry</button>
          <div class="flegend">By submitting you accept our <a href="/{{ $locale }}/pages/privacy-policy">Privacy Policy</a>.</div>
        </div>
      </form>
    </div>

    {{-- OTHER --}}
    <div class="fc" id="fc-other">
      <div class="fh">
        <div class="fh-left"><div class="fh-title">Other Enquiry</div><div class="fh-sub">If your enquiry does not fit a specific category, send us a message here. We respond within one business day.</div></div>
        <button class="fh-close" onclick="gtCloseForm()" aria-label="Close">✕</button>
      </div>
      <form id="gtf-other" novalidate>
        @csrf
        <input type="hidden" name="form_type" value="other">
        <div class="success-banner" id="sb-other"><div class="sb-icon">✅</div><div><div class="sb-title">Message received.</div><div class="sb-text">We will respond within one business day.</div></div></div>
        <div class="error-banner" id="eb-other">Something went wrong. Please try again.</div>
        <div class="fb">
          <div class="fg g2">
            <div class="fgroup"><label class="flabel" for="ot-name">Full name <em>*</em></label><input class="finput" type="text" id="ot-name" name="name" required placeholder="Your name"><span class="ferr" id="e-ot-name">Please enter your name.</span></div>
            <div class="fgroup"><label class="flabel" for="ot-email">Email address <em>*</em></label><input class="finput" type="email" id="ot-email" name="email" required placeholder="you@company.com"><span class="ferr" id="e-ot-email">Please enter a valid email address.</span></div>
          </div>
          <div class="fg">
            <div class="fgroup"><label class="flabel" for="ot-subject">Subject <em>*</em></label><input class="finput" type="text" id="ot-subject" name="subject" required placeholder="Brief subject of your message"><span class="ferr" id="e-ot-subject">Please enter a subject.</span></div>
          </div>
          <div class="fg">
            <div class="fgroup"><label class="flabel" for="ot-message">Message <em>*</em></label><textarea class="ftarea" id="ot-message" name="message" required placeholder="How can we help?"></textarea><span class="ferr" id="e-ot-message">Please enter your message.</span></div>
          </div>
        </div>
        <div class="ff">
          <button class="fsubmit" type="submit"><svg width="15" height="15" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 2L9 11M18 2L13 18l-4-7-7-4 16-5z"/></svg> Send message</button>
          <div class="flegend">By submitting you accept our <a href="/{{ $locale }}/pages/privacy-policy">Privacy Policy</a>.</div>
        </div>
      </form>
    </div>

  </div>{{-- /form-panel --}}

  {{-- Offices --}}
  <section class="offices-sec">
    <div class="offices-inner">
      <div class="sec-label">Our locations</div>
      <div class="sec-h2">Istanbul, Türkiye</div>
      <div class="offices-grid">
        <div class="ocard">
          <div class="o-num">Headquarters</div>
          <div class="o-name">Varyap Meridian — Ataşehir</div>
          <div class="o-row"><span class="o-row-icon">📍</span><span>Al Zambak Sokağı, Varyap Meridian A Blok<br>34746 Ataşehir / İstanbul, Türkiye</span></div>
          <div class="o-row"><span class="o-row-icon">☎</span><a href="tel:+902166292029" style="color:#0A0E1A">+90 216 629 20 29</a></div>
          <div class="o-row"><span class="o-row-icon">✉</span><a href="mailto:info@globaltrding.com" style="color:#0A0E1A">info@globaltrding.com</a></div>
          <a class="o-map" href="https://maps.google.com/?q=Varyap+Meridian+A+Blok+Atasehir+Istanbul" target="_blank" rel="noopener">↗ Open in Google Maps</a>
        </div>
        <div class="hours-card">
          <div class="card-title">Business hours</div>
          <div class="h-row"><span class="h-day">Monday – Friday</span><div style="display:flex;align-items:center;gap:.5rem"><span class="h-val">09:00 – 18:00</span><span class="h-tag">TRT</span></div></div>
          <div class="h-row"><span class="h-day">Saturday</span><span class="h-val">By arrangement</span></div>
          <div class="h-row"><span class="h-day">Sunday</span><span class="h-val">Closed</span></div>
          <div class="h-row" style="background:rgba(200,169,110,.04)"><span class="h-day" style="font-weight:600;color:#0A0E1A">RFQ inbox</span><span class="h-tag">24h response</span></div>
        </div>
        <div class="langs-card">
          <div class="card-title">We communicate in</div>
          <div class="l-row"><div class="l-flag">🇬🇧</div><div class="l-name">English</div><div class="l-native">Primary</div></div>
          <div class="l-row"><div class="l-flag">🇹🇷</div><div class="l-name">Turkish</div><div class="l-native">Türkçe</div></div>
          <div class="l-row"><div class="l-flag">🇸🇦</div><div class="l-name">Arabic</div><div class="l-native">العربية</div></div>
          <div class="l-row"><div class="l-flag">🇫🇷</div><div class="l-name">French</div><div class="l-native">Français</div></div>
        </div>
      </div>
    </div>
  </section>

</div>{{-- /gt-contact --}}

<script>
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
    if (btn) { btn.disabled = true; btn.textContent = 'Sending…'; }

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