<?php

namespace App\Http\Controllers;

use App\Models\MarketInstrument;
use App\Models\MarketPoint;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MarketController extends Controller
{
    public function index(Request $request, string $locale)
    {
        $instruments = MarketInstrument::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $selectedSlug = (string) $request->query('instrument', ($instruments->first()?->slug ?? ''));
        $selected = $instruments->firstWhere('slug', $selectedSlug) ?? $instruments->first();

        return view('market.index', [
            'locale' => $locale,
            'instruments' => $instruments,
            'selected' => $selected,
        ]);
    }

    public function data(Request $request, string $locale)
    {
        // 1) Belt mode: return latest values for multiple instruments
        $list = trim((string) $request->query('instruments', ''));
        if ($list !== '') {
            $slugs = collect(explode(',', $list))
                ->map(fn ($s) => trim($s))
                ->filter()
                ->values();

            $cacheKey = 'market.belt.latest:' . md5($slugs->implode(','));
            $payload = Cache::remember($cacheKey, 60, function () use ($slugs) {
                $instruments = MarketInstrument::query()
                    ->whereIn('slug', $slugs)
                    ->where('is_active', true)
                    ->get();

                $out = [];

                foreach ($instruments as $inst) {
                    $last = MarketPoint::query()
                        ->where('market_instrument_id', $inst->id)
                        ->orderByDesc('date')
                        ->first(['date', 'value']);

                    $out[$inst->slug] = [
                        'slug' => $inst->slug,
                        'name' => $inst->name,
                        'unit' => $inst->unit,
                        'date' => $last?->date?->toDateString(),
                        'value' => $last?->value === null ? null : (float) $last->value,
                    ];
                }

                return $out;
            });

            return response()->json($payload);
        }

        // 2) Chart mode: return points for a single instrument
        $slug = (string) $request->query('instrument', '');
        $period = (string) $request->query('period', '3m'); // 1m|3m|1y|custom
        $from = (string) $request->query('from', '');
        $to = (string) $request->query('to', '');

        $instrument = MarketInstrument::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        [$startDate, $endDate] = $this->resolvePeriod($period, $from, $to);

        $points = MarketPoint::query()
            ->where('market_instrument_id', $instrument->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->get(['date', 'value']);

        return response()->json([
            'instrument' => [
                'slug' => $instrument->slug,
                'unit' => $instrument->unit,
                'name' => $instrument->name,
            ],
            'range' => [
                'from' => $startDate->toDateString(),
                'to' => $endDate->toDateString(),
            ],
            'points' => $points->map(fn ($p) => [
                'date' => $p->date->toDateString(),
                'value' => $p->value === null ? null : (float) $p->value,
            ])->values(),
        ]);
    }

    private function resolvePeriod(string $period, string $from, string $to): array
    {
        $end = now()->startOfDay();
        $start = match ($period) {
            '1m' => $end->copy()->subMonth(),
            '3m' => $end->copy()->subMonths(3),
            '1y' => $end->copy()->subYear(),
            'custom' => $from !== '' && $to !== ''
                ? Carbon::parse($from)->startOfDay()
                : $end->copy()->subMonths(3),
            default => $end->copy()->subMonths(3),
        };

        if ($period === 'custom' && $to !== '') {
            $end = Carbon::parse($to)->startOfDay();
        }

        if ($start->greaterThan($end)) {
            [$start, $end] = [$end, $start];
        }

        return [$start, $end];
    }
}