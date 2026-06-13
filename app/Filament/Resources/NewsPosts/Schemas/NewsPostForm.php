<?php

namespace App\Filament\Resources\NewsPosts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
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
                            ->maxLength(255)
                            ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')
                            ->unique(ignoreRecord: true)
                            ->helperText('Example: new-partnership-rotork')
                            ->afterStateUpdated(function (?string $state, callable $set) {
                                if (! is_string($state)) return;
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
                            ->required()
                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                if ($state && blank($get('published_at'))) {
                                    $set('published_at', now());
                                }
                            }),

                        Toggle::make('is_featured')
                            ->label('Featured on Home')
                            ->default(false)
                            ->required(),
                    ]),

                Grid::make(2)
                    ->schema([
                        FileUpload::make('cover_image_path')
                            ->label('Cover image (optional)')
                            ->disk('public')
                            ->directory('news/covers')
                            ->image(),

                        FileUpload::make('cover_video_path')
                            ->label('Cover video (optional)')
                            ->disk('public')
                            ->directory('news/covers')
                            ->acceptedFileTypes(['video/mp4', 'video/webm']),

                        FileUpload::make('cover_poster_path')
                            ->label('Video poster (optional)')
                            ->disk('public')
                            ->directory('news/covers')
                            ->image()
                            ->helperText('Used as poster for the cover video.'),
                    ])
                    ->columns(2),

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