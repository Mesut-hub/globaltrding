<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Brand;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        $locales = config('locales.supported', ['en']);
        $default = config('locales.default', 'en');

        return $schema->components([
            Grid::make(2)->schema([
                Select::make('brand_id')
                    ->label('Brand')
                    ->required()
                    ->options(fn () => Brand::query()
                        ->where('is_published', true)
                        ->orderBy('id')
                        ->get()
                        ->mapWithKeys(fn (Brand $b) => [
                            $b->id => (data_get($b->name, $default) ?: "Brand #{$b->id}"),
                        ])
                        ->all()
                    )
                    ->searchable()
                    ->preload(),

                Toggle::make('is_published')
                    ->label('Published')
                    ->default(true)
                    ->required(),
            ]),

            TextInput::make('slug')
                ->required()
                ->maxLength(255)
                ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')
                ->unique(ignoreRecord: true)
                ->helperText('Example: butterfly-valve-3-inch')
                ->afterStateUpdated(function (?string $state, callable $set) {
                    if (is_string($state)) {
                        $set('slug', Str::slug($state));
                    }
                }),

            Tabs::make('Translations')
                ->persistTabInQueryString()
                ->tabs(
                    collect($locales)->map(function (string $locale) use ($default) {
                        $label = strtoupper($locale);

                        return Tab::make($label)->schema([
                            TextInput::make("name.$locale")
                                ->label("Name ($label)")
                                ->required($locale === $default)
                                ->maxLength(255),

                            Textarea::make("summary.$locale")
                                ->label("Summary ($label)")
                                ->rows(3),

                            Textarea::make("description.$locale")
                                ->label("Description ($label)")
                                ->rows(10),

                            Grid::make(2)->schema([
                                TextInput::make("seo.title.$locale")
                                    ->label("SEO Title ($label)")
                                    ->maxLength(60),

                                Textarea::make("seo.description.$locale")
                                    ->label("SEO Description ($label)")
                                    ->rows(3)
                                    ->maxLength(160),
                            ]),
                        ]);
                    })->values()->all()
                ),
        ]);
    }
}