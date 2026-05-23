<?php

namespace App\Filament\Resources\HomeSections;

use App\Filament\Resources\HomeSections\Pages\CreateHomeSection;
use App\Filament\Resources\HomeSections\Pages\EditHomeSection;
use App\Filament\Resources\HomeSections\Pages\ListHomeSections;
use App\Filament\Resources\HomeSections\Schemas\HomeSectionForm;
use App\Filament\Resources\HomeSections\Tables\HomeSectionsTable;
use App\Filament\Resources\Pages\Schemas\PageBlockBuilder;
use App\Models\HomeSection;
use BackedEnum;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Concerns\HasPermission;

class HomeSectionResource extends Resource
{
    use HasPermission;

    protected static string $permissionKey = 'home_sections';

    protected static ?string $model = HomeSection::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Home sections';

    protected static ?string $modelLabel     = 'Home section';

    /*public static function form(Schema $schema): Schema
    {
        return HomeSectionForm::configure($schema);
    }*/

    public static function form(Schema $schema): Schema
    {
        $locales = config('locales.supported', ['en']);
        $default = config('locales.default', 'en');

        return $form->schema([
            TextInput::make('key')
                ->label('Section key (internal identifier)')
                ->required()->maxLength(64)->unique(ignoreRecord: true),

            TextInput::make('sort')
                ->label('Display order')->numeric()->default(0),

            Toggle::make('is_active')->label('Active')->default(true),

            Tabs::make('HomeSectionTranslations')
                ->columnSpanFull()
                ->tabs(
                    collect($locales)->map(function (string $locale) use ($default): Tab {
                        $lbl = strtoupper($locale);
                        return Tab::make($lbl)->schema([
                            TextInput::make("title.{$locale}")
                                ->label("Section title ({$lbl})")
                                ->required($locale === $default)
                                ->maxLength(255),
                        ]);
                    })->values()->all()
                ),

            Builder::make('blocks')
                ->label('Blocks')
                ->collapsible()
                ->blocks(PageBlockBuilder::blocks()),
        ]);
    }
    
        /*public static function table(Table $table): Table
    {
        return HomeSectionsTable::configure($table);
    }*/

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('title')
                    ->getStateUsing(fn ($r) => data_get($r->title, app()->getLocale())
                        ?: data_get($r->title, config('locales.default', 'en')) ?: '—'),
                Tables\Columns\TextColumn::make('sort')->sortable(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->defaultSort('sort')
            ->reorderable('sort')
            ->actions([Tables\Actions\EditAction::make()]);
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
            'index' => ListHomeSections::route('/'),
            'create' => CreateHomeSection::route('/create'),
            'edit' => EditHomeSection::route('/{record}/edit'),
        ];
    }
    
}
