<?php

namespace App\Filament\Resources\CustomerRegistration\Pages;

use App\Filament\Resources\CustomerRegistration\CustomerRegistrationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCustomerRegistration extends EditRecord
{
    protected static string $resource = CustomerRegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}