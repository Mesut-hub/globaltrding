<?php

namespace App\Filament\Resources\InquiryRequests;

use App\Filament\Resources\InquiryRequests\Pages\CreateInquiryRequest;
use App\Filament\Resources\InquiryRequests\Pages\EditInquiryRequest;
use App\Filament\Resources\InquiryRequests\Pages\ListInquiryRequests;
use App\Filament\Resources\InquiryRequests\Schemas\InquiryRequestForm;
use App\Filament\Resources\InquiryRequests\Tables\InquiryRequestsTable;
use App\Models\InquiryRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Filament\Concerns\HasPermission;

class InquiryRequestResource extends Resource
{
    use HasPermission;

    protected static string $permissionKey = 'inquiry_requests';

    protected static ?string $model = InquiryRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return InquiryRequestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InquiryRequestsTable::configure($table);
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
            'index' => ListInquiryRequests::route('/'),
            'create' => CreateInquiryRequest::route('/create'),
            'edit' => EditInquiryRequest::route('/{record}/edit'),
        ];
    }
}