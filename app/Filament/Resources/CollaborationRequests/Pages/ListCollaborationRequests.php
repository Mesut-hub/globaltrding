<?php

namespace App\Filament\Resources\CollaborationRequests\Pages;

use App\Filament\Resources\CollaborationRequests\CollaborationRequestResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCollaborationRequests extends ListRecords
{
    protected static string $resource = CollaborationRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
