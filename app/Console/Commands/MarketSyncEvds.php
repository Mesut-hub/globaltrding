<?php

namespace App\Console\Commands;

use App\Models\MarketInstrument;
use App\Models\MarketPoint;
use App\Services\EvdsClient;
use Carbon\Carbon;
use Illuminate\Console\Command;

class MarketSyncEvds extends Command
{
    protected $signature = 'market:sync-evds {--days=365} {--force : Re-sync even if points exist} {--debug : Print EVDS request debug (safe)}';
    protected $description = 'Sync Market instruments data from TCMB EVDS into market_points';

    public function handle(EvdsClient $evds): int
    {
        if (! $evds->hasKey()) {
            $this->warn('EVDS_API_KEY is empty. Skipping sync.');
            return self::SUCCESS;
        }

        $days = max(1, (int) $this->option('days'));

        $end = now()->startOfDay();
        $start = $end->copy()->subDays($days);

        $startStr = $start->format('Y-m-d');
        $endStr = $end->format('Y-m-d');

        $instruments = MarketInstrument::query()
            ->where('is_active', true)
            ->whereNotNull('evds_series')
            ->orderBy('sort_order')
            ->get();

        foreach ($instruments as $inst) {
            $series = trim((string) $inst->evds_series);
            if ($series === '') continue;

            $this->info("Syncing {$inst->slug} ({$series}) from {$startStr} to {$endStr}");

            // Skip if already has points (unless forced)
            if (! $this->option('force')) {
                $hasAny = MarketPoint::query()
                    ->where('market_instrument_id', $inst->id)
                    ->exists();

                if ($hasAny) {
                    $this->line('  - skipped (already has points). Use --force to re-sync.');
                    continue;
                }
            }

            try {
                $payload = $evds->fetchSeries($series, $startStr, $endStr, (bool) $this->option('debug'));

                // EVDS canonical: { "items": [ { "Tarih": "...", "TP_DK_USD_A": "..." }, ... ] }
                $items = $payload['items'] ?? $payload['Items'] ?? null;

                if (! is_array($items)) {
                    throw new \RuntimeException('EVDS payload missing "items". Payload keys: ' . implode(', ', array_keys($payload)));
                }

                $valueKey = str_replace('.', '_', $series);

                $upsert = [];
                foreach ($items as $row) {
                    if (! is_array($row)) continue;

                    $dateRaw = $row['Tarih'] ?? $row['tarih'] ?? $row['DATE'] ?? $row['date'] ?? null;
                    if (! is_string($dateRaw) || trim($dateRaw) === '') continue;

                    $date = Carbon::parse($dateRaw)->startOfDay();

                    $rawVal = $row[$valueKey] ?? null;
                    if ($rawVal === null) continue;

                    // normalize numbers like "32,1234"
                    if (is_string($rawVal)) {
                        $rawVal = str_replace(',', '.', trim($rawVal));
                    }
                    if (! is_numeric($rawVal)) continue;

                    $upsert[] = [
                        'market_instrument_id' => $inst->id,
                        'date' => $date->toDateString(),
                        'value' => (float) $rawVal,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                if (empty($upsert)) {
                    throw new \RuntimeException("No points parsed. Expected value key: {$valueKey}");
                }

                MarketPoint::query()->upsert(
                    $upsert,
                    ['market_instrument_id', 'date'],
                    ['value', 'updated_at']
                );

                $this->info('  - upserted ' . count($upsert) . ' points.');
            } catch (\Throwable $e) {
                $this->error('  - FAILED: ' . $e->getMessage());
                $this->line('    base_url=' . config('services.evds.base_url'));
                $this->line('    series=' . $series);
            }
        }

        $this->info('Done.');
        return self::SUCCESS;
    }
}