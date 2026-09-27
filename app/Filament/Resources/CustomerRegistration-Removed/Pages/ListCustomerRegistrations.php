<?php

namespace App\Filament\Resources\CustomerRegistration\Pages;

use App\Filament\Resources\CustomerRegistration\CustomerRegistrationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCustomerRegistrations extends ListRecords
{
    protected static string $resource = CustomerRegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Add customer')];
    }
}