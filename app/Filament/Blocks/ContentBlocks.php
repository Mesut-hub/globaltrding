<?php

namespace App\Filament\Blocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

class ContentBlocks
{
    /**
     * Shared blocks for Pages + Industries (and optionally Products).
     */
    public static function shared(): array
    {
        return [
            Block::make('richText')
                ->label('Rich text')
                ->schema([
                    TextInput::make('title')->label('Title (optional)'),
                    Textarea::make('html')->label('HTML')->rows(8)
                        ->helperText('Paste formatted HTML.'),
                ]),

            Block::make('split')
                ->label('Split (image + text)')
                ->schema([
                    Toggle::make('image_side')->label('Image side')->default('left'),
                    FileUpload::make('image')
                        ->disk('public')
                        ->directory('blocks/split')
                        ->image(),
                    TextInput::make('title')->required(),
                    Textarea::make('html')->label('Body (HTML)')->rows(8),
                    TextInput::make('cta_label'),
                    TextInput::make('cta_url'),
                ]),

            // Add any other blocks you want pages+industries to share here.
        ];
    }
}