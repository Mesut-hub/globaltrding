<?php

namespace App\Filament\Concerns;

use App\Support\FilamentPermissions;

trait HasPermission
{
    protected static function permissionKey(): string
    {
        /** @var string|null $key */
        $key = static::$permissionKey ?? null;

        return $key ?: '';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return FilamentPermissions::allowed(auth()->user(), static::permissionKey());
    }

    public static function canViewAny(): bool
    {
        return FilamentPermissions::allowed(auth()->user(), static::permissionKey());
    }
}