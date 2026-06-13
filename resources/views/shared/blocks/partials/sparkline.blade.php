@php
    /*
    |───────────────────────────────────────────────────────────────────────
    | Premium Enterprise Sparkline — SVG, gradient fill, trend colour,
    | last-point dot, optional date labels
    |───────────────────────────────────────────────────────────────────────
    */

    $points     = $points     ?? [];
    $mode       = $mode       ?? 'absolute';   // absolute | percent
    $scale      = $scale      ?? 'linear';     // linear | log
    $auto       = (bool)($auto       ?? true);
    $minFixed   = $minFixed   ?? null;
    $maxFixed   = $maxFixed   ?? null;
    $showGrid   = (bool)($showGrid   ?? true);
    $showAxes   = (bool)($showAxes   ?? true);
    $gridLines  = is_numeric($gridLines ?? null) ? max(2, (int)$gridLines) : 4;
    $premium    = (bool)($premium    ?? false);

    // ── Extract values & optional date labels ────────────────────────────
    $rawItems   = collect($points);
    $vals       = $rawItems
        ->map(fn($p) => is_array($p) ? ($p['value'] ?? null) : $p)
        ->filter(fn($v) => $v !== null && is_numeric($v))
        ->map(fn($v)  => (float) $v)
        ->values();

    $dateLabels = $rawItems
        ->map(fn($p) => is_array($p) ? ($p['date'] ?? null) : null)
        ->values();

    if ($vals->count() < 2) {
        $vals = collect([0.0, 0.0]);
    }

    $rawMin = $vals->min();
    $rawMax = $vals->max();

    // Apply log transform if requested
    if ($scale === 'log') {
        $vals = $vals->map(fn($v) => $v > 0 ? log10($v) : 0.0);
    }

    $min = $auto ? $vals->min() : (is_numeric($minFixed) ? (float)$minFixed : $vals->min());
    $max = $auto ? $vals->max() : (is_numeric($maxFixed) ? (float)$maxFixed : $vals->max());
    if (abs($max - $min) < 0.000001) { $max = $min + 1; }

    $norm = $vals->map(function($v) use ($min, $max, $mode) {
        $p = ($v - $min) / ($max - $min);
        $p = max(0.0, min(1.0, $p));
        return $mode === 'percent' ? $p * 100.0 : $p;
    });

    // ── Layout constants ─────────────────────────────────────────────────
    $w    = 210;
    $h    = 72;
    $padL = $showAxes ? 30 : 6;
    $padR = 6;
    $padT = 8;
    $hasXLabels = $showAxes && $dateLabels->filter()->count() >= 2;
    $padB = $hasXLabels ? 18 : 8;

    $count = $norm->count();
    $dx    = ($w - $padL - $padR) / max(1, $count - 1);

    $toY = function(float $p) use ($h, $padT, $padB, $mode): float {
        $pp = $mode === 'percent' ? ($p / 100.0) : $p;
        return ($h - $padB) - $pp * ($h - $padT - $padB);
    };

    // ── Build SVG paths ──────────────────────────────────────────────────
    $linePath = '';
    $xArr     = [];
    $yArr     = [];

    foreach ($norm as $i => $p) {
        $x    = round($padL + $dx * $i, 2);
        $y    = round($toY($p), 2);
        $xArr[] = $x;
        $yArr[] = $y;
        $linePath .= ($i === 0 ? 'M' : 'L') . "{$x} {$y} ";
    }

    // Closed area fill path
    $firstX  = round($xArr[0],                      2);
    $lastX   = round($xArr[count($xArr) - 1],        2);
    $bottomY = round($h - $padB,                     2);
    $fillPath = trim($linePath) . " L{$lastX} {$bottomY} L{$firstX} {$bottomY} Z";

    // ── Trend colour ─────────────────────────────────────────────────────
    $isUp         = $vals->last() >= $vals->first();
    $strokeColor  = $isUp ? 'rgba(34,197,94,0.95)'  : 'rgba(239,68,68,0.95)';
    $fillOpacity0 = $isUp ? '0.28' : '0.22';
    $fillOpacity1 = '0';
    $stopColor    = $isUp ? '#22c55e' : '#ef4444';

    // Unique gradient ID (no JS required, collisions negligible)
    $gid = 'sg' . abs(crc32(serialize($points) . $mode));

    // ── Axis labels ──────────────────────────────────────────────────────
    $fmt = fn(float $v): string =>
        rtrim(rtrim(number_format($v, 2, '.', ''), '0'), '.');

    $yMaxLabel = $mode === 'percent' ? '100'        : $fmt($rawMax);
    $yMinLabel = $mode === 'percent' ? '0'          : $fmt($rawMin);
    $firstDate = $dateLabels->first();
    $lastDate  = $dateLabels->last();

    // Last point coordinates
    $lx = count($xArr) ? round(end($xArr), 2) : 0;
    $ly = count($yArr) ? round(end($yArr), 2) : 0;
@endphp

<svg
    viewBox="0 0 {{ $w }} {{ $h }}"
    class="block w-full"
    aria-hidden="true"
    xmlns="http://www.w3.org/2000/svg"
    overflow="visible"
>
    <defs>
        <linearGradient id="{{ $gid }}" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0%"   stop-color="{{ $stopColor }}" stop-opacity="{{ $fillOpacity0 }}"/>
            <stop offset="100%" stop-color="{{ $stopColor }}" stop-opacity="{{ $fillOpacity1 }}"/>
        </linearGradient>
    </defs>

    {{-- Grid lines --}}
    @if ($showGrid)
        @for ($i = 0; $i <= $gridLines; $i++)
            @php $gy = round($padT + ($i / $gridLines) * ($h - $padT - $padB), 1); @endphp
            <line
                x1="{{ $padL }}" y1="{{ $gy }}"
                x2="{{ $w - $padR }}" y2="{{ $gy }}"
                stroke="currentColor"
                stroke-width="1"
                stroke-dasharray="{{ $premium ? '3 3' : '2 2' }}"
                opacity="0.12"
            />
        @endfor
    @endif

    {{-- Y-axis rule --}}
    @if ($showAxes)
        <line
            x1="{{ $padL }}" y1="{{ $padT }}"
            x2="{{ $padL }}" y2="{{ $h - $padB }}"
            stroke="currentColor" stroke-width="1" opacity="0.18"
        />

        {{-- Y labels --}}
        <text x="2"   y="{{ $padT + 7 }}"       font-size="8" fill="currentColor" opacity="0.55">{{ $yMaxLabel }}</text>
        <text x="2"   y="{{ $h - $padB - 1 }}"  font-size="8" fill="currentColor" opacity="0.55">{{ $yMinLabel }}</text>
    @endif

    {{-- Gradient area fill --}}
    <path d="{{ $fillPath }}" fill="url(#{{ $gid }})"/>

    {{-- Line --}}
    <path
        d="{{ trim($linePath) }}"
        fill="none"
        stroke="{{ $strokeColor }}"
        stroke-width="{{ $premium ? '2.2' : '1.8' }}"
        stroke-linecap="round"
        stroke-linejoin="round"
    />

    {{-- Last-point dot --}}
    @if ($premium)
        <circle cx="{{ $lx }}" cy="{{ $ly }}" r="3.2" fill="{{ $strokeColor }}" opacity="0.95"/>
        <circle cx="{{ $lx }}" cy="{{ $ly }}" r="5.5" fill="{{ $stopColor }}"   opacity="0.18"/>
    @endif

    {{-- X-axis date labels --}}
    @if ($hasXLabels && $firstDate && $lastDate && $firstDate !== $lastDate)
        <text x="{{ $padL }}"           y="{{ $h - 2 }}" font-size="8" fill="currentColor" opacity="0.48">{{ $firstDate }}</text>
        <text x="{{ $w - $padR }}"      y="{{ $h - 2 }}" font-size="8" fill="currentColor" opacity="0.48" text-anchor="end">{{ $lastDate }}</text>
    @endif
</svg>