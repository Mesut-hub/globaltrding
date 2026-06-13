<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;
use SimpleXMLElement;

class Evds3Client
{
    public function fetchSkSeriler(): array
    {
        $url = 'https://evds3.tcmb.gov.tr/igmevdsms-dis/sk-seriler';

        $res = Http::timeout(25)
            ->withHeaders([
                'Accept' => 'application/xml,text/xml,*/*',
            ])
            ->get($url);

        $res->throw();

        $body = (string) $res->body();

        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($body);

        if (! $xml instanceof SimpleXMLElement) {
            throw new RuntimeException('EVDS3 sk-seriler could not be parsed as XML.');
        }

        // XML looks like: <List><item>...</item></List>
        $items = [];
        foreach ($xml->item as $item) {
            $items[] = [
                'seriKodu' => (string) ($item->seriKodu ?? ''),
                'deger' => (string) ($item->deger ?? ''),
                'tarih' => (string) ($item->tarih ?? ''), // dd-mm-YYYY in your example
                'gorunurAdi' => (string) ($item->gorunurAdi ?? ''),
                'birim' => (string) ($item->birim ?? ''),
            ];
        }

        return $items;
    }
}