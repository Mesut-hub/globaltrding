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
use Filament\Forms\Components\ColorPicker;

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
                    'featured_news' => 'Featured News',
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
                    Block::make('insightsGrid')
                            ->label('Insights Grid')
                            ->schema([
                                TextInput::make('heading')
                                    ->label('Heading')
                                    ->default('Company insights')
                                    ->required(),

                                Select::make('accent')
                                    ->label('Accent / Panel color')
                                    ->options([
                                        'blue' => 'Blue',
                                        'slate' => 'Slate',
                                        'dark' => 'Dark',
                                    ])
                                    ->default('blue')
                                    ->required(),

                                ColorPicker::make('panel_text_color')
                                    ->label('Panel text color (optional)')
                                    ->default('#ffffff'),

                                ColorPicker::make('row2_link_color')
                                    ->label('Row 2 link color (optional)')
                                    ->default('#0ea5e9'),

                                // Top row: 2 tiles
                                \Filament\Schemas\Components\Grid::make(2)
                                    ->schema([
                                        FileUpload::make('top_left_image')
                                            ->label('Top-left image')
                                            ->disk('public')
                                            ->directory('pages/insights')
                                            ->image()
                                            ->required(),

                                        \Filament\Schemas\Components\Grid::make(1)
                                            ->schema([
                                                TextInput::make('top_right_kicker')->label('Top-right kicker')->default('Future-proof insulation'),
                                                TextInput::make('top_right_title')->label('Top-right title')->required(),
                                                Textarea::make('top_right_text')->label('Top-right text')->rows(5),
                                                TextInput::make('top_right_cta_label')->label('Top-right CTA label')->default('Find out more'),
                                                TextInput::make('top_right_cta_url')->label('Top-right CTA URL'),
                                            ]),
                                    ]),

                                // Bottom row: 3 tiles (2 images + 1 panel)
                                \Filament\Forms\Components\Repeater::make('bottom_tiles')
                                    ->label('Bottom row tiles (exactly 3)')
                                    ->helperText('Create exactly 3 tiles: Image, Image, Panel.')
                                    ->minItems(3)
                                    ->maxItems(3)
                                    ->schema([
                                        Select::make('type')
                                            ->options(['image' => 'Image tile', 'panel' => 'Color panel tile'])
                                            ->required(),

                                        FileUpload::make('image')
                                            ->disk('public')
                                            ->directory('pages/insights')
                                            ->image()
                                            ->visible(fn ($get) => $get('type') === 'image')
                                            ->required(fn ($get) => $get('type') === 'image'),

                                        TextInput::make('kicker')
                                            ->label('Kicker')
                                            ->visible(fn ($get) => $get('type') === 'image'),

                                        TextInput::make('title')
                                            ->label('Title')
                                            ->required()
                                            ->visible(fn ($get) => $get('type') === 'image'),

                                        Textarea::make('lead')
                                            ->label('Lead text')
                                            ->rows(3)
                                            ->visible(fn ($get) => $get('type') === 'image'),

                                        TextInput::make('cta_label')
                                            ->label('CTA label')
                                            ->default('Find out more')
                                            ->visible(fn ($get) => $get('type') === 'image'),
                                        
                                        TextInput::make('cta_url')
                                            ->label('CTA URL')
                                            ->visible(fn ($get) => $get('type') === 'image'),
                                        Textarea::make('panel_excerpt')
                                            ->label('Excerpt (panel)')
                                            ->rows(2)
                                            ->visible(fn ($get) => $get('type') === 'panel'),

                                        Textarea::make('panel_body')
                                            ->label('Body text (panel)')
                                            ->rows(4)
                                            ->visible(fn ($get) => $get('type') === 'panel'),

                                        Toggle::make('show_chart')
                                            ->label('Show sparkline chart')
                                            ->default(false)
                                            ->visible(fn ($get) => $get('type') === 'panel'),

                                        \Filament\Forms\Components\Repeater::make('chart_points')
                                            ->label('Chart points (5–12)')
                                            ->minItems(5)
                                            ->maxItems(12)
                                            ->defaultItems(6)
                                            ->schema([
                                                TextInput::make('value')->numeric()->required(),
                                            ])
                                            ->visible(fn ($get) => $get('type') === 'panel' && (bool) $get('show_chart'))
                                            ->columns(3),

                                        Select::make('chart_scale')
                                            ->label('Scale')
                                            ->options(['linear' => 'Linear', 'log' => 'Log'])
                                            ->default('linear')
                                            ->visible(fn ($get) => $get('type') === 'panel' && (bool) $get('show_chart')),

                                        Select::make('chart_mode')
                                            ->label('Mode')
                                            ->options(['absolute' => 'Absolute', 'percent' => 'Percent (0–100)'])
                                            ->default('absolute')
                                            ->visible(fn ($get) => $get('type') === 'panel' && (bool) $get('show_chart')),

                                        Select::make('chart_range')
                                            ->label('Range (label only for now)')
                                            ->options(['daily' => 'Daily', 'weekly' => 'Weekly', 'monthly' => 'Monthly'])
                                            ->default('daily')
                                            ->visible(fn ($get) => $get('type') === 'panel' && (bool) $get('show_chart')),

                                        Toggle::make('chart_auto_minmax')
                                            ->label('Auto min/max')
                                            ->default(true)
                                            ->visible(fn ($get) => $get('type') === 'panel' && (bool) $get('show_chart')),

                                        TextInput::make('chart_min')
                                            ->label('Min (fixed)')
                                            ->numeric()
                                            ->visible(fn ($get) => $get('type') === 'panel' && (bool) $get('show_chart') && ! (bool) $get('chart_auto_minmax')),

                                        TextInput::make('chart_max')
                                            ->label('Max (fixed)')
                                            ->numeric()
                                            ->visible(fn ($get) => $get('type') === 'panel' && (bool) $get('show_chart') && ! (bool) $get('chart_auto_minmax')),
                                    ])
                                    ->columns(2),
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

                    Block::make('featuredNews')
                        ->label('Featured News')
                        ->schema([
                            TextInput::make('title')->label('Section title')->default('Featured News')->required(),
                            Textarea::make('lead')->label('Section lead (optional)')->rows(2),
                            TextInput::make('limit')->label('Max posts')->numeric()->default(3)->minValue(1)->maxValue(12),
                            Toggle::make('show_view_all')->label('Show "View all" link')->default(true),
                            TextInput::make('view_all_label')->label('"View all" label')->default('View all →'),
                        ]),
                 ]),
         ]);
     }
 }