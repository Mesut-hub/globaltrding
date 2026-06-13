<?php

namespace App\Filament\Resources\RegistrationRequests;

use App\Filament\Concerns\HasPermission;
use App\Filament\Resources\RegistrationRequests\Pages\ListRegistrationRequests;
use App\Filament\Resources\RegistrationRequests\Pages\ViewRegistrationRequest;
use App\Filament\Resources\RegistrationRequests\Tables\RegistrationRequestsTable;
use App\Models\RegistrationRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;

class RegistrationRequestResource extends Resource
{
    use HasPermission;

    protected static string $permissionKey = 'registration_requests';

    protected static ?string $model = RegistrationRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function table(Table $table): Table
    {
        return RegistrationRequestsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRegistrationRequests::route('/'),
            'view' => ViewRegistrationRequest::route('/{record}'),
        ];
    }
}