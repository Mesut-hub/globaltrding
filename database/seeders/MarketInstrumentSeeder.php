<?php

namespace Database\Seeders;

use App\Models\MarketInstrument;
use Illuminate\Database\Seeder;

class MarketInstrumentSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            [
                'slug' => 'usd-try',
                'name' => 'USD/TRY (Buying)',
                'unit' => 'TRY',
                'evds_series' => 'TP.DK.USD.A',
                'sort_order' => 10,
                'is_active' => true,
            ],
            [
                'slug' => 'eur-try',
                'name' => 'EUR/TRY (Buying)',
                'unit' => 'TRY',
                'evds_series' => 'TP.DK.EUR.A',
                'sort_order' => 20,
                'is_active' => true,
            ],
            [
                'slug' => 'gbp-try',
                'name' => 'GBP/TRY (Buying)',
                'unit' => 'TRY',
                'evds_series' => 'TP.DK.GBP.A',
                'sort_order' => 30,
                'is_active' => true,
            ],
            [
                'slug' => 'gold-gram-try',
                'name' => 'Gold (Gram, TRY)',
                'unit' => 'TRY',
                'evds_series' => 'TP.DK.ALTIN.A',
                'sort_order' => 40,
                'is_active' => true,
            ],
            [
                'slug' => 'gold-ounce-usd',
                'name' => 'Gold (Ounce, USD)',
                'unit' => 'USD',
                'evds_series' => 'TP.ALTIN.ONS.USD',
                'sort_order' => 50,
                'is_active' => true,
            ],
            [
                'slug' => 'brent-usd',
                'name' => 'Brent (USD/Barrel)',
                'unit' => 'USD/BBL',
                'evds_series' => 'TP.BRENT.USD',
                'sort_order' => 60,
                'is_active' => true,
            ],
        ];

        foreach ($rows as $row) {
            MarketInstrument::query()->updateOrCreate(
                ['slug' => $row['slug']],
                $row,
            );
        }
    }
}