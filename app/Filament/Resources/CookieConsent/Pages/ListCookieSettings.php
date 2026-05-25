<?php

namespace App\Filament\Resources\CookieConsent\Pages;

use App\Filament\Resources\CookieConsent\CookieSettingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCookieSettings extends ListRecords
{
    protected static string $resource = CookieSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}