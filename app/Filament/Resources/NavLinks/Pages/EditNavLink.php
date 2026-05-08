<?php

namespace App\Filament\Resources\NavLinks\Pages;

use App\Filament\Resources\NavLinks\NavLinkResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditNavLink extends EditRecord
{
    protected static string $resource = NavLinkResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}