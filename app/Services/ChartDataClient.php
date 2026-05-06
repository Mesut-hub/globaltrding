<?php
// app/Services/ChartDataClient.php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ChartDataClient
{
    public function fromUrl(string $url, int $cacheSeconds = 900): array
    {
        return Cache::remember('chart_url:' . sha1($url), $cacheSeconds, function () use ($url) {
            $res = Http::timeout(10)->acceptJson()->get($url);
            $res->throw();
            $json = $res->json();

            // Accept either: [1,2,3] or [{value:1},{value:2}]
            if (is_array($json)) {
                $out = [];
                foreach ($json as $row) {
                    if (is_numeric($row)) $out[] = ['value' => (float) $row];
                    elseif (is_array($row) && isset($row['value']) && is_numeric($row['value'])) $out[] = ['value' => (float) $row['value']];
                }
                return $out;
            }

            return [];
        });
    }
}