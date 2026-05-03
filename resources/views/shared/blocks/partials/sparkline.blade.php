@php
  $points = $points ?? [];
  $mode = $mode ?? 'absolute'; // absolute|percent
  $scale = $scale ?? 'linear'; // linear|log
  $auto = (bool)($auto ?? true);
  $minFixed = $minFixed ?? null;
  $maxFixed = $maxFixed ?? null;

  // Normalize input to floats
  $vals = collect($points)
    ->map(fn($p) => is_array($p) ? ($p['value'] ?? null) : $p)
    ->filter(fn($v) => $v !== null && $v !== '' && is_numeric($v))
    ->map(fn($v) => (float)$v)
    ->values();

  if ($vals->count() < 2) { $vals = collect([0, 0]); }

  // Apply log if requested (only if positive)
  if ($scale === 'log') {
    $vals = $vals->map(fn($v) => $v > 0 ? log10($v) : 0.0);
  }

  $min = $auto ? $vals->min() : (is_numeric($minFixed) ? (float)$minFixed : $vals->min());
  $max = $auto ? $vals->max() : (is_numeric($maxFixed) ? (float)$maxFixed : $vals->max());

  if ($max - $min == 0) { $max = $min + 1; }

  // Percent mode maps to 0..100
  $norm = $vals->map(function($v) use ($min,$max,$mode) {
    $p = ($v - $min) / ($max - $min);
    $p = max(0, min(1, $p));
    return $mode === 'percent' ? $p * 100 : $p; // in percent mode we'll still use 0..100 for y mapping below
  });

  $w = 120; $h = 36; $pad = 2;
  $count = $norm->count();
  $dx = ($w - $pad*2) / max(1, $count - 1);

  $toY = function($p) use ($h,$pad,$mode) {
    // percent: p is 0..100 -> convert to 0..1
    $pp = $mode === 'percent' ? ($p / 100) : $p;
    return ($h - $pad) - $pp * ($h - $pad*2);
  };

  $d = '';
  foreach ($norm as $i => $p) {
    $x = $pad + $dx * $i;
    $y = $toY($p);
    $d .= ($i === 0 ? 'M' : 'L') . round($x,2) . ' ' . round($y,2) . ' ';
  }
@endphp

<svg viewBox="0 0 {{ $w }} {{ $h }}" class="block" width="{{ $w }}" height="{{ $h }}" aria-hidden="true">
  <path d="{{ trim($d) }}" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" opacity="0.95"/>
</svg>