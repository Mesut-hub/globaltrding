<?php

namespace App\Console\Commands;

use App\Models\MarketInstrument;
use App\Models\MarketPoint;
use App\Services\Evds3Client;
use Carbon\Carbon;
use Illuminate\Console\Command;

class MarketSyncEvds3Latest extends Command
{
    protected $signature = 'market:sync-evds3-latest {--force}';
    protected $description = 'Sync latest belt instrument values from EVDS3 XML (sk-seriler) into market_points';

    public function handle(Evds3Client $client): int
    {
        $this->info('Fetching EVDS3 sk-seriler ...');

        $items = $client->fetchSkSeriler();

        // Build lookup by series code
        $bySeries = [];
        foreach ($items as $row) {
            if (!empty($row['seriKodu'])) {
                $bySeries[$row['seriKodu']] = $row;
            }
        }

        // Instruments that have an evds_series value
        $instruments = MarketInstrument::query()
            ->where('is_active', true)
            ->whereNotNull('evds_series')
            ->get();

        foreach ($instruments as $inst) {
            $series = trim((string) $inst->evds_series);
            if ($series === '') continue;

            // EVDS3 sk-seriler uses different codes for some series than your old EVDS codes.
            // Example you showed:
            // USD effective buying: TP.DK.USD.A.EF.YTL
            //
            // So: try direct match first.
            $hit = $bySeries[$series] ?? null;

            if (! $hit) {
                $this->warn("No EVDS3 sk-seriler match for {$inst->slug} series={$series}");
                continue;
            }

            $rawVal = $hit['deger'];
            $rawVal = str_replace(',', '.', trim((string) $rawVal));
            if ($rawVal === '' || !is_numeric($rawVal)) {
                $this->warn("Non-numeric value for {$inst->slug} series={$series}: {$hit['deger']}");
                continue;
            }

            $dateRaw = trim((string) $hit['tarih']); // expected dd-mm-YYYY
            $date = Carbon::createFromFormat('d-m-Y', $dateRaw)->startOfDay();

            MarketPoint::query()->upsert([
                [
                    'market_instrument_id' => $inst->id,
                    'date' => $date->toDateString(),
                    'value' => (float) $rawVal,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ], ['market_instrument_id', 'date'], ['value', 'updated_at']);

            $this->info("Upserted {$inst->slug} = {$rawVal} ({$date->toDateString()})");
        }

        $this->info('Done.');
        return self::SUCCESS;
    }
}