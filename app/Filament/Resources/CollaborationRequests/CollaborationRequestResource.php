<?php

namespace App\Filament\Resources\CollaborationRequests;

use App\Filament\Resources\CollaborationRequests\Pages\CreateCollaborationRequest;
use App\Filament\Resources\CollaborationRequests\Pages\EditCollaborationRequest;
use App\Filament\Resources\CollaborationRequests\Pages\ListCollaborationRequests;
use App\Filament\Resources\CollaborationRequests\Schemas\CollaborationRequestForm;
use App\Filament\Resources\CollaborationRequests\Tables\CollaborationRequestsTable;
use App\Models\CollaborationRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Filament\Concerns\HasPermission;

class CollaborationRequestResource extends Resource
{
    use HasPermission;

    protected static string $permissionKey = 'collaboration_requests';

    protected static ?string $model = CollaborationRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return CollaborationRequestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CollaborationRequestsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCollaborationRequests::route('/'),
            'create' => CreateCollaborationRequest::route('/create'),
            'edit' => EditCollaborationRequest::route('/{record}/edit'),
        ];
    }
}
