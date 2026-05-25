<?php

namespace App\Filament\Resources\CookieConsent\Pages;

use App\Filament\Resources\CookieConsent\CookieSettingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCookieSetting extends CreateRecord
{
    protected static string $resource = CookieSettingResource::class;
}