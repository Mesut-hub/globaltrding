<?php

namespace App\Filament\Resources\CookieConsent\Pages;

use App\Filament\Resources\CookieConsent\CookieSettingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCookieSetting extends CreateRecord
{
    protected static string $resource = CookieSettingResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return static::resolveValueField($data);
    }

    public static function resolveValueField(array $data): array
    {
        $booleanKeys = ['show_reject_all', 'show_manage'];
        $key = $data['key'] ?? '';

        if (in_array($key, $booleanKeys, true)) {
            $data['value'] = (bool) ($data['value_bool'] ?? false);
        } elseif (!isset($data['value']) || !is_array($data['value'])) {
            // scalar — value_scalar overrides
            if (isset($data['value_scalar'])) {
                $data['value'] = $data['value_scalar'];
            }
        }

        unset($data['value_bool'], $data['value_scalar']);
        return $data;
    }
}