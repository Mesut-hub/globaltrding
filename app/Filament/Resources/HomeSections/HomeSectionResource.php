<?php

namespace App\Filament\Resources\HomeSections;

use App\Filament\Concerns\HasPermission;
use App\Filament\Resources\HomeSections\Pages\CreateHomeSection;
use App\Filament\Resources\HomeSections\Pages\EditHomeSection;
use App\Filament\Resources\HomeSections\Pages\ListHomeSections;
use App\Filament\Resources\Pages\Schemas\PageBlockBuilder;
use App\Models\HomeSection;
use BackedEnum;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions;

class HomeSectionResource extends Resource
{
    use HasPermission;

    protected static string $permissionKey = 'home_sections';

    protected static ?string $model = HomeSection::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Home sections';

    protected static ?string $modelLabel = 'Home section';

    public static function form(Schema $schema): Schema
    {
        $locales = config('locales.supported', ['en']);
        $default = config('locales.default', 'en');

        // FIX 1: was "$form->schema([" — $form is undefined, parameter is $schema
        // FIX 2: Filament API is ->components(), not ->schema()
        return $schema->components([
            TextInput::make('key')
                ->label('Section key (internal identifier)')
                ->required()
                ->maxLength(64)
                ->unique(ignoreRecord: true),

            // FIX 3: was 'sort' — DB column is 'sort_order'
            TextInput::make('sort_order')
                ->label('Display order')
                ->numeric()
                ->default(0),

            Toggle::make('is_active')
                ->label('Active')
                ->default(true),

            // Translatable title (requires the migration above to be run)
            Tabs::make('HomeSectionTranslations')
                ->columnSpanFull()
                ->tabs(
                    collect($locales)->map(function (string $locale) use ($default): Tab {
                        $lbl = strtoupper($locale);
                        return Tab::make($lbl)->schema([
                            TextInput::make("title.{$locale}")
                                ->label("Section title ({$lbl})")
                                ->maxLength(255),
                        ]);
                    })->values()->all()
                ),

            Builder::make('blocks')
                ->label('Blocks')
                ->collapsible()
                ->blocks(array_merge(
                    PageBlockBuilder::blocks(),
                )),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('title')
                    ->getStateUsing(function ( $record ) {
                        if (! $record) return '—';
                        return data_get($record->title, app()->getLocale())
                            ?: data_get($record->title, config('locales.default', 'en'))
                            ?: '—';
                    }),
                // FIX: was 'sort'
                Tables\Columns\TextColumn::make('sort_order')->sortable(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->defaultSort('sort_order')    // FIX: was 'sort'
            ->reorderable('sort_order')    // FIX: was 'sort'
            ->recordActions([Actions\EditAction::make()]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListHomeSections::route('/'),
            'create' => CreateHomeSection::route('/create'),
            'edit'   => EditHomeSection::route('/{record}/edit'),
        ];
    }
}