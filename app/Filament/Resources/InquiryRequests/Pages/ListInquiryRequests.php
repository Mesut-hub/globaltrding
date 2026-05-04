<?php

namespace App\Filament\Resources\InquiryRequests\Pages;

use App\Filament\Resources\InquiryRequests\InquiryRequestResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListInquiryRequests extends ListRecords
{
    protected static string $resource = InquiryRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}