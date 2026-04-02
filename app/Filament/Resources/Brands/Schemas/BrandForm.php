<?php

namespace App\Filament\Resources\Brands\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class BrandForm
{
    public static function configure(Schema $schema): Schema
    {
        $locales = config('locales.supported', ['en']);
        $default = config('locales.default', 'en');

        return $schema->components([
            Grid::make(2)->schema([
                TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->afterStateUpdated(function (?string $state, callable $set) {
                        if (is_string($state)) {
                            $set('slug', Str::slug($state));
                        }
                    }),

                Toggle::make('is_published')
                    ->label('Published')
                    ->default(true)
                    ->required(),
            ]),

            Tabs::make('Translations')
                ->persistTabInQueryString()
                ->tabs(
                    collect($locales)->map(function (string $locale) use ($default) {
                        $label = strtoupper($locale);

                        return Tab::make($label)->schema([
                            TextInput::make("name.$locale")
                                ->label("Brand name ($label)")
                                ->required($locale === $default)
                                ->maxLength(255),
                        ]);
                    })->values()->all()
                ),
        ]);
    }
}