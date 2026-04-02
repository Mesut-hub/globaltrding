<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class EvdsClient
{
    public function http(): PendingRequest
    {
        // Must be the EVDS service root
        $baseUrl = rtrim((string) config('services.evds.base_url'), '/') . '/';

        return Http::baseUrl($baseUrl)
            ->timeout(30)
            ->withHeaders(['Accept' => 'application/json']);
    }

    public function hasKey(): bool
    {
        return filled(config('services.evds.key'));
    }

    /**
     * EVDS "service" expects parameters embedded in the path, not as query string.
     *
     * Example:
     * /series=TP.DK.USD.A&startDate=2026-03-03&endDate=2026-04-02&type=json&key=XXXX
     */
    public function fetchSeries(string $series, string $startDate, string $endDate): array
    {
        $key = (string) config('services.evds.key');
        if ($key === '') {
            throw new RuntimeException('EVDS_API_KEY is missing in .env');
        }

        $path = 'series=' . rawurlencode($series)
            . '&startDate=' . rawurlencode($startDate)
            . '&endDate=' . rawurlencode($endDate)
            . '&type=json'
            . '&key=' . rawurlencode($key);

        $response = $this->http()->get($path);

        $body = (string) $response->body();
        $contentType = (string) $response->header('content-type');

        if (! str_contains(strtolower($contentType), 'application/json')) {
            throw new RuntimeException(
                "EVDS did not return JSON. Status={$response->status()} Content-Type={$contentType}. Body head: " .
                mb_substr($body, 0, 200)
            );
        }

        $json = $response->json();
        if (! is_array($json)) {
            throw new RuntimeException(
                "EVDS JSON parse failed. Status={$response->status()}. Body head: " . mb_substr($body, 0, 200)
            );
        }

        return $json;
    }
}