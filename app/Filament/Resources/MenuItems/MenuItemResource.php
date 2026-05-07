<?php

namespace App\Filament\Resources\MenuItems;

use App\Filament\Concerns\HasPermission;
use App\Filament\Resources\MenuItems\Pages\CreateMenuItem;
use App\Filament\Resources\MenuItems\Pages\EditMenuItem;
use App\Filament\Resources\MenuItems\Pages\ListMenuItems;
use App\Models\MenuItem;
use App\Models\Page;
use BackedEnum;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MenuItemResource extends Resource
{
    use HasPermission;

    protected static string $permissionKey = 'menu_items';

    protected static ?string $model = MenuItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBars3;

    protected static \UnitEnum|string|null $navigationGroup = 'Menu';

    public static function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema->components([
            \Filament\Forms\Components\KeyValue::make('label')
                ->label(__('Label (multilingual)'))
                ->keyLabel(__('Locale'))
                ->valueLabel(__('Label'))
                ->helperText(__('Use keys: en, tr, ar, fr'))
                ->required()
                ->dehydrateStateUsing(function ($state) {
                    // normalize JSON string -> array
                    if (is_string($state)) {
                        $decoded = json_decode($state, true);
                        $state = is_array($decoded) ? $decoded : [];
                    }

                    // normalize row-list -> associative ['en' => '...', ...]
                    if (is_array($state) && array_is_list($state)) {
                        $assoc = [];
                        foreach ($state as $row) {
                            if (is_array($row) && isset($row['key'])) {
                                $k = trim((string) $row['key']);
                                $v = (string) ($row['value'] ?? '');
                                if ($k !== '') {
                                    $assoc[$k] = $v;
                                }
                            }
                        }
                        $state = $assoc;
                    }

                    return is_array($state) ? $state : [];
                })
                ->rule(function () {
                    $default = config('locales.default', 'en');

                    return function (string $attribute, $value, \Closure $fail) use ($default) {
                        // normalize JSON string -> array
                        if (is_string($value)) {
                            $decoded = json_decode($value, true);
                            $value = is_array($decoded) ? $decoded : [];
                        }

                        // normalize row-list -> associative
                        if (is_array($value) && array_is_list($value)) {
                            $assoc = [];
                            foreach ($value as $row) {
                                if (is_array($row) && isset($row['key'])) {
                                    $k = trim((string) $row['key']);
                                    $v = (string) ($row['value'] ?? '');
                                    if ($k !== '') {
                                        $assoc[$k] = $v;
                                    }
                                }
                            }
                            $value = $assoc;
                        }

                        $en = '';
                        if (is_array($value)) {
                            $en = trim((string) ($value[$default] ?? ''));
                        }

                        if ($en === '') {
                            $fail("Label must include a non-empty '{$default}' value.");
                        }
                    };
                }),

            \Filament\Forms\Components\Select::make('page_id')
                ->label(__('Page'))
                ->options(fn () => \App\Models\Page::query()->orderBy('slug')->pluck('slug', 'id')->all())
                ->searchable()
                ->nullable()
                ->reactive()
                ->afterStateUpdated(function ($state, callable $set) {
                    // If a Page is selected, clear URL
                    if (!empty($state)) {
                        $set('url', null);
                    }
                })
                ->helperText(__('Select a Page OR enter a URL. Not both.')),

            \Filament\Forms\Components\TextInput::make('url')
                ->label(__('URL'))
                ->nullable()
                ->reactive()
                ->afterStateUpdated(function ($state, callable $set) {
                    // If URL is set, clear Page
                    if (!empty($state)) {
                        $set('page_id', null);
                    }
                })
                ->helperText(__('Optional. Can include {locale}, e.g. /{locale}/industries')),

            \Filament\Forms\Components\Select::make('parent_id')
                ->label(__('Parent item'))
                ->nullable()
                ->options(fn () => \App\Models\MenuItem::query()
                    ->whereNull('parent_id')
                    ->orderBy('sort_order')
                    ->pluck('id', 'id')
                    ->all())
                ->helperText(__('Only top-level items can be selected as parents.')),

            \Filament\Forms\Components\Toggle::make('is_active')
                ->label(__('Active'))
                ->default(true)
                ->required(),

            \Filament\Forms\Components\Select::make('target')
                ->label(__('Target'))
                ->options(['_self' => '_self', '_blank' => '_blank'])
                ->default('_self')
                ->required(),

            \Filament\Forms\Components\TextInput::make('sort_order')
                ->label(__('Sort order'))
                ->numeric()
                ->default(0)
                ->required(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order', 'asc')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('id')->sortable(),
                \Filament\Tables\Columns\TextColumn::make('label')
                    ->label(__('Label'))
                    ->formatStateUsing(function ($state) {
                        $loc = app()->getLocale();
                        $fallback = config('locales.default', 'en');

                        // state might be array, JSON string, or plain string
                        if (is_string($state)) {
                            $decoded = json_decode($state, true);
                            if (is_array($decoded)) {
                                $state = $decoded;
                            } else {
                                return $state; // plain string label
                            }
                        }

                        if (!is_array($state)) {
                            return '';
                        }

                        $v = $state[$loc] ?? $state[$fallback] ?? null;

                        if (is_string($v) && trim($v) !== '') {
                            return $v;
                        }

                        // last resort: first available translation
                        foreach ($state as $x) {
                            if (is_string($x) && trim($x) !== '') {
                                return $x;
                            }
                        }

                        return '';
                    }),
                TextColumn::make('parent_id')->label(__('Parent')),
                IconColumn::make('is_active')->boolean(),
                TextColumn::make('target')->label(__('Target')),
                TextColumn::make('sort_order')->sortable(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMenuItems::route('/'),
            'create' => CreateMenuItem::route('/create'),
            'edit' => EditMenuItem::route('/{record}/edit'),
        ];
    }
}