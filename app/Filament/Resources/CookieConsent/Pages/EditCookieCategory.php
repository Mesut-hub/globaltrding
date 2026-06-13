<?php

namespace App\Filament\Resources\CookieConsent\Pages;

use App\Filament\Resources\CookieConsent\CookieCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCookieCategory extends EditRecord
{
    protected static string $resource = CookieCategoryResource::class;
    protected function getHeaderActions(): array { return [DeleteAction::make()]; }
}