<?php

namespace App\Filament\Resources\CookieConsent\Pages;

use App\Filament\Resources\CookieConsent\CookieCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCookieCategories extends ListRecords
{
    protected static string $resource = CookieCategoryResource::class;
    protected function getHeaderActions(): array { return [CreateAction::make()]; }
}