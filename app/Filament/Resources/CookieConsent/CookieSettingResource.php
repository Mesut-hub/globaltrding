<?php

namespace App\Filament\Resources\CookieConsent;

use App\Filament\Concerns\HasPermission;
use App\Models\CookieSetting;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CookieSettingResource extends Resource
{
    use HasPermission;

    protected static string $permissionKey = 'cookie_settings_manage';
    protected static ?string $model = CookieSetting::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;
    protected static \UnitEnum|string|null $navigationGroup = 'Settings';
    protected static ?string $navigationLabel = 'Cookie Settings';

    public static function form(Schema $schema): Schema
    {
        $locales = config('locales.supported', ['en']);

        // Determine if this is a multilingual text field
        $multilingualKeys = ['banner_title', 'banner_description'];

        return $schema->components([
            Select::make('key')
                ->options([
                    'banner_title'       => 'Banner Title',
                    'banner_description' => 'Banner Description',
                    'consent_version'    => 'Consent Version (bump to re-ask)',
                    'policy_url_suffix'  => 'Privacy Policy URL Suffix',
                    'show_reject_all'    => 'Show Reject All Button',
                    'show_manage'        => 'Show Manage Preferences Button',
                    'position'           => 'Banner Position',
                ])
                ->required()
                ->unique(ignoreRecord: true)
                ->reactive(),

            // Multilingual text settings
            Tabs::make('Translations')
                ->columnSpanFull()
                ->visible(fn ($get) => in_array($get('key'), $multilingualKeys, true))
                ->tabs(collect($locales)->map(function (string $locale) {
                    $lbl = strtoupper($locale);
                    return Tab::make($lbl)->schema([
                        Textarea::make("value.{$locale}")
                            ->label("Value ({$lbl})")
                            ->rows(3),
                    ]);
                })->values()->all()),

            // Scalar settings
            TextInput::make('value.scalar')
                ->label('Value')
                ->visible(fn ($get) => !in_array($get('key'), $multilingualKeys, true)
                    && !in_array($get('key'), ['show_reject_all', 'show_manage'], true))
                ->dehydrateStateUsing(fn ($state, $get) => $state)
                ->afterStateHydrated(function ($component, $state, $record) {
                    if ($record && !is_array($record->value)) {
                        $component->state((string) $record->value);
                    }
                }),

            Toggle::make('value.bool')
                ->label('Enabled')
                ->visible(fn ($get) => in_array($get('key'), ['show_reject_all', 'show_manage'], true)),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')->sortable()->searchable(),
                TextColumn::make('value')
                    ->formatStateUsing(function ($state) {
                        if (is_array($state)) {
                            $loc = app()->getLocale();
                            return $state[$loc] ?? $state['en'] ?? json_encode($state);
                        }
                        if (is_bool($state)) return $state ? 'Yes' : 'No';
                        return (string) $state;
                    })
                    ->wrap(),
                TextColumn::make('updated_at')->dateTime()->sortable(),
            ])
            ->defaultSort('key');
    }

    public static function getPages(): array
    {
        return [
            'index'  => \App\Filament\Resources\CookieConsent\Pages\ListCookieSettings::route('/'),
            'create' => \App\Filament\Resources\CookieConsent\Pages\CreateCookieSetting::route('/create'),
            'edit'   => \App\Filament\Resources\CookieConsent\Pages\EditCookieSetting::route('/{record}/edit'),
        ];
    }
}