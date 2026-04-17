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
        if ($key === '') {
            // still allow object creation; fetchSeries() will throw a nicer error
            $key = '';
        }

        return Http::baseUrl($baseUrl)
            ->timeout(30)
            ->withHeaders([
                'Accept' => 'application/json',
                // IMPORTANT: EVDS expects the API key in header (not ?key=)
                'key' => $key,
            ]);
    }

    public function hasKey(): bool
    {
        return filled(config('services.evds.key'));
    }

    /**
     * Fetch EVDS series data.
     *
     * EVDS expects "parameters embedded in the path", e.g.:
     *   /series=TP.DK.USD.S.YTL&startDate=10-04-2026&endDate=17-04-2026&type=json
     *
     * IMPORTANT:
     * - API key is sent via header: key: YOUR_KEY
     * - Dates should be dd-mm-YYYY (EVDS guide).
     */
    public function fetchSeries(string $series, string $startDate, string $endDate): array
    {
        $key = (string) config('services.evds.key');
        if ($key === '') {
            throw new RuntimeException('EVDS_API_KEY is missing in .env');
        }

        // Convert incoming Y-m-d -> d-m-Y (EVDS guide format)
        $start = Carbon::parse($startDate)->format('d-m-Y');
        $end = Carbon::parse($endDate)->format('d-m-Y');

        $path = 'series=' . rawurlencode($series)
            . '&startDate=' . rawurlencode($start)
            . '&endDate=' . rawurlencode($end)
            . '&type=json';

        $response = $this->http()->get($path);

        $body = (string) $response->body();
        $contentType = (string) $response->header('content-type');

        // Helpful failure if they return HTML (SPA)
        if (str_contains($body, '<!DOCTYPE html>') || str_contains($body, '<html')) {
            throw new RuntimeException(
                "EVDS returned HTML instead of JSON. This usually means auth/endpoint mismatch.\n" .
                "Status={$response->status()} Content-Type={$contentType}. Body head: " . mb_substr($body, 0, 200)
            );
        }

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