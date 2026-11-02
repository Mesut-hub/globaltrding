<!DOCTYPE html>
<html><head><meta charset="UTF-8">
<style>
  body{font-family:'Segoe UI',sans-serif;color:#111827;background:#fff;margin:0;padding:0}
  .wrap{max-width:600px;margin:0 auto;padding:32px 24px}
  .header{background:#0A0E1A;padding:20px 24px;border-radius:6px 6px 0 0}
  .header-title{color:#C8A96E;font-size:13px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;font-family:monospace;margin:0}
  .body{background:#F8F7F5;border:1px solid #E5E2DC;border-top:none;padding:24px;border-radius:0 0 6px 6px}
  .row{margin-bottom:16px}
  .label{font-size:11px;font-weight:700;color:#9CA3AF;letter-spacing:.1em;text-transform:uppercase;font-family:monospace;margin-bottom:4px}
  .value{font-size:14px;color:#111827;line-height:1.6;white-space:pre-wrap;word-break:break-word;background:#fff;border:1px solid #E5E2DC;border-radius:4px;padding:10px 12px}
  .spec-value{font-size:14px;color:#111827;line-height:1.7;white-space:pre-wrap;word-break:break-word;background:#fff;border:1px solid #E5E2DC;border-radius:4px;padding:12px;min-height:60px}
  .footer{margin-top:24px;font-size:12px;color:#9CA3AF;line-height:1.7;border-top:1px solid #E5E2DC;padding-top:16px}
</style></head>
<body>
<div class="wrap">
  <div class="header">
    <p class="header-title">New {{ strtoupper(str_replace('_',' ',$data['form_type'] ?? 'enquiry')) }} — Global Trading Contact System</p>
  </div>
  <div class="body">
    @foreach($data as $key => $val)
      @if(!in_array($key,['_token','form_type']) && $val)
      <div class="row">
        <div class="label">{{ str_replace('_',' ',ucfirst($key)) }}</div>
        <div class="{{ in_array($key,['specification','message','question','request']) ? 'spec-value' : 'value' }}">{{ $val }}</div>
      </div>
      @endif
    @endforeach
    <div class="footer">
      Submitted {{ now()->format('d M Y \a\t H:i') }} TRT · globaltrding.com contact system<br>
      Reply directly to this email to respond to {{ $data['name'] ?? 'the submitter' }}.
    </div>
  </div>
</div>
</body></html>