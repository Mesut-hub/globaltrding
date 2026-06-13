<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use SimpleXMLElement;

class TcmbTodayXmlClient
{
    /**
     * TCMB daily FX rates XML (today.xml)
     * Example base: https://www.tcmb.gov.tr/kurlar/today.xml
     */
    public function fetch(): SimpleXMLElement
    {
        $url = config('services.tcmb.today_xml_url', 'https://www.tcmb.gov.tr/kurlar/today.xml');

        $res = Http::timeout(20)
            ->withHeaders(['Accept' => 'application/xml'])
            ->get($url);

        $res->throw();

        $body = (string) $res->body();

        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($body);

        if (! $xml instanceof SimpleXMLElement) {
            throw new RuntimeException('TCMB today.xml could not be parsed.');
        }

        return $xml;
    }

    /**
     * Returns ['date' => Carbon, 'rates' => ['USD' => 32.1, ...]]
     * Uses ForexBuying as requested.
     */
    public function parseForexBuying(SimpleXMLElement $xml): array
    {
        $dateAttr = (string) ($xml['Date'] ?? '');
        $date = $dateAttr !== '' ? Carbon::parse($dateAttr)->startOfDay() : now()->startOfDay();

        $rates = [];

        foreach ($xml->Currency as $c) {
            $code = (string) ($c['CurrencyCode'] ?? '');
            if ($code === '') continue;

            $buying = (string) ($c->ForexBuying ?? '');
            $buying = str_replace(',', '.', trim($buying));

            if ($buying !== '' && is_numeric($buying)) {
                $rates[$code] = (float) $buying;
            }
        }

        return [
            'date' => $date,
            'rates' => $rates,
        ];
    }
}