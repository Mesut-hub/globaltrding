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
                'name' => 'USD/TRY (Selling)',
                'unit' => 'TRY',
                'evds_series' => 'TP.DK.USD.A.EF.YTL',
                'sort_order' => 10,
                'is_active' => true,
            ],
            [
                'slug' => 'eur-try',
                'name' => 'EUR/TRY (Selling)',
                'unit' => 'TRY',
                'evds_series' => 'TP.DK.EUR.A.EF.YTL',
                'sort_order' => 20,
                'is_active' => true,
            ],
            [
                'slug' => 'gbp-try',
                'name' => 'GBP/TRY (Selling)',
                'unit' => 'TRY',
                'evds_series' => 'TP.DK.GBP.S.YTL',
                'sort_order' => 30,
                'is_active' => true,
            ],
            [
                'slug' => 'gold-gram-try',
                'name' => 'Gold (Gram, TRY)',
                'unit' => 'TRY',
                'evds_series' => 'TP.MK.KUL.YTL',
                'sort_order' => 40,
                'is_active' => true,
            ],
            [
                'slug' => 'gold-ounce-usd',
                'name' => 'Gold (Ounce, USD)',
                'unit' => 'USD',
                'evds_series' => null,
                'sort_order' => 50,
                'is_active' => true,
            ],
            [
                'slug' => 'brent-usd',
                'name' => 'Brent (EUBP)',
                'unit' => 'EUBP',
                'evds_series' => 'TP.BRENTPETROL.EUBP',
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