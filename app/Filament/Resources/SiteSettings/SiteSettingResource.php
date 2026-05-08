<?php

namespace App\Filament\Resources\SiteSettings;

use App\Filament\Concerns\HasPermission;
use App\Filament\Resources\SiteSettings\Pages\EditSiteSetting;
use App\Filament\Resources\SiteSettings\Pages\ListSiteSettings;
use App\Models\SiteSetting;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SiteSettingResource extends Resource
{
    use HasPermission;

    protected static string $permissionKey = 'site_settings';

    protected static ?string $model = SiteSetting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static \UnitEnum|string|null $navigationGroup = 'Settings';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('key')
                ->label(__('Setting'))
                ->options([
                    'linkedin_url' => 'LinkedIn URL',
                    'instagram_url' => 'Instagram URL',
                    'x_url' => 'X/Twitter URL',
                    'youtube_url' => 'YouTube URL',
                ])
                ->required()
                ->unique(ignoreRecord: true),

            TextInput::make('value')
                ->label(__('Value'))
                ->helperText(__('Full URL including https://'))
                ->url()
                ->nullable(),
        ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('key')->sortable()->searchable(),
            TextColumn::make('value')->sortable()->wrap(),
            TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
        ])->defaultSort('key');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSiteSettings::route('/'),
            'edit' => EditSiteSetting::route('/{record}/edit'),
        ];
    }
}