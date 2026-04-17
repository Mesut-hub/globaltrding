<?php

namespace App\Console\Commands;

use App\Services\EvdsClient;
use Illuminate\Console\Command;

class EvdsProbe extends Command
{
    protected $signature = 'evds:probe
        {series=TP.DK.USD.S.YTL}
        {--start=}
        {--end=}
        {--debug}';

    protected $description = 'Probe EVDS and print whether JSON is returned (debuggable).';

    public function handle(EvdsClient $evds): int
    {
        $start = $this->option('start') ?: now()->subDays(7)->format('Y-m-d');
        $end   = $this->option('end')   ?: now()->format('Y-m-d');

        $series = (string) $this->argument('series');

        try {
            $json = $evds->fetchSeries($series, $start, $end, (bool) $this->option('debug'));
            $keys = implode(', ', array_slice(array_keys($json), 0, 12));
            $this->info("OK: got JSON. Top keys: {$keys}");
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("FAIL: " . $e->getMessage());
            return self::FAILURE;
        }
    }
}