<?php

namespace App\Filament\Resources\CookieConsent\Pages;

use App\Filament\Resources\CookieConsent\CookieSettingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCookieSetting extends EditRecord
{
    protected static string $resource = CookieSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return \App\Filament\Resources\CookieConsent\Pages\CreateCookieSetting::resolveValueField($data);
    }
}