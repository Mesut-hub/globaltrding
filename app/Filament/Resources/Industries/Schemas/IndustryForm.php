<?php

namespace App\Filament\Resources\Industries\Schemas;

use App\Filament\Resources\Pages\Schemas\PageBlockBuilder;
use App\Filament\Concerns\HasBlockLocaleTabs;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class IndustryForm
{
    use HasBlockLocaleTabs;
    public static function configure(Schema $schema): Schema
    {
        $locales  = config('locales.supported', ['en']);
        $default  = config('locales.default', 'en');

        return Block::make('colsGrids')
                ->label('4 cols grid')
                ->schema([

            TextInput::make('slug')
                ->required()
                ->maxLength(255)
                ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')
                ->unique(ignoreRecord: true),

            static::blockLocaleTabs('colsGrids_item_lang', [
                    ['name' => 'title_tabs', 'label' => 'Title', 'type' => 'text'],
                    ['name' => 'excerpt_tabs',  'label' => 'Excerpt',  'type' => 'textarea', 'rows' => 2],
                ]),

            FileUpload::make('cover_image_path')
                ->label('Cover image')
                ->disk('public')
                ->directory('industries')
                ->image(),

            Builder::make('blocks')
                ->label('Content blocks')
                ->collapsible()
                ->collapsed()
                ->blocks(PageBlockBuilder::blocks()),

            // ── SEO ──────────────────────────────────────────────────────
            Section::make('SEO')
                ->schema(
                    collect($locales)->flatMap(fn ($loc) => [
                        TextInput::make("seo.title.{$loc}")
                            ->label('SEO Title (' . strtoupper($loc) . ')')
                            ->maxLength(70),
                        Textarea::make("seo.description.{$loc}")
                            ->label('SEO Description (' . strtoupper($loc) . ')')
                            ->rows(2)
                            ->maxLength(160),
                    ])->all()
                )
                ->collapsible()
                ->collapsed(),

            Toggle::make('is_published')->required()->default(true),

            TextInput::make('sort_order')->required()->numeric()->default(0),
        ]);
    }
}