@php
  $points = $points ?? [];
  $mode = $mode ?? 'absolute'; // absolute|percent
  $scale = $scale ?? 'linear'; // linear|log
  $auto = (bool)($auto ?? true);
  $minFixed = $minFixed ?? null;
  $maxFixed = $maxFixed ?? null;

  // Optional display toggles
  $showGrid = (bool)($showGrid ?? true);
  $showAxes = (bool)($showAxes ?? true);
  $gridLines = is_numeric($gridLines ?? null) ? max(2, (int)$gridLines) : 4;

  // Optional x labels (strings); if omitted we won’t show x labels
  $xMinLabel = $xMinLabel ?? null;
  $xMaxLabel = $xMaxLabel ?? null;

  // Normalize input to floats (supports array points with value)
  $vals = collect($points)
    ->map(fn($p) => is_array($p) ? ($p['value'] ?? ($p['y'] ?? null)) : $p)
    ->filter(fn($v) => $v !== null && $v !== '' && is_numeric($v))
    ->map(fn($v) => (float)$v)
    ->values();

  if ($vals->count() < 2) { $vals = collect([0, 0]); }

  $rawMin = $vals->min();
  $rawMax = $vals->max();

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
    return $mode === 'percent' ? $p * 100 : $p;
  });

  // Layout
  $w = 180; $h = 54;
  $padL = $showAxes ? 28 : 4; // space for y labels
  $padR = 4;
  $padT = 4;
  $padB = ($showAxes && ($xMinLabel || $xMaxLabel)) ? 14 : 4;

  $count = $norm->count();
  $dx = ($w - $padL - $padR) / max(1, $count - 1);

  $toY = function($p) use ($h,$padT,$padB,$mode) {
    $pp = $mode === 'percent' ? ($p / 100) : $p; // 0..1
    return ($h - $padB) - $pp * ($h - $padT - $padB);
  };

  // Path
  $d = '';
  foreach ($norm as $i => $p) {
    $x = $padL + $dx * $i;
    $y = $toY($p);
    $d .= ($i === 0 ? 'M' : 'L') . round($x,2) . ' ' . round($y,2) . ' ';
  }

  // Axis label values (use raw values for friendliness, not log-transformed)
  $yMinLabel = $mode === 'percent' ? '0' : rtrim(rtrim(number_format((float)$rawMin, 2, '.', ''), '0'), '.');
  $yMaxLabel = $mode === 'percent' ? '100' : rtrim(rtrim(number_format((float)$rawMax, 2, '.', ''), '0'), '.');
@endphp

<svg viewBox="0 0 {{ $w }} {{ $h }}" class="block w-full" aria-hidden="true">
  @if ($showGrid)
    @for ($i = 0; $i <= $gridLines; $i++)
      @php
        $p = $i / $gridLines; // 0..1
        $y = ($padT) + $p * ($h - $padT - $padB);
      @endphp
      <line x1="{{ $padL }}" y1="{{ $y }}" x2="{{ $w - $padR }}" y2="{{ $y }}"
            stroke="currentColor" opacity="0.12" stroke-width="1"/>
    @endfor
  @endif

  @if ($showAxes)
    {{-- Y axis line --}}
    <line x1="{{ $padL }}" y1="{{ $padT }}" x2="{{ $padL }}" y2="{{ $h - $padB }}"
          stroke="currentColor" opacity="0.18" stroke-width="1"/>

    {{-- Y labels --}}
    <text x="2" y="{{ $padT + 9 }}" font-size="9" fill="currentColor" opacity="0.65">{{ $yMaxLabel }}</text>
    <text x="2" y="{{ $h - $padB }}" font-size="9" fill="currentColor" opacity="0.65">{{ $yMinLabel }}</text>

    {{-- X labels (optional) --}}
    @if ($xMinLabel)
      <text x="{{ $padL }}" y="{{ $h - 2 }}" font-size="9" fill="currentColor" opacity="0.65">{{ $xMinLabel }}</text>
    @endif
    @if ($xMaxLabel)
      <text x="{{ $w - $padR - 1 }}" y="{{ $h - 2 }}" font-size="9" fill="currentColor" opacity="0.65" text-anchor="end">{{ $xMaxLabel }}</text>
    @endif
  @endif

  <path d="{{ trim($d) }}" fill="none" stroke="currentColor" stroke-width="2"
        stroke-linecap="round" stroke-linejoin="round" opacity="0.95"/>
</svg>