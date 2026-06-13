<?php

namespace App\Filament\Resources\Industries\Schemas;

use Filament\Forms\Components\Builder;
use App\Filament\Resources\Pages\Schemas\PageBlockBuilder;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class IndustryForm
{
    public static function configure(Schema $schema): Schema
    {
        $locales = config('locales.supported', ['en']);
        $default = config('locales.default', 'en');
        
        return $schema->components([
            TextInput::make('slug')
                ->required()
                ->maxLength(255)
                ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')
                ->unique(ignoreRecord: true),

            KeyValue::make('title')
                ->label('Title (multilingual)')
                ->keyLabel('Locale')
                ->valueLabel('Title')
                ->helperText('Use keys: en, tr, ar, fr')
                ->required()
                ->dehydrateStateUsing(function ($state) {
                    // Normalize before save: accept associative or row-list shapes
                    if (is_string($state)) {
                        $decoded = json_decode($state, true);
                        $state = is_array($decoded) ? $decoded : [];
                    }

                    // Convert row-list to associative
                    if (is_array($state) && array_is_list($state)) {
                        $assoc = [];
                        foreach ($state as $row) {
                            if (is_array($row) && isset($row['key'])) {
                                $k = (string) $row['key'];
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
                        // Normalize any weird value shapes to array
                        if (is_string($value)) {
                            $decoded = json_decode($value, true);
                            $value = is_array($decoded) ? $decoded : [];
                        }

                        if (is_array($value) && array_is_list($value)) {
                            $assoc = [];
                            foreach ($value as $row) {
                                if (is_array($row) && isset($row['key'])) {
                                    $k = (string) $row['key'];
                                    $v = (string) ($row['value'] ?? '');
                                    if ($k !== '') $assoc[$k] = $v;
                                }
                            }
                            $value = $assoc;
                        }

                        $title = '';
                        if (is_array($value)) {
                            $title = trim((string) ($value[$default] ?? ''));
                        }

                        if ($title === '') {
                            $fail("Title must include a non-empty '{$default}' value.");
                        }
                    };
                }),

            KeyValue::make('excerpt')
                ->label('Excerpt (multilingual)')
                ->keyLabel('Locale')
                ->valueLabel('Excerpt'),

            FileUpload::make('cover_image_path')
                ->label('Cover image')
                ->disk('public')
                ->directory('industries')
                ->image(),

            Builder::make('blocks')
                ->label('Content blocks')
                ->collapsible()
                ->collapsed()
                ->blocks(
                    PageBlockBuilder::blocks(),
                ),

            // Add SEO section
            \Filament\Schemas\Components\Section::make('SEO')
                ->schema(
                    collect(config('locales.supported', ['en']))->flatMap(function (string $locale) use ($default) {
                        $lbl = strtoupper($locale);
                        return [
                            \Filament\Forms\Components\TextInput::make("seo.title.{$locale}")
                                ->label("SEO Title ({$lbl})")
                                ->maxLength(70),
                            \Filament\Forms\Components\Textarea::make("seo.description.{$locale}")
                                ->label("SEO Description ({$lbl})")
                                ->rows(2)
                                ->maxLength(160),
                        ];
                    })->all()
                )
                ->collapsible()
                ->collapsed(),
            
            Toggle::make('is_published')
                ->required()
                ->default(true),

            TextInput::make('sort_order')
                ->required()
                ->numeric()
                ->default(0),
        ]);
    }
}