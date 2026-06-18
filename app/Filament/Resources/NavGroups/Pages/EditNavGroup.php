<?php

namespace App\Filament\Resources\NavGroups\Pages;

use App\Filament\Resources\NavGroups\NavGroupResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditNavGroup extends EditRecord
{
    protected static string $resource = NavGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }

    protected function afterSave(): void
    {
        \Illuminate\Support\Facades\Cache::forget('gt_nav_payload');
    }
}