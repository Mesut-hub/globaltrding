<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class EvdsClient
{
    public function http(string $baseUrl): PendingRequest
    {
        $baseUrl = rtrim($baseUrl, '/') . '/';

        return Http::baseUrl($baseUrl)
            ->timeout(30)
            ->withHeaders([
                'Accept' => 'application/json',
                'X-Requested-With' => 'XMLHttpRequest',
            ]);
    }

    public function hasKey(): bool
    {
        return filled(config('services.evds.key'));
    }

    /**
     * Try several EVDS endpoint shapes.
     * Returns decoded JSON array.
     */
    public function fetchSeries(string $series, string $startDate, string $endDate, bool $debug = false): array
    {
        $key = (string) config('services.evds.key');
        if ($key === '') {
            throw new RuntimeException('EVDS_API_KEY is missing in .env');
        }

        $configuredBase = (string) config('services.evds.base_url');
        $fallbackBase = 'https://evds2.tcmb.gov.tr/service/evds/';

        // Endpoints to try (base + relative path)
        // NOTE: We intentionally try "/api/" because some EVDS3 setups expose JSON there.
        $candidates = [
            [$configuredBase, 'api/'],
            [$configuredBase, 'service/evds/'],
            [$configuredBase, ''],
            [$fallbackBase,  ''], // fallback to evds2 if evds3 serves SPA html
        ];

        $params = [
            'series' => $series,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'type' => 'json',
            'key' => $key,
        ];

        $lastError = null;

        foreach ($candidates as [$baseUrl, $path]) {
            // Query-string style
            $urlForLog = rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
            if ($debug) {
                $safe = $params;
                $safe['key'] = '***';
                fwrite(STDERR, "[EVDS DEBUG] TRY GET {$urlForLog} ?" . http_build_query($safe) . PHP_EOL);
            }

            $res = $this->http($baseUrl)->get($path, $params);

            $json = $this->tryParseJsonResponse($res, $debug);
            if ($json !== null) return $json;

            // Path style (legacy)
            $pathStyle = 'series=' . rawurlencode($series)
                . '&startDate=' . rawurlencode($startDate)
                . '&endDate=' . rawurlencode($endDate)
                . '&type=json'
                . '&key=' . rawurlencode($key);

            $urlForLog2 = rtrim($baseUrl, '/') . '/' . ltrim($path, '/') . $pathStyle;
            if ($debug) {
                // redact key in log
                $urlForLog2Safe = preg_replace('/key=[^&]+/', 'key=***', $urlForLog2);
                fwrite(STDERR, "[EVDS DEBUG] TRY GET {$urlForLog2Safe}" . PHP_EOL);
            }

            $res2 = $this->http($baseUrl)->get(rtrim($path, '/') . '/' . $pathStyle);

            $json2 = $this->tryParseJsonResponse($res2, $debug);
            if ($json2 !== null) return $json2;

            $lastError = "Base={$baseUrl} Path={$path} Status={$res->status()} CT={$res->header('content-type')}";
        }

        throw new RuntimeException(
            "EVDS did not return JSON from any endpoint. Last: {$lastError}. " .
            "Tip: run `php artisan market:sync-evds --days=7 --force --debug` and paste the debug output."
        );
    }

    private function tryParseJsonResponse($response, bool $debug): ?array
    {
        $contentType = (string) $response->header('content-type');
        $body = (string) $response->body();

        // Detect SPA HTML quickly
        if (str_contains($body, '<!DOCTYPE html>') || str_contains($body, '<html')) {
            if ($debug) {
                fwrite(STDERR, "[EVDS DEBUG] Response looks like HTML (SPA). CT={$contentType} HEAD=" . mb_substr($body, 0, 120) . PHP_EOL);
            }
            return null;
        }

        $looksJson = str_contains(strtolower($contentType), 'application/json')
            || (strlen($body) > 0 && ($body[0] === '{' || $body[0] === '['));

        if (! $looksJson) {
            if ($debug) {
                fwrite(STDERR, "[EVDS DEBUG] Not JSON. CT={$contentType} HEAD=" . mb_substr($body, 0, 120) . PHP_EOL);
            }
            return null;
        }

        $json = $response->json();
        if (! is_array($json)) {
            if ($debug) {
                fwrite(STDERR, "[EVDS DEBUG] JSON parse failed. HEAD=" . mb_substr($body, 0, 120) . PHP_EOL);
            }
            return null;
        }

        return $json;
    }
}