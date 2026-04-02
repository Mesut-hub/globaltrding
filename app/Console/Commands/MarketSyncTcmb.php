<?php

namespace App\Console\Commands;

use App\Models\MarketInstrument;
use App\Models\MarketPoint;
use App\Services\TcmbTodayXmlClient;
use Illuminate\Console\Command;

class MarketSyncTcmb extends Command
{
    protected $signature = 'market:sync-tcmb {--days=30} {--force : Re-sync even if points exist}';
    protected $description = 'Sync FX rates from TCMB today.xml into market_points (USD/TRY, EUR/TRY, GBP/TRY)';

    public function handle(TcmbTodayXmlClient $tcmb): int
    {
        $this->info('Fetching TCMB today.xml ...');

        $xml = $tcmb->fetch();
        $parsed = $tcmb->parseForexBuying($xml);

        $date = $parsed['date'];
        $rates = $parsed['rates'];

        // Map our slugs to TCMB currency codes
        $map = [
            'usd-try' => 'USD',
            'eur-try' => 'EUR',
            'gbp-try' => 'GBP',
        ];

        foreach ($map as $slug => $code) {
            $inst = MarketInstrument::query()
                ->where('slug', $slug)
                ->where('is_active', true)
                ->first();

            if (! $inst) {
                $this->warn("Instrument missing/disabled: {$slug}");
                continue;
            }

            if (! array_key_exists($code, $rates)) {
                $this->warn("Rate not found in today.xml: {$code}");
                continue;
            }

            $value = (float) $rates[$code];

            MarketPoint::query()->upsert([
                [
                    'market_instrument_id' => $inst->id,
                    'date' => $date->toDateString(),
                    'value' => $value,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ], ['market_instrument_id', 'date'], ['value', 'updated_at']);

            $this->info("Upserted {$slug} = {$value} ({$date->toDateString()})");
        }

        $this->warn('Gold and Brent are NOT in today.xml. We will add a second provider next.');
        $this->info('Done.');
        return self::SUCCESS;
    }
}