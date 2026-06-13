<?php

namespace App\Filament\Resources\InquiryRequests\Pages;

use App\Filament\Resources\InquiryRequests\InquiryRequestResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditInquiryRequest extends EditRecord
{
    protected static string $resource = InquiryRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}