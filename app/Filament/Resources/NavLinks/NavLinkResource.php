<?php

namespace App\Filament\Resources\NavLinks;

use App\Filament\Concerns\HasBlockLocaleTabs;
use App\Filament\Concerns\HasPermission;
use App\Filament\Resources\NavLinks\Pages\CreateNavLink;
use App\Filament\Resources\NavLinks\Pages\EditNavLink;
use App\Filament\Resources\NavLinks\Pages\ListNavLinks;
use Filament\Forms\Components\FileUpload;
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
    use HasBlockLocaleTabs;    
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

            FileUpload::make('preview_image')
                ->label(__('Overlay preview image'))
                ->helperText(__('Upload an image or paste an absolute URL below. Uploaded images are stored in storage/app/public/nav-previews/.'))
                ->image()
                ->disk('public')
                ->directory('nav-previews')
                ->visibility('public')
                ->imagePreviewHeight('120')
                ->nullable()
                ->columnSpanFull(),

            Toggle::make('is_finder')
                ->label(__('Product finder row'))
                ->default(false),

            Select::make('target')
                ->label(__('Target'))
                ->options(['_self' => '_self', '_blank' => '_blank'])
                ->default('_self')
                ->required(),

            static::blockLocaleTabs('split_lang', [
                    ['name' => 'label',     'label' => 'Label (multilingual)',    'type' => 'text'],
                    ['name' => 'desc',      'label' => 'Overlay description','type' => 'html', 'rows' => 8],
                ]),

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