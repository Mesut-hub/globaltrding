<?php

namespace App\Filament\Resources\NavGroups\Pages;

use App\Filament\Resources\NavGroups\NavGroupResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Cache;

class ListNavGroups extends ListRecords
{
    protected static string $resource = NavGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }

    public function reorderTable(array $order, string|int|null $draggedRecordKey = null): void
    {
        parent::reorderTable($order, $draggedRecordKey);

        Cache::forget('gt_nav_payload');
    }
}