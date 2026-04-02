<?php

namespace App\Filament\Resources\NewsPosts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class NewsPostForm
{
    public static function configure(Schema $schema): Schema
    {
        $locales = config('locales.supported', ['en']);
        $default = config('locales.default', 'en');

        return $schema
            ->components([
                Grid::make(2)
                    ->schema([
                        TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->helperText('Example: new-partnership-rotork')
                            ->afterStateUpdated(function (?string $state, callable $set) {
                                if (! is_string($state)) {
                                    return;
                                }

                                $set('slug', Str::slug($state));
                            }),

                        DateTimePicker::make('published_at')
                            ->label('Publish date')
                            ->helperText('Leave empty to publish immediately (MVP).'),
                    ]),

                Grid::make(2)
                    ->schema([
                        Toggle::make('is_published')
                            ->label('Published')
                            ->default(true)
                            ->required(),

                        Toggle::make('is_featured')
                            ->label('Featured on Home')
                            ->default(false)
                            ->required(),
                    ]),

                Tabs::make('Translations')
                    ->persistTabInQueryString()
                    ->tabs(
                        collect($locales)->map(function (string $locale) use ($default) {
                            $label = strtoupper($locale);

                            return Tab::make($label)
                                ->schema([
                                    TextInput::make("title.$locale")
                                        ->label("Title ($label)")
                                        ->required($locale === $default)
                                        ->maxLength(255),

                                    Textarea::make("excerpt.$locale")
                                        ->label("Excerpt ($label)")
                                        ->rows(3)
                                        ->helperText('Short summary used in lists and homepage slider.'),

                                    Textarea::make("content.$locale")
                                        ->label("Content ($label)")
                                        ->rows(12),

                                    Grid::make(2)
                                        ->schema([
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