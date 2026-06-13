<?php

namespace App\Filament\Resources\NavLinks;

use App\Filament\Concerns\HasPermission;
use App\Filament\Resources\NavLinks\Pages\CreateNavLink;
use App\Filament\Resources\NavLinks\Pages\EditNavLink;
use App\Filament\Resources\NavLinks\Pages\ListNavLinks;
use App\Models\NavGroup;
use App\Models\NavLink;
use App\Models\Page;
use BackedEnum;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class NavLinkResource extends Resource
{
    use HasPermission;

    protected static string $permissionKey = 'nav_links';

    protected static ?string $model = NavLink::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLink;

    protected static \UnitEnum|string|null $navigationGroup = 'Menu';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('nav_group_id')
                ->label(__('Nav group'))
                ->options(fn () => NavGroup::query()->orderBy('sort_order')->pluck('key', 'id')->all())
                ->required()
                ->searchable(),

            KeyValue::make('label')
                ->label(__('Label (multilingual)'))
                ->keyLabel(__('Locale'))
                ->valueLabel(__('Label'))
                ->required()
                ->dehydrateStateUsing(function ($state) {
                    if (is_string($state)) {
                        $decoded = json_decode($state, true);
                        $state = is_array($decoded) ? $decoded : [];
                    }
                    if (is_array($state) && array_is_list($state)) {
                        $assoc = [];
                        foreach ($state as $row) {
                            if (is_array($row) && isset($row['key'])) {
                                $k = trim((string) $row['key']);
                                $v = (string) ($row['value'] ?? '');
                                if ($k !== '') $assoc[$k] = $v;
                            }
                        }
                        $state = $assoc;
                    }
                    return is_array($state) ? $state : [];
                })
                ->rule(function () {
                    $default = config('locales.default', 'en');
                    return function (string $attribute, $value, \Closure $fail) use ($default) {
                        if (is_string($value)) {
                            $decoded = json_decode($value, true);
                            $value = is_array($decoded) ? $decoded : [];
                        }
                        if (is_array($value) && array_is_list($value)) {
                            $assoc = [];
                            foreach ($value as $row) {
                                if (is_array($row) && isset($row['key'])) {
                                    $k = trim((string) $row['key']);
                                    $v = (string) ($row['value'] ?? '');
                                    if ($k !== '') $assoc[$k] = $v;
                                }
                            }
                            $value = $assoc;
                        }

                        $v = is_array($value) ? trim((string) ($value[$default] ?? '')) : '';
                        if ($v === '') $fail("Label must include a non-empty '{$default}' value.");
                    };
                }),

            Select::make('page_id')
                ->label(__('Page'))
                ->options(fn () => Page::query()->orderBy('slug')->pluck('slug', 'id')->all())
                ->searchable()
                ->nullable()
                ->reactive()
                ->afterStateUpdated(function ($state, callable $set) {
                    if (!empty($state)) $set('url', null);
                })
                ->helperText(__('Select a Page OR enter a URL. Not both.')),

            TextInput::make('url')
                ->label(__('URL'))
                ->nullable()
                ->reactive()
                ->afterStateUpdated(function ($state, callable $set) {
                    if (!empty($state)) $set('page_id', null);
                })
                ->helperText(__('Can include {locale}.')),

            TextInput::make('action')
                ->label(__('Action (optional)'))
                ->helperText(__('Special overlay action key, e.g. finder or search. Leave empty for normal links.'))
                ->nullable(),

            Textarea::make('desc')
                ->label(__('Overlay description'))
                ->rows(3)
                ->nullable(),

            TextInput::make('preview_image')
                ->label(__('Overlay preview image'))
                ->helperText(__('Absolute URL or public path, e.g. /images/overlay/example.jpg'))
                ->nullable(),

            Toggle::make('is_finder')
                ->label(__('Product finder row'))
                ->default(false),

            Select::make('target')
                ->label(__('Target'))
                ->options(['_self' => '_self', '_blank' => '_blank'])
                ->default('_self')
                ->required(),

            Toggle::make('is_active')->label(__('Active'))->default(true)->required(),

            TextInput::make('sort_order')->label(__('Sort order'))->numeric()->default(0)->required(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('group.key')->label(__('Group'))->sortable()->searchable(),
                TextColumn::make('label')->label(__('Label'))
                    ->formatStateUsing(function ($state) {
                        $loc = app()->getLocale();
                        $fallback = config('locales.default', 'en');

                        if (is_string($state)) {
                            $decoded = json_decode($state, true);
                            $state = is_array($decoded) ? $decoded : ['en' => $state];
                        }
                        if (!is_array($state)) return '';
                        return (string) ($state[$loc] ?? $state[$fallback] ?? collect($state)->first() ?? '');
                    }),
                TextColumn::make('url')->toggleable(),
                TextColumn::make('page.slug')->label(__('Page'))->toggleable(),
                IconColumn::make('is_active')->boolean(),
                TextColumn::make('sort_order')->sortable(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNavLinks::route('/'),
            'create' => CreateNavLink::route('/create'),
            'edit' => EditNavLink::route('/{record}/edit'),
        ];
    }
}