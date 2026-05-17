<?php

namespace App\Filament\Resources\RegistrationRequests\Pages;

use App\Filament\Resources\RegistrationRequests\RegistrationRequestResource;
use App\Filament\Resources\RegistrationRequests\Schemas\RegistrationRequestViewSchema;
use Filament\Schemas\Schema;
use Filament\Resources\Pages\ViewRecord;

class ViewRegistrationRequest extends ViewRecord
{
    protected static string $resource = RegistrationRequestResource::class;

    public function form(Schema $schema): Schema
    {
        return RegistrationRequestViewSchema::configure($schema);
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}