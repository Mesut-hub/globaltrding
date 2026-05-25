<?php

namespace App\Filament\Resources\CookieConsent\Pages;

use App\Filament\Resources\CookieConsent\CookieConsentLogResource;
use Filament\Resources\Pages\ListRecords;

class ListCookieConsentLogs extends ListRecords
{
    protected static string $resource = CookieConsentLogResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}