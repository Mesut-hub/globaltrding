{{-- resources/views/mail/admin-request-notification.blade.php --}}
@php
  $lines = [];
  foreach ($payload as $k => $v) {
    if (is_array($v)) $v = json_encode($v, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    $lines[] = $k . ': ' . (string) $v;
  }
@endphp

New {{ ucfirst($type) }} request received.

@foreach($lines as $line)
- {{ $line }}
@endforeach