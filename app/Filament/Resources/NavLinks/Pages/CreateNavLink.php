<?php

namespace App\Filament\Resources\NavLinks\Pages;

use App\Filament\Resources\NavLinks\NavLinkResource;
use Filament\Resources\Pages\CreateRecord;

class CreateNavLink extends CreateRecord
{
    protected static string $resource = NavLinkResource::class;

    protected function afterCreate(): void
    {
        \Illuminate\Support\Facades\Cache::forget('gt_nav_payload');
    }
}