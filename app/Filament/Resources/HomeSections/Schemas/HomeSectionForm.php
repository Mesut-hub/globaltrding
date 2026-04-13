<?php

namespace App\Filament\Resources\HomeSections\Schemas;

use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class HomeSectionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('key')
                ->required()
                ->unique(ignoreRecord: true)
                ->options([
                    'hero' => 'Hero',
                    'market_belt' => 'Market belt',
                    'industries' => 'Industries (slider)',
                    'insights' => 'Company insights',
                    'trending' => 'Trending topics',
                    'sustainability' => 'Sustainability',
                    'people' => 'People',
                ]),

            Toggle::make('is_active')
                ->required()
                ->default(true),

            TextInput::make('sort_order')
                ->required()
                ->numeric()
                ->default(0),

            Builder::make('blocks')
                ->label('Blocks')
                ->collapsed()
                ->blocks([
                    // HERO block (video OR image)
                    Block::make('hero')
                        ->label('Hero (video or image)')
                        ->schema([
                            Select::make('media_type')
                                ->options([
                                    'video' => 'Video',
                                    'image' => 'Image',
                                ])
                                ->default('video')
                                ->required(),

                            FileUpload::make('media_path')
                                ->label('Hero media')
                                ->disk('public')
                                ->directory('home/hero')
                                ->maxSize((auth()->user()?->maxUploadMb() ?? 150) * 1024)
                                ->acceptedFileTypes(['video/mp4', 'video/webm', 'image/jpeg', 'image/png', 'image/webp'])
                                ->required(),

                            FileUpload::make('poster_path')
                                ->label('Hero poster (fallback image)')
                                ->disk('public')
                                ->directory('home/hero')
                                ->image()
                                ->maxSize(51200),

                            KeyValue::make('title')
                                ->label('Title (en,tr,ar,fr)')
                                ->keyLabel('Locale')
                                ->valueLabel('Title')
                                ->required(),

                            KeyValue::make('subtitle')
                                ->label('Subtitle (en,tr,ar,fr)')
                                ->keyLabel('Locale')
                                ->valueLabel('Subtitle'),

                            KeyValue::make('cta1_label')
                                ->label('CTA1 label (en,tr,ar,fr)')
                                ->keyLabel('Locale')
                                ->valueLabel('Label'),

                            TextInput::make('cta1_url')->label('CTA1 URL'),

                            KeyValue::make('cta2_label')
                                ->label('CTA2 label (en,tr,ar,fr)')
                                ->keyLabel('Locale')
                                ->valueLabel('Label'),

                            TextInput::make('cta2_url')->label('CTA2 URL'),

                            TextInput::make('min_h')
                                ->label('Hero height (CSS, e.g. 90vh / 100vh)')
                                ->default('90vh'),

                            TextInput::make('text_offset_px')
                                ->label('Text offset from top (px)')
                                ->numeric()
                                ->default(290),

                            TextInput::make('overlay_top')
                                ->label('Overlay top opacity (0-1)')
                                ->default('0.45'),

                            TextInput::make('overlay_mid')
                                ->label('Overlay mid opacity (0-1)')
                                ->default('0.15'),

                            TextInput::make('overlay_bottom')
                                ->label('Overlay bottom opacity (0-1)')
                                ->default('0.55'),
                        ]),

                    // Market belt (simple fixed block)
                    Block::make('market_belt')
                        ->label('Market belt')
                        ->schema([
                            Toggle::make('enabled')->default(true),
                        ]),

                    // Industries slider (render from Industries DB)
                    Block::make('industries_slider')
                        ->label('Industries slider (from Industries CMS)')
                        ->schema([
                            KeyValue::make('title')
                                ->label('Section title (en,tr,ar,fr)')
                                ->keyLabel('Locale')
                                ->valueLabel('Title'),

                            TextInput::make('view_all_url')
                                ->label('View all industries URL')
                                ->default('/{locale}/industries'),
                        ]),

                    // Simple CTA block
                    Block::make('cta')
                        ->label('CTA section')
                        ->schema([
                            KeyValue::make('title')
                                ->label('Title (en,tr,ar,fr)')
                                ->keyLabel('Locale')
                                ->valueLabel('Title')
                                ->required(),

                            KeyValue::make('text')
                                ->label('Text (en,tr,ar,fr)')
                                ->keyLabel('Locale')
                                ->valueLabel('Text'),

                            KeyValue::make('button_label')
                                ->label('Button label (en,tr,ar,fr)')
                                ->keyLabel('Locale')
                                ->valueLabel('Label'),

                            TextInput::make('button_url')->required(),
                        ]),

                    // Cards grid (insights, people, sustainability can all use this)
                    Block::make('cards')
                        ->label('Cards grid')
                        ->schema([
                            KeyValue::make('title')
                                ->label('Section title (en,tr,ar,fr)')
                                ->keyLabel('Locale')
                                ->valueLabel('Title'),

                            \Filament\Forms\Components\Repeater::make('items')
                                ->schema([
                                    FileUpload::make('image_path')
                                        ->disk('public')
                                        ->directory('home/cards')
                                        ->image(),

                                    KeyValue::make('title')
                                        ->label('Card title (en,tr,ar,fr)')
                                        ->keyLabel('Locale')
                                        ->valueLabel('Title')
                                        ->required(),

                                    KeyValue::make('text')
                                        ->label('Card text (en,tr,ar,fr)')
                                        ->keyLabel('Locale')
                                        ->valueLabel('Text'),

                                    TextInput::make('url')->label('Card URL'),
                                ])
                                ->defaultItems(3)
                                ->collapsed(),
                        ]),

                    // Trending topics chips
                    Block::make('trending_topics')
                        ->label('Trending topics')
                        ->schema([
                            KeyValue::make('title')
                                ->label('Title (en,tr,ar,fr)')
                                ->keyLabel('Locale')
                                ->valueLabel('Title'),

                            FileUpload::make('background_image_path')
                                ->label('Background image')
                                ->disk('public')
                                ->directory('home/trending')
                                ->image(),

                            \Filament\Forms\Components\Repeater::make('topics')
                                ->label('Cards (exactly 5 in order: IG, IG, LI big, LI, LI)')
                                ->minItems(5)
                                ->maxItems(5)
                                ->defaultItems(5)
                                ->schema([
                                    Select::make('source')
                                        ->options([
                                            'instagram' => 'Instagram',
                                            'linkedin' => 'LinkedIn',
                                        ])
                                        ->required()
                                        ->default('linkedin'),

                                    FileUpload::make('image_path')
                                        ->label('Card image')
                                        ->disk('public')
                                        ->directory('home/trending/cards')
                                        ->image(),

                                    KeyValue::make('title')
                                        ->label('Title (en,tr,ar,fr)')
                                        ->keyLabel('Locale')
                                        ->valueLabel('Title'),

                                    KeyValue::make('text')
                                        ->label('Text (en,tr,ar,fr)')
                                        ->keyLabel('Locale')
                                        ->valueLabel('Text')
                                        ->required(),

                                    TextInput::make('profile_name')
                                        ->label('Profile name (e.g. globaltrding)')
                                        ->default('Globaltrding'),

                                    TextInput::make('time_ago')
                                        ->label('Time ago (e.g. 7 days ago)')
                                        ->default('—'),

                                    TextInput::make('original_url')
                                        ->label('Original post URL')
                                        ->url(),

                                    TextInput::make('privacy_url')
                                        ->label('Privacy policy URL (supports /{locale}/...)')
                                        ->default('/{locale}/pages/privacy-policy'),
                                ])
                                ->collapsed(false)
                                ->itemLabel(fn (array $state): ?string => $state['source'] ?? 'card'),
                         ]),
                 ]),
         ]);
     }
 }