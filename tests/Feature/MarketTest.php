<?php

namespace Tests\Feature;

use App\Models\MarketInstrument;
use App\Models\MarketPoint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketTest extends TestCase
{
    use RefreshDatabase;

    public function test_market_index_renders_successfully(): void
    {
        MarketInstrument::query()->create([
            'slug' => 'usd-try',
            'name' => ['en' => 'USD/TRY'],
            'evds_series' => null,
            'unit' => 'TRY',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $response = $this->get('/en/market');

        $response->assertOk();
        $response->assertSee('Market Intelligence');
    }

    public function test_market_data_returns_chart_points_for_selected_instrument(): void
    {
        $instrument = MarketInstrument::query()->create([
            'slug' => 'usd-try',
            'name' => ['en' => 'USD/TRY'],
            'evds_series' => null,
            'unit' => 'TRY',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        MarketPoint::query()->create([
            'market_instrument_id' => $instrument->id,
            'date' => now()->subDays(2)->toDateString(),
            'value' => 31.5000,
        ]);

        MarketPoint::query()->create([
            'market_instrument_id' => $instrument->id,
            'date' => now()->subDay()->toDateString(),
            'value' => 31.8000,
        ]);

        $response = $this->get('/en/market/data?instrument=usd-try&period=1m');

        $response->assertOk();
        $response->assertJsonPath('instrument.slug', 'usd-try');
        $response->assertJsonCount(2, 'points');
    }

    public function test_market_data_returns_latest_values_for_multiple_instruments(): void
    {
        $usd = MarketInstrument::query()->create([
            'slug' => 'usd-try',
            'name' => ['en' => 'USD/TRY'],
            'evds_series' => null,
            'unit' => 'TRY',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $eur = MarketInstrument::query()->create([
            'slug' => 'eur-try',
            'name' => ['en' => 'EUR/TRY'],
            'evds_series' => null,
            'unit' => 'TRY',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        MarketPoint::query()->create([
            'market_instrument_id' => $usd->id,
            'date' => now()->toDateString(),
            'value' => 31.6500,
        ]);

        MarketPoint::query()->create([
            'market_instrument_id' => $eur->id,
            'date' => now()->toDateString(),
            'value' => 34.2500,
        ]);

        $response = $this->get('/en/market/data?instruments=usd-try,eur-try');

        $response->assertOk();
        $response->assertJsonPath('usd-try.slug', 'usd-try');
        $response->assertJsonPath('eur-try.slug', 'eur-try');
    }
}

