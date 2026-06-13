<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class EvdsClient
{
    public function http(): PendingRequest
    {
        $baseUrl = rtrim((string) config('services.evds.base_url'), '/') . '/';
        $key = (string) config('services.evds.key');

        return Http::baseUrl($baseUrl)
            ->timeout(60)
            ->withHeaders([
                'Accept' => 'application/json',
                'key' => $key, // EVDS requires key in header
            ]);
    }

    public function hasKey(): bool
    {
        return filled(config('services.evds.key'));
    }

    /**
     * EVDS3 format:
     * https://evds3.tcmb.gov.tr/igmevdsms-dis/series=TP.DK.USD.S.YTL&startDate=01-01-2024&endDate=31-12-2024&type=json
     * Header: key: API_KEY
     */
    public function fetchSeries(string $series, string $startDate, string $endDate): array
    {
        $key = (string) config('services.evds.key');
        if ($key === '') {
            throw new RuntimeException('EVDS_API_KEY is missing in .env');
        }

        // EVDS expects DD-MM-YYYY
        $start = Carbon::parse($startDate)->format('d-m-Y');
        $end = Carbon::parse($endDate)->format('d-m-Y');

        // IMPORTANT: parameters are in the PATH (not query string)
        $path = 'series=' . rawurlencode($series)
            . '&startDate=' . rawurlencode($start)
            . '&endDate=' . rawurlencode($end)
            . '&type=json';

        $response = $this->http()->get($path);
        $response->throw();

        $json = $response->json();
        if (! is_array($json)) {
            $body = (string) $response->body();
            throw new RuntimeException(
                "EVDS JSON parse failed. Status={$response->status()}. Body head: " . mb_substr($body, 0, 250)
            );
        }

        return $json;
    }
}