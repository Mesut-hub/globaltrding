<?php

namespace App\Filament\Resources\CollaborationRequests\Pages;

use App\Filament\Resources\CollaborationRequests\CollaborationRequestResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCollaborationRequest extends EditRecord
{
    protected static string $resource = CollaborationRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
