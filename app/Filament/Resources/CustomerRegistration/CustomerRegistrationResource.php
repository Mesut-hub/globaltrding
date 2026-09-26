<?php

namespace App\Filament\Resources\CustomerRegistration;

use App\Filament\Concerns\HasPermission;
use App\Filament\Resources\CustomerRegistration\Pages\CreateCustomerRegistration;
use App\Filament\Resources\CustomerRegistration\Pages\EditCustomerRegistration;
use App\Filament\Resources\CustomerRegistration\Pages\ListCustomerRegistrations;
use App\Filament\Resources\CustomerRegistration\RelationManagers\OrdersRelationManager;
use App\Filament\Resources\CustomerRegistration\Schemas\CustomerRegistrationForm;
use App\Filament\Resources\CustomerRegistration\Tables\CustomerRegistrationTable;
use App\Models\Customer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CustomerRegistrationResource extends Resource
{
    use HasPermission;

    protected static string $permissionKey = 'customer_registration';

    protected static ?string $model = Customer::class;

    protected static ?string $navigationLabel = 'Customer Registration';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    public static function form(Schema $schema): Schema
    {
        return CustomerRegistrationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomerRegistrationTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            OrdersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCustomerRegistrations::route('/'),
            'create' => CreateCustomerRegistration::route('/create'),
            'edit' => EditCustomerRegistration::route('/{record}/edit'),
        ];
    }
}