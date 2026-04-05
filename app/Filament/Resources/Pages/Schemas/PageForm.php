<?php

namespace App\Filament\Resources\Pages\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\FileUpload;

class PageForm
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
                            ->helperText('URL identifier. Example: about-us')
                            ->afterStateUpdated(function (?string $state, callable $set) {
                                if (! is_string($state)) return;
                                $set('slug', Str::slug($state));
                            }),

                        Toggle::make('is_published')
                            ->label('Published')
                            ->default(true)
                            ->required(),
                        Toggle::make('show_in_footer')
                            ->label('Show in footer')
                            ->default(false),
                    ]),

                Builder::make('blocks')
                    ->label('Content blocks')
                    ->collapsed()
                    ->blocks([
                        Block::make('richText')
                            ->label('Rich text')
                            ->schema([
                                TextInput::make('heading')->label('Heading'),
                                Textarea::make('html')
                                    ->label('HTML content')
                                    ->rows(10)
                                    ->helperText('Paste HTML content.'),
                            ]),

                        Block::make('image')
                            ->label('Image')
                            ->schema([
                                FileUpload::make('path')
                                    ->disk('public')
                                    ->directory('pages/blocks')
                                    ->image()
                                    ->required(),
                                TextInput::make('caption'),
                            ]),

                        Block::make('video')
                            ->label('Video')
                            ->schema([
                                // Store uploaded video or poster image (simple MVP)
                                FileUpload::make('path')
                                    ->disk('public')
                                    ->directory('pages/blocks')
                                    ->acceptedFileTypes(['video/mp4', 'video/webm', 'image/jpeg', 'image/png', 'image/webp'])
                                    ->required(),
                                TextInput::make('caption'),
                            ]),

                        Block::make('cta')
                            ->label('Button / CTA')
                            ->schema([
                                TextInput::make('label')->required()->default('Learn more'),
                                TextInput::make('url')->required(),
                            ]),
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

                                    Textarea::make("content.$locale")
                                        ->label("Content ($label)")
                                        ->rows(10)
                                        ->required($locale === $default),

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