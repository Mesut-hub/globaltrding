<?php

namespace App\Filament\Resources\NavGroups;

use App\Filament\Concerns\HasPermission;
use App\Filament\Resources\NavGroups\Pages\CreateNavGroup;
use App\Filament\Resources\NavGroups\Pages\EditNavGroup;
use App\Filament\Resources\NavGroups\Pages\ListNavGroups;
use App\Models\NavGroup;
use App\Models\Page;
use BackedEnum;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class NavGroupResource extends Resource
{
    use HasPermission;

    protected static string $permissionKey = 'nav_groups';

    protected static ?string $model = NavGroup::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBars3;

    protected static \UnitEnum|string|null $navigationGroup = 'Menu';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([

            // ── Identity ─────────────────────────────────────────────────────

            TextInput::make('key')
                ->label(__('Key'))
                ->helperText(__('Unique identifier (e.g. who-we-are, products).'))
                ->required()
                ->maxLength(80)
                ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')
                ->unique(ignoreRecord: true),

            KeyValue::make('label')
                ->label(__('Label (multilingual)'))
                ->keyLabel(__('Locale'))
                ->valueLabel(__('Label'))
                ->helperText(__('Use keys: en, tr, ar, fr'))
                ->required()
                ->columnSpanFull()
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

            // ── Overlay section-title link ────────────────────────────────────
            // Rendered ONLY on the overlay panel heading; the main nav-bar item
            // is never a link.

            Placeholder::make('overlay_link_heading')
                ->label(__('Overlay section-title link'))
                ->content(__('Optional. Choose a CMS page OR enter a raw URL — not both. This link appears on the section heading inside the nav overlay, not on the top nav bar item itself.'))
                ->columnSpanFull(),

            Select::make('page_id')
                ->label(__('Page'))
                ->options(fn () => Page::query()->orderBy('slug')->pluck('slug', 'id')->all())
                ->searchable()
                ->nullable()
                ->reactive()
                ->afterStateUpdated(function ($state, callable $set) {
                    // Clear the raw URL when a page is chosen
                    if (!empty($state)) {
                        $set('url', null);
                    }
                })
                ->helperText(__('Select a CMS page — or use the URL field below.')),

            TextInput::make('url')
                ->label(__('URL'))
                ->nullable()
                ->reactive()
                ->afterStateUpdated(function ($state, callable $set) {
                    // Clear the page when a raw URL is typed
                    if (!empty($state)) {
                        $set('page_id', null);
                    }
                })
                ->helperText(__('Absolute URL or relative path. Supports {locale} placeholder.')),

            Select::make('target')
                ->label(__('Target'))
                ->options(['_self' => '_self', '_blank' => '_blank'])
                ->default('_self')
                ->required(),

            // ── Visibility & ordering ─────────────────────────────────────────

            Toggle::make('is_active')
                ->label(__('Active'))
                ->default(true)
                ->required(),

            TextInput::make('sort_order')
                ->label(__('Sort order'))
                ->numeric()
                ->default(0)
                ->required(),

        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('key')->sortable()->searchable(),
                TextColumn::make('label')
                    ->label(__('Label'))
                    ->formatStateUsing(function ($state) {
                        $loc      = app()->getLocale();
                        $fallback = config('locales.default', 'en');

                        if (is_string($state)) {
                            $decoded = json_decode($state, true);
                            $state = is_array($decoded) ? $decoded : ['en' => $state];
                        }
                        if (!is_array($state)) return '';

                        return (string) ($state[$loc] ?? $state[$fallback] ?? collect($state)->first() ?? '');
                    }),
                TextColumn::make('url')
                    ->label(__('Overlay URL'))
                    ->toggleable()
                    ->placeholder('—'),
                TextColumn::make('page.slug')
                    ->label(__('Overlay page'))
                    ->toggleable()
                    ->placeholder('—'),
                IconColumn::make('is_active')->boolean(),
                TextColumn::make('sort_order')->sortable(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListNavGroups::route('/'),
            'create' => CreateNavGroup::route('/create'),
            'edit'   => EditNavGroup::route('/{record}/edit'),
        ];
    }
}