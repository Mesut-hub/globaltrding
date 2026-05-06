@php
  // Keep it clean and business-like
@endphp

{!! nl2br(e($reply->body)) !!}

<hr>
<div style="font-size: 12px; color: #666;">
  Globaltrding — Inquiry Department<br>
  Reply-to: {{ config('departments.inquiry.reply_to') }}
</div>