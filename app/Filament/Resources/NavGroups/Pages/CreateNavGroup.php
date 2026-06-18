<?php

namespace App\Filament\Resources\NavGroups\Pages;

use App\Filament\Resources\NavGroups\NavGroupResource;
use Filament\Resources\Pages\CreateRecord;

class CreateNavGroup extends CreateRecord
{
    protected static string $resource = NavGroupResource::class;

    protected function afterCreate(): void
    {
        \Illuminate\Support\Facades\Cache::forget('gt_nav_payload');
    }
}