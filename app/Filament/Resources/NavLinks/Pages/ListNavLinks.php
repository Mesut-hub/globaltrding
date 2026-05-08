<?php

namespace App\Filament\Resources\NavLinks\Pages;

use App\Filament\Resources\NavLinks\NavLinkResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListNavLinks extends ListRecords
{
    protected static string $resource = NavLinkResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}