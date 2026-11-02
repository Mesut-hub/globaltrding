<!DOCTYPE html>
<html><head><meta charset="UTF-8">
<style>
  body{font-family:'Segoe UI',sans-serif;color:#111827;background:#fff;margin:0;padding:0}
  .wrap{max-width:600px;margin:0 auto;padding:32px 24px}
  .header{background:#0A0E1A;padding:24px;border-radius:6px 6px 0 0;text-align:center}
  .logo{color:#C8A96E;font-size:18px;font-family:Georgia,serif;font-weight:400}
  .body{background:#F8F7F5;border:1px solid #E5E2DC;border-top:none;padding:28px 24px;border-radius:0 0 6px 6px}
  h2{font-size:20px;font-weight:600;color:#0A0E1A;margin-bottom:12px;font-family:Georgia,serif;font-weight:400}
  p{font-size:14px;color:#4B5563;line-height:1.75;margin-bottom:12px}
  .cta{display:inline-block;padding:10px 20px;background:#0A0E1A;color:#fff;text-decoration:none;font-size:13px;font-weight:700;border-radius:4px;margin-top:8px}
  .footer{margin-top:24px;font-size:12px;color:#9CA3AF;text-align:center;line-height:1.7;border-top:1px solid #E5E2DC;padding-top:16px}
</style></head>
<body>
<div class="wrap">
  <div class="header">
    <div class="logo">Global Trading Ltd. Co.</div>
  </div>
  <div class="body">
    <h2>We have received your enquiry, {{ explode(' ',$name)[0] }}.</h2>
    @if($formType === 'rfq')
    <p>Our technical sourcing team will review your specification and respond with a competitive, itemised quotation — including unit price, lead time, Incoterm, and documentation list — within <strong>24 hours</strong>.</p>
    <p>If your requirement is urgent, please call us directly on <strong>+90 216 629 20 29</strong>.</p>
    @elseif($formType === 'partnership')
    <p>A senior member of our commercial team will review your partnership proposal and respond within <strong>2–3 business days</strong>.</p>
    @else
    <p>Our team will respond within <strong>one business day</strong> with the information you need.</p>
    @endif
    <p>You can also explore our product catalogue and technical resources at any time:</p>
    <a href="https://globaltrding.com/en/products" class="cta">Explore our products →</a>
    <div class="footer">
      Global Trading Ltd. Co. · Ataşehir, Istanbul 34746, Türkiye<br>
      +90 216 629 20 29 · globaltrding.com<br>
      This is an automated acknowledgement — please do not reply to this message.
    </div>
  </div>
</div>
</body></html>