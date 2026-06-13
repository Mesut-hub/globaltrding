<?php

namespace App\Filament\Resources\NavGroups\Pages;

use App\Filament\Resources\NavGroups\NavGroupResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListNavGroups extends ListRecords
{
    protected static string $resource = NavGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}