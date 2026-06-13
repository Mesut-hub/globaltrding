<?php

namespace App\Filament\Resources\CookieConsent\Pages;

use App\Filament\Resources\CookieConsent\CookieCategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCookieCategory extends CreateRecord
{
    protected static string $resource = CookieCategoryResource::class;
}