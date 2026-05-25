<?php

namespace App\Filament\Resources\CookieConsent;

use App\Filament\Concerns\HasPermission;
use App\Models\CookieCategory;
use BackedEnum;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CookieCategoryResource extends Resource
{
    use HasPermission;

    protected static string $permissionKey = 'cookie_categories';
    protected static ?string $model = CookieCategory::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;
    protected static \UnitEnum|string|null $navigationGroup = 'Settings';
    protected static ?string $navigationLabel = 'Cookie Categories';

    public static function form(Schema $schema): Schema
    {
        $locales = config('locales.supported', ['en']);
        $default = config('locales.default', 'en');

        return $schema->components([
            TextInput::make('key')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(64)
                ->helperText('e.g. necessary, analytics, marketing, social'),

            Toggle::make('is_required')
                ->label('Always active (cannot be disabled by user)')
                ->helperText('Enable for "Strictly Necessary" category only.'),

            Toggle::make('is_enabled')
                ->label('Show this category to users')
                ->default(true),

            TextInput::make('sort_order')
                ->numeric()
                ->default(0),

            Tabs::make('Translations')
                ->columnSpanFull()
                ->tabs(collect($locales)->map(function (string $locale) use ($default) {
                    $lbl = strtoupper($locale);
                    return Tab::make($lbl)->schema([
                        TextInput::make("label.{$locale}")
                            ->label("Label ({$lbl})")
                            ->required($locale === $default),
                        Textarea::make("description.{$locale}")
                            ->label("Description ({$lbl})")
                            ->rows(4)
                            ->required($locale === $default),
                    ]);
                })->values()->all()),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('key')->sortable()->searchable(),
                TextColumn::make('label')
                    ->formatStateUsing(fn ($state) => is_array($state)
                        ? ($state[app()->getLocale()] ?? $state['en'] ?? '')
                        : (string) $state),
                IconColumn::make('is_required')->boolean()->label('Always Active'),
                IconColumn::make('is_enabled')->boolean()->label('Enabled'),
                TextColumn::make('sort_order')->sortable(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => \App\Filament\Resources\CookieConsent\Pages\ListCookieCategories::route('/'),
            'create' => \App\Filament\Resources\CookieConsent\Pages\CreateCookieCategory::route('/create'),
            'edit'   => \App\Filament\Resources\CookieConsent\Pages\EditCookieCategory::route('/{record}/edit'),
        ];
    }
}