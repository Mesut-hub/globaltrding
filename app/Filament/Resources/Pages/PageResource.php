<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Concerns\HasPermission;
use App\Filament\Resources\Pages\Pages\CreatePage;
use App\Filament\Resources\Pages\Pages\EditPage;
use App\Filament\Resources\Pages\Pages\ListPages;
use App\Filament\Resources\Pages\Schemas\PageBlockBuilder;
use App\Models\Page;
use BackedEnum;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PageResource extends Resource
{
    use HasPermission;

    protected static string $permissionKey = 'pages';

    protected static ?string $model = Page::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Pages';

    protected static ?string $modelLabel = 'Page';

    public static function form(Schema $schema): Schema
    {
        $locales = config('locales.supported', ['en']);
        $default = config('locales.default', 'en');

        // FIX: was $schema->schema([) — Filament API is ->components()
        return $schema->components([

            TextInput::make('slug')
                ->label('Slug (shared across locales)')
                ->required()
                ->maxLength(255)
                ->unique(ignoreRecord: true)
                ->regex('/^[a-z0-9]+(?:[\/\-][a-z0-9]+)*$/')
                ->helperText('Example: cookie-policy  or  who-we-are/sustainability')
                ->live(onBlur: true)
                ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state, '-'))),

            // FIX: was 'is_active' — PageController and all other code use 'is_published'
            Toggle::make('is_published')
                ->label('Published')
                ->default(true),

            Tabs::make('PageTranslations')
                ->persistTabInQueryString()
                ->columnSpanFull()
                ->tabs(
                    collect($locales)->map(function (string $locale) use ($default): Tab {
                        $lbl = strtoupper($locale);
                        return Tab::make($lbl)->schema([
                            TextInput::make("title.{$locale}")
                                ->label("Page title ({$lbl})")
                                ->required($locale === $default)
                                ->maxLength(255),
                            TextInput::make("meta_title.{$locale}")
                                ->label("SEO title ({$lbl})")
                                ->maxLength(60),
                            Textarea::make("meta_description.{$locale}")
                                ->label("SEO description ({$lbl})")
                                ->rows(2)
                                ->maxLength(160),
                        ]);
                    })->values()->all()
                ),

            Builder::make('blocks')
                ->label('Page blocks')
                ->collapsible()
                ->blocks(PageBlockBuilder::blocks()),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('slug')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->getStateUsing(fn ($record) => data_get($record->title, app()->getLocale())
                        ?: data_get($record->title, config('locales.default', 'en'))
                        ?: '—')
                    ->searchable(query: fn ($query, $search) =>
                        $query->whereRaw("JSON_SEARCH(LOWER(title), 'one', LOWER(?)) IS NOT NULL", ["%{$search}%"])
                    ),
                Tables\Columns\IconColumn::make('is_published')->boolean()->label('Published'),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
            ])]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit'   => Pages\EditPage::route('/{record}/edit'),
        ];
    }
}