<?php

namespace App\Filament\Resources\Customers;

use App\Filament\Concerns\HasPermission;
use App\Filament\Resources\Customers\Pages\ListCustomers;
use App\Filament\Resources\Customers\Pages\ViewCustomer;
use App\Filament\Resources\Customers\RelationManagers\ActivityLogsRelationManager;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CustomerResource extends Resource
{
    use HasPermission;

    protected static string $permissionKey = 'customers';

    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $navigationLabel = 'Customers';

    protected static ?string $modelLabel = 'Customer';

    protected static ?string $pluralModelLabel = 'Customers';

    protected static \UnitEnum|string|null $navigationGroup = 'Customers';

    protected static ?int $navigationSort = 1;

    // Scope to non-admin users only
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->where('is_admin', false);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return \App\Filament\Resources\Customers\Tables\CustomersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ActivityLogsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCustomers::route('/'),
            'view'  => ViewCustomer::route('/{record}'),
        ];
    }

    // Customers cannot be created from within this resource
    // (created via Registration Request approval)
    public static function canCreate(): bool
    {
        return false;
    }
}