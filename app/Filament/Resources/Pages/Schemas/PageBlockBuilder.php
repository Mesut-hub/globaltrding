<?php

namespace App\Filament\Resources\Pages\Schemas;

use App\Filament\Concerns\HasBlockLocaleTabs;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\textarea;
use Filament\Schemas\Schema;

/**
 * Defines all Filament Builder blocks for the Pages and HomeSections resources.
 * Text fields inside each block use locale tabs (via HasBlockLocaleTabs) so the
 * rendered output is translated through the $t() closure in render.blade.php.
 */
class PageBlockBuilder
{
    use HasBlockLocaleTabs;

    public static function blocks(): array
    {
        return [
            static::heroBlock(),
            static::sectionHeadingBlock(),
            static::insightsGridBlock(),
            static::cardsCarouselBlock(),
            static::splitBlock(),
            static::cardsBlock(),
            static::metricsBlock(),
            static::mediaTextBlock(),
            static::mediaTextLinks3Block(),
            static::dropdownLinksBlock(),
            static::timelineBlock(),
            static::ctaStatsBlock(),
            static::supplyConcentrationBarsBlock(),
            static::richText2Block(),
            static::pullQuoteBlock(),
            static::overlayCarouselBlock(),
            static::fullWidthCardsBlock(),
            static::specTableBlock(),
            ...static::homeOnlyBlocks(),
            ...static::industryOnlyBlocks(),
        ];
    }

    protected static function fontSizeOptions(): array
    {
        return [
            'text-xs'   => 'XS – 10px',
            'text-xs'   => 'XS – 12px',
            'text-sm'   => 'SM – 14px',
            'text-base' => 'Base – 16px',
            'text-lg'   => 'LG – 18px',
            'text-xl'   => 'XL – 20px',
            'text-2xl'  => '2XL – 24px',
            'text-3xl'  => '3XL – 30px',
            'text-4xl'  => '4XL – 36px',
            'text-5xl'  => '5XL – 48px',
            'text-6xl'  => '6XL – 60px',
        ];
    }

    // ── Hero ─────────────────────────────────────────────────────────────────

    private static function heroBlock(): Block
    {
        return Block::make('hero')
            ->label('Hero — full-width banner with slides')
            ->schema([
                Select::make('media_type')
                                ->options([
                                    'video' => 'Video',
                                    'image' => 'Image',
                                    'multimedia' => 'Multimedia (video + image)',
                                ])
                                ->default('image')
                                ->helperText('Select the media type BEFORE uploading. '
                                    . 'Images → use "Background image(s)" upload. '
                                    . 'Video → use "Background video" upload.'),

                Select::make('height')
                    ->options(['screen' => 'Full screen', 'xl' => 'XL', 'lg' => 'LG'])
                    ->default('screen'),

                Select::make('content_position')
                    ->label('Content position')
                    ->options(['left' => 'Left', 'center' => 'Center', 'right' => 'Right'])
                    ->default('left'),

                Select::make('content_align')
                    ->label('Text alignment')
                    ->options(['left' => 'Left', 'center' => 'Center', 'right' => 'Right'])
                    ->default('left'),

                ColorPicker::make('overlay_color')->label('Overlay colour')->default('#000000'),

                TextInput::make('overlay_opacity')
                    ->label('Overlay opacity (0–1)')->default('0.45')->numeric(),

                TextInput::make('content_max_width')->label('Content max width (px)')->default('760')->numeric(),
                TextInput::make('content_offset_x')->label('Content offset X (px)')->default('140')->numeric(),
                TextInput::make('content_offset_y')->label('Content offset Y (px)')->default('-90')->numeric(),

                Select::make('title_size')
                    ->options(['md' => 'Medium', 'lg' => 'Large', 'xl' => 'XL'])
                    ->default('xl'),

                Select::make('lead_size')
                    ->options(['sm' => 'Small', 'md' => 'Medium', 'lg' => 'Large'])
                    ->default('md'),

                Toggle::make('autoplay')->default(true),
                TextInput::make('interval_ms')->label('Autoplay interval (ms)')->default('4500')->numeric(),
                Toggle::make('pause_on_hover')->default(true),

                FileUpload::make('images')
                    ->label('Background image(s)')
                    ->disk('public')
                    ->directory('pages/hero')
                    ->image()
                    ->multiple()
                    ->reorderable(),

                FileUpload::make('video')
                    ->label('Background video (mp4/webm)')
                    ->disk('public')
                    ->directory('pages/hero')
                    ->maxSize((auth()->user()?->maxUploadMb() ?? 150) * 1024)
                    ->acceptedFileTypes(['video/mp4', 'video/webm', 'image/jpeg', 'image/png', 'image/webp']),

                // ── Slides (text per slide, per locale) ───────────────────
                Repeater::make('slides')
                    ->label('Slides (text content)')
                    ->minItems(1)
                    ->schema([
                        TextInput::make('cta1_url')->label('CTA1 URL'),
                        TextInput::make('cta2_url')->label('CTA2 URL'),
                        TextInput::make('cta3_url')->label('CTA3 URL'),

                        static::blockLocaleTabs('hero_slide_lang', [
                            ['name' => 'kicker', 'label' => 'Kicker', 'type' => 'html', 'rows' => 2],
                            ['name' => 'title',  'label' => 'Title',  'type' => 'html', 'rows' => 2],
                            ['name' => 'lead',   'label' => 'Lead',   'type' => 'html', 'rows' => 3],
                            ['name' => 'cta1_label', 'label' => 'CTA1 label', 'type' => 'text'],
                            ['name' => 'cta2_label', 'label' => 'CTA2 label', 'type' => 'text'],
                            ['name' => 'cta3_label', 'label' => 'CTA3 label', 'type' => 'text'],
                        ]),
                    ]),
            ]);
    }

    // ── Section Heading ───────────────────────────────────────────────────────

    private static function sectionHeadingBlock(): Block
    {
        return Block::make('sectionHeading')
            ->label('Section heading')
            ->schema([
                static::blockLocaleTabs('sh_lang', [
                    ['name' => 'title', 'label' => 'Title', 'type' => 'text'],
                    ['name' => 'lead',  'label' => 'Lead',  'type' => 'textarea', 'rows' => 2],
                ]),
            ]);
    }

    // ── Insights Grid ─────────────────────────────────────────────────────────

    private static function insightsGridBlock(): Block
    {
        return Block::make('insightsGrid')
            ->label('Insights grid (2-row panel layout)')
            ->schema([
                Select::make('accent')
                    ->label('Accent / Panel color')
                    ->options([
                        'blue' => 'Blue', 
                        'dark' => 'Dark', 
                        'slate' => 'Slate'
                    ])
                    ->default('blue')
                    ->required(),

                ColorPicker::make('panel_text_color')
                    ->label('Panel text color (optional)')
                    ->default('#ffffff'),
                ColorPicker::make('row2_link_color')
                    ->label('Row 2 link color (optional)')
                    ->default('#0ea5e9'),

                FileUpload::make('top_left_image')
                    ->label('Top-left image')
                    ->disk('public')
                    ->directory('pages/insights')
                    ->image(),

                // Top-right panel text
                static::blockLocaleTabs('ig_top_lang', [
                    ['name' => 'heading',           'label' => 'Section heading',  'type' => 'text'],
                    ['name' => 'top_right_kicker',  'label' => 'Top-right kicker', 'type' => 'text'],
                    ['name' => 'top_right_title',   'label' => 'Top-right title',  'type' => 'text'],
                    ['name' => 'top_right_text',    'label' => 'Top-right text',   'type' => 'textarea', 'rows' => 3],
                    ['name' => 'top_right_cta_label','label' => 'Top-right CTA',   'type' => 'text'],
                ]),

                TextInput::make('top_right_cta_url')->label('Top-right CTA URL'),

                // Bottom tiles
                Repeater::make('bottom_tiles')
                    ->label('Bottom tiles')
                    ->helperText('Create exactly 3 tiles: Image, Image, Image/Panel.')
                    ->schema([
                        Select::make('type')
                            ->options(['image' => 'Image tile', 'panel' => 'Panel tile'])
                            ->default('image')->required(),

                        FileUpload::make('image')
                            ->disk('public')->directory('pages/insights')->image()
                            ->visible(fn ($get) => $get('type') === 'image'),

                        static::blockLocaleTabs('ig_tile_lang', [
                            ['name' => 'kicker',        'label' => 'Kicker',        'type' => 'text'],
                            ['name' => 'title',         'label' => 'Title',         'type' => 'text'],
                            ['name' => 'lead',          'label' => 'Lead',          'type' => 'textarea', 'rows' => 2],
                            ['name' => 'cta_label',     'label' => 'CTA label',     'type' => 'text'],
                            ['name' => 'panel_excerpt', 'label' => 'Panel excerpt', 'type' => 'textarea', 'rows' => 2],
                            ['name' => 'panel_body',    'label' => 'Panel body',    'type' => 'textarea', 'rows' => 3],
                        ]),

                        TextInput::make('cta_url')->label('CTA URL'),
                        Toggle::make('show_chart')
                            ->label('Show sparkline chart')
                            ->default(false)
                            ->visible(fn ($get) => $get('type') === 'panel'),

                        Select::make('chart_source')
                            ->label('Chart data source')
                            ->options([
                                'manual' => 'Manual points',
                                'url_json' => 'URL (JSON)',
                                'market_instrument' => 'Market instrument (DB)',
                            ])
                            ->default('manual')
                            ->visible(fn ($get) => $get('type') === 'panel' && (bool) $get('show_chart')),

                        TextInput::make('chart_url')
                            ->label('Chart data URL (JSON)')
                            ->helperText('Expected JSON: either [1,2,3] or [{value:1},{value:2}]')
                            ->url()
                            ->visible(fn ($get) => $get('type') === 'panel' && (bool) $get('show_chart') && $get('chart_source') === 'url_json'),

                        Select::make('chart_instrument')
                            ->label('Market instrument')
                            ->options(fn () => \App\Models\MarketInstrument::query()->where('is_active', true)->orderBy('slug')->pluck('slug','slug')->all())
                            ->searchable()
                            ->visible(fn ($get) => $get('type') === 'panel' && (bool) $get('show_chart') && $get('chart_source') === 'market_instrument'),

                        TextInput::make('chart_days')
                            ->label('Days (5–120)')
                            ->numeric()
                            ->default(14)
                            ->visible(fn ($get) => $get('type') === 'panel' && (bool) $get('show_chart') && $get('chart_source') === 'market_instrument'),

                        Repeater::make('chart_points')
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
            ]);
    }

    // ── Cards Carousel ────────────────────────────────────────────────────────

    private static function cardsCarouselBlock(): Block
    {
        return Block::make('cardsCarousel')
            ->label('Cards carousel')
            ->schema([
                Select::make('bg')->options(['white' => 'White', 'slate' => 'Slate', 'dark' => 'Dark'])->default('white'),
                Select::make('title_size')->options(['md' => 'MD', 'lg' => 'LG'])->default('lg'),
                Select::make('text_size')->options(['sm' => 'SM', 'md' => 'MD'])->default('md'),
                Toggle::make('autoplay')->default(false),
                TextInput::make('autoplay_ms')->label('Autoplay ms')->default('4500')->numeric(),
                Toggle::make('pause_on_hover')->default(true),

                static::blockLocaleTabs('cc_lang', [
                    ['name' => 'title', 'label' => 'Heading', 'type' => 'text'],
                    ['name' => 'lead',  'label' => 'Lead',    'type' => 'textarea', 'rows' => 2],
                ]),

                Repeater::make('items')->label('Cards')->minItems(2)->schema([
                    FileUpload::make('image')->disk('public')->directory('pages/carousel')->image(),
                    TextInput::make('url')->label('Card URL'),

                    static::blockLocaleTabs('cc_item_lang', [
                        ['name' => 'title', 'label' => 'Title', 'type' => 'text'],
                        ['name' => 'text',  'label' => 'Text',  'type' => 'textarea', 'rows' => 2],
                    ]),
                ]),
            ]);
    }

    // ── Split ─────────────────────────────────────────────────────────────────

    private static function splitBlock(): Block
    {
        return Block::make('split')
            ->label('Split — image + text')
            ->schema([
                Select::make('image_side')
                    ->options(['left' => 'Image left', 'right' => 'Image right'])
                    ->default('left'),

                FileUpload::make('image')->disk('public')->directory('pages/split')->image(),

                static::blockLocaleTabs('split_lang', [
                    ['name' => 'title',     'label' => 'Title',    'type' => 'text'],
                    ['name' => 'html',      'label' => 'HTML body','type' => 'html', 'rows' => 8],
                    ['name' => 'cta_label', 'label' => 'CTA',      'type' => 'text'],
                ]),

                TextInput::make('cta_url')->label('CTA URL'),
            ]);
    }

    // ── Cards ─────────────────────────────────────────────────────────────────

    private static function cardsBlock(): Block
    {
        return Block::make('cards')
            ->label('Cards grid (3-up)')
            ->schema([
                static::blockLocaleTabs('ind_slider_meta_lang', [
                    ['name' => 'kicker',        'label' => 'Section kicker',   'type' => 'text'],
                    ['name' => 'heading_tabs',  'label' => 'Section heading',  'type' => 'text'],
                    ['name' => 'subtitle_tabs', 'label' => 'Section subtitle', 'type' => 'text'],
                ]),
                ColorPicker::make('title_color')->label('Title color')->default('#F3F4F6'),
                static::blockLocaleTabs('cards_lang', [
                    ['name' => 'title', 'label' => 'Heading', 'type' => 'text'],
                ]),
                Select::make('section_type')
                    ->label('Section type')
                    ->options(['full' => 'Full width', 'nofull' => 'No full width'])
                    ->default('nofull'),
                ColorPicker::make('section_bg_color')->label('Section background color')->default('#F3F4F6'),
                ColorPicker::make('nav_btn_color')
                                    ->label('Prev / Next button color')
                                    ->default('#DAA520'),
                FileUpload::make('bg_image_path')
                    ->label('Section background image')
                    ->disk('public')
                    ->directory('pages/cards')
                    ->image(),
                ColorPicker::make('bg_overlay_color')
                    ->label('Background overlay color')
                    ->default('#8B0000CC'),
                TextInput::make('view_all_url')
                    ->label('View-all URL (supports {locale})')
                    ->default('/{locale}/en'),
                Repeater::make('items')->label('Cards')->minItems(1)->schema([
                    FileUpload::make('image')->disk('public')->directory('pages/cards')->image(),
                    TextInput::make('url')->label('Card URL'),

                    static::blockLocaleTabs('cards_item_lang', [
                        ['name' => 'title', 'label' => 'Title', 'type' => 'text'],
                        ['name' => 'text',  'label' => 'Text',  'type' => 'textarea', 'rows' => 2],
                        ['name' => 'body_html', 'label' => 'Body HTML', 'type' => 'html',     'rows' => 8],
                        ['name' => 'cta_label', 'label' => 'CTA label', 'type' => 'text'],
                    ]),
                ]),
            ]);
    }

    // ── Metrics ───────────────────────────────────────────────────────────────

    public static function metricsBlock(): Block
    {
        return Block::make('metrics')
            ->label('Metrics / Statistics')
            ->schema([

                \Filament\Schemas\Components\Grid::make(2)->schema([
                    Select::make('layout')
                        ->label('Layout')
                        ->options(['contained' => 'Contained (max-w-7xl)', 'full_width' => 'Full Width'])
                        ->default('contained'),
                    \Filament\Forms\Components\Toggle::make('animate')
                        ->label('Animate numbers on scroll')
                        ->default(true),
                ]),

                \Filament\Schemas\Components\Grid::make(2)->schema([
                    \Filament\Forms\Components\ColorPicker::make('section_bg_color')
                        ->label('Section background color')
                        ->default('#f8fafc'),
                    \Filament\Forms\Components\ColorPicker::make('heading_color')
                        ->label('Section heading color')
                        ->default('#0f172a'),
                ]),

                \Filament\Schemas\Components\Grid::make(3)->schema([
                    \Filament\Forms\Components\ColorPicker::make('item_bg_color')
                        ->label('Metric card background')
                        ->default('#ffffff'),
                    \Filament\Forms\Components\ColorPicker::make('value_color')
                        ->label('Value color')
                        ->default('#0f172a'),
                    \Filament\Forms\Components\ColorPicker::make('label_color')
                        ->label('Label color')
                        ->default('#64748b'),
                ]),

                \Filament\Schemas\Components\Section::make('Card font sizes')
                    ->collapsed()
                    ->schema([
                        \Filament\Schemas\Components\Grid::make(4)->schema([
                            Select::make('heading_size')
                                ->label('Heading')
                                ->options(static::fontSizeOptions())
                                ->default('text-xl'),
                            Select::make('value_size')
                                ->label('Value')
                                ->options(static::fontSizeOptions())
                                ->default('text-3xl'),
                            Select::make('label_size')
                                ->label('Label')
                                ->options(static::fontSizeOptions())
                                ->default('text-sm'),
                        ]),
                    ]),

                static::blockLocaleTabs('metrics_heading_lang', [
                    ['name' => 'title', 'label' => 'Section heading (optional)', 'type' => 'text'],
                ]),

                Repeater::make('items')
                    ->label('Metric items')
                    ->minItems(1)
                    ->schema([
                        TextInput::make('value')
                            ->label('Value (e.g. 250+ or 98.5)')
                            ->required(),
                        static::blockLocaleTabs('metrics_item_lang', [
                            ['name' => 'label', 'label' => 'Label', 'type' => 'text'],
                        ]),
                    ])
                    ->columns(1)
                    ->collapsible(),
            ]);
    }

    // ── Media Text ────────────────────────────────────────────────────────────

    private static function mediaTextBlock(): Block
    {
        return Block::make('mediaText')
            ->label('Media + Text')
            ->schema([
                Select::make('media_side')
                    ->options(['left' => 'Media left', 'right' => 'Media right'])
                    ->default('left'),

                Select::make('media_type')
                    ->options(['image' => 'Image', 'video' => 'Video'])
                    ->default('image'),

                Select::make('media_width')
                    ->label('Column ratio')
                    ->options(['30-70' => '30/70', '40-60' => '40/60', '50-50' => '50/50', '60-40' => '60/40', '70-30' => '70/30'])
                    ->default('50-50'),

                TextInput::make('media_max_h')->label('Media max height (px)')->numeric(),

                FileUpload::make('image')
                    ->disk('public')->directory('pages/media-text')->image()
                    ->visible(fn ($get) => $get('media_type') === 'image'),

                FileUpload::make('video')
                    ->disk('public')->directory('pages/media-text')
                    ->acceptedFileTypes(['video/mp4', 'video/webm'])
                    ->visible(fn ($get) => $get('media_type') === 'video'),

                FileUpload::make('poster')
                    ->disk('public')->directory('pages/media-text')->image()
                    ->visible(fn ($get) => $get('media_type') === 'video'),

                static::blockLocaleTabs('mt_lang', [
                    ['name' => 'kicker',     'label' => 'Kicker',     'type' => 'text'],
                    ['name' => 'title',     'label' => 'Title',     'type' => 'text'],
                    ['name' => 'excerpt',   'label' => 'Excerpt',   'type' => 'textarea', 'rows' => 2],
                    ['name' => 'body_html', 'label' => 'Body HTML', 'type' => 'html',     'rows' => 8],
                    ['name' => 'cta_label', 'label' => 'CTA label', 'type' => 'text'],
                ]),

                TextInput::make('cta_url')->label('CTA URL'),
            ]);
    }

    // ── Media Text Links 3 ────────────────────────────────────────────────────

    private static function mediaTextLinks3Block(): Block
    {
        return Block::make('mediaTextLinks3')
            ->label('Media | Text | Links (3 columns)')
            ->schema([
                Select::make('layout')
                    ->options([
                        'media-text-links' => 'Media | Text | Links',
                        'links-media-text' => 'Links | Media | Text',
                        'links-text-media' => 'Links | Text | Media',
                        'text-media-links' => 'Text | Media | Links',
                    ])
                    ->default('media-text-links')->required(),

                Select::make('media_type')
                    ->options(['image' => 'Image', 'video' => 'Video'])->default('image'),

                TextInput::make('media_width')->label('Media col % (30-40)')->default('35')->numeric(),
                TextInput::make('text_width')->label('Text col % (30-40)')->default('35')->numeric(),
                TextInput::make('links_width')->label('Links col % (30-40)')->default('30')->numeric(),
                TextInput::make('media_max_h')->label('Media max height (px)')->numeric(),

                ColorPicker::make('links_pad_color')->label('Links panel background')->default('#ffffff'),
                ColorPicker::make('links_row_color')->label('Links hint text colour')->default('#0ea5e9'),

                FileUpload::make('image')
                    ->disk('public')->directory('pages/mtl3')->image()
                    ->visible(fn ($get) => $get('media_type') === 'image'),

                FileUpload::make('video')
                    ->disk('public')->directory('pages/mtl3')
                    ->acceptedFileTypes(['video/mp4', 'video/webm'])
                    ->visible(fn ($get) => $get('media_type') === 'video'),

                FileUpload::make('poster')
                    ->disk('public')->directory('pages/mtl3')->image()
                    ->visible(fn ($get) => $get('media_type') === 'video'),

                static::blockLocaleTabs('mtl3_lang', [
                    ['name' => 'title',       'label' => 'Title',        'type' => 'text'],
                    ['name' => 'excerpt',     'label' => 'Excerpt',      'type' => 'textarea', 'rows' => 2],
                    ['name' => 'body_html',   'label' => 'Body HTML',    'type' => 'html',     'rows' => 6],
                    ['name' => 'cta_label',   'label' => 'CTA label',    'type' => 'text'],
                    ['name' => 'links_title', 'label' => 'Links heading','type' => 'text'],
                ]),

                TextInput::make('cta_url')->label('CTA URL'),

                Repeater::make('links')->label('Link rows')->minItems(0)->schema([
                    TextInput::make('url')->label('URL'),
                    TextInput::make('linksNo')->label('Number / badge (optional)'),
                    Select::make('target')
                        ->options(['_self' => '_self', '_blank' => '_blank'])->default('_self'),

                    static::blockLocaleTabs('mtl3_link_lang', [
                        ['name' => 'label', 'label' => 'Link label', 'type' => 'text'],
                        ['name' => 'hint',  'label' => 'Hint text',  'type' => 'text'],
                    ]),
                ])->columns(2),
            ]);
    }

    // ── Dropdown Links (accordion) ────────────────────────────────────────────

    private static function dropdownLinksBlock(): Block
    {
        return Block::make('dropdownLinks')
            ->label('Dropdown links (accordion)')
            ->schema([
                static::blockLocaleTabs('dl_lang', [
                    ['name' => 'kicker', 'label' => 'Section kicker', 'type' => 'text'],
                    ['name' => 'heading', 'label' => 'Section heading', 'type' => 'text'],
                ]),

                Repeater::make('items')->label('Accordion items')->minItems(1)->schema([
                    static::blockLocaleTabs('dl_item_lang', [
                        ['name' => 'title',     'label' => 'Accordion title', 'type' => 'text'],
                        ['name' => 'content',   'label' => 'Short content',   'type' => 'textarea', 'rows' => 2],
                        ['name' => 'link_label','label' => 'Link label',      'type' => 'text'],
                        ['name' => 'row_title', 'label' => 'Inner panel title','type' => 'text'],
                        ['name' => 'excerpt',   'label' => 'Inner excerpt',   'type' => 'textarea', 'rows' => 2],
                        ['name' => 'body_html', 'label' => 'Inner HTML',      'type' => 'html',     'rows' => 6],
                        ['name' => 'cta_label', 'label' => 'Inner CTA',       'type' => 'text'],
                    ]),

                    TextInput::make('link_url')->label('Link URL'),
                    TextInput::make('cta_url')->label('Inner CTA URL'),

                    Select::make('target')
                        ->options(['_self' => '_self', '_blank' => '_blank'])->default('_self'),

                    Select::make('media_side')
                        ->options(['left' => 'Media left', 'right' => 'Media right'])->default('left'),

                    Select::make('media_type')
                        ->options(['image' => 'Image', 'video' => 'Video'])->default('image'),

                    Select::make('media_width')
                        ->options(['0-100' => '0/100', '30-70' => '30/70', '40-60' => '40/60', '50-50' => '50/50', '60-40' => '60/40', '70-30' => '70/30', '100-0' => '100/0',])
                        ->default('50-50'),

                    FileUpload::make('image')
                        ->disk('public')->directory('pages/accordion')->image()
                        ->visible(fn ($get) => $get('media_type') === 'image'),

                    FileUpload::make('video')
                        ->disk('public')->directory('pages/accordion')
                        ->acceptedFileTypes(['video/mp4', 'video/webm'])
                        ->visible(fn ($get) => $get('media_type') === 'video'),

                    FileUpload::make('poster')
                        ->disk('public')->directory('pages/accordion')->image()
                        ->visible(fn ($get) => $get('media_type') === 'video'),
                ]),
            ]);
    }

    // ── Timeline ──────────────────────────────────────────────────────────────────

    private static function timelineBlock(): Block
    {
        return Block::make('timeline')
            ->label('Timeline — vertical history / milestones')
            ->schema([
                static::blockLocaleTabs('tl_header_lang', [
                    ['name' => 'kicker',  'label' => 'Section kicker (e.g. A HISTORY OF EXPANSION)', 'type' => 'text'],
                    ['name' => 'heading', 'label' => 'Section heading', 'type' => 'text'],
                ]),

                Repeater::make('items')
                    ->label('Timeline items')
                    ->minItems(1)
                    ->schema([
                        TextInput::make('year')
                            ->label('Year')
                            ->required()
                            ->maxLength(4)
                            ->placeholder('2001'),

                        Toggle::make('dot_filled')
                            ->label('Filled dot (most prominent milestone)')
                            ->default(false),

                        static::blockLocaleTabs('tl_item_lang', [
                            ['name' => 'category', 'label' => 'Era / category label (e.g. FOUNDED)',   'type' => 'text'],
                            ['name' => 'title',    'label' => 'Item title (bold heading)',              'type' => 'text'],
                            ['name' => 'body',     'label' => 'Body paragraph',                        'type' => 'textarea', 'rows' => 4],
                        ]),
                    ])
                    ->itemLabel(fn (array $state): ?string => implode(' — ', array_filter([
                        $state['year'] ?? null,
                        is_array($state['category'] ?? null)
                            ? ($state['category']['en'] ?? null)
                            : ($state['category'] ?? null),
                    ])))
                    ->collapsible()
                    ->columns(1),
            ]);
    }

    // ── CTA with stats ────────────────────────────────────────────────────────────

    private static function ctaStatsBlock(): Block
    {
        return Block::make('ctaStats')
            ->label('CTA panel with stats row')
            ->schema([
                Select::make('section_width')
                    ->label('Section background width')
                    ->options([
                        'contained' => 'Contained (current — centered, max width)',
                        'full'      => 'Full width (edge-to-edge)',
                    ])
                    ->default('contained')
                    ->helperText('Full width makes the dark panel itself stretch edge-to-edge; its content stays centered and readable.')
                    ->native(false),

                static::blockLocaleTabs('cs_main_lang', [
                    ['name' => 'heading',  'label' => 'Heading',                    'type' => 'text'],
                    ['name' => 'subtitle', 'label' => 'Subtitle (1–2 lines)',        'type' => 'textarea', 'rows' => 2],
                ]),

                Repeater::make('buttons')
                    ->label('CTA buttons (max 4)')
                    ->minItems(1)
                    ->maxItems(4)
                    ->schema([
                        static::blockLocaleTabs('cs_btn_lang', [
                            ['name' => 'label', 'label' => 'Button label', 'type' => 'text'],
                        ]),
                        TextInput::make('url')->label('URL')->required(),
                    ])
                    ->itemLabel(fn (array $state): ?string =>
                        is_array($state['label'] ?? null)
                            ? ($state['label']['en'] ?? null)
                            : ($state['label'] ?? null)
                    )
                    ->collapsible()
                    ->columns(1),

                Repeater::make('stats')
                    ->label('Stats badges (below divider — max 8)')
                    ->minItems(1)
                    ->maxItems(8)
                    ->schema([
                        Select::make('icon')
                            ->label('Icon')
                            ->options([
                                'location' => '📍 Location pin',
                                'calendar' => '📅 Calendar',
                                'globe'    => '🌐 Globe',
                                'shield'   => '🛡️ Shield / badge',
                                'clock'    => '⏰ Clock',
                                'star'     => '⭐ Star',
                                'check'    => '✅ Check',
                                'users'    => '👥 Users / team',
                                'db'       => '⛃ database / servers',
                                'cube'     => '🧊 Cube',
                            ])
                            ->default('globe')
                            ->required(),

                        static::blockLocaleTabs('cs_stat_lang', [
                            ['name' => 'text', 'label' => 'Stat text (e.g. 40+ countries served)', 'type' => 'text'],
                        ]),
                    ])
                    ->itemLabel(fn (array $state): ?string =>
                        is_array($state['text'] ?? null)
                            ? ($state['text']['en'] ?? null)
                            : ($state['text'] ?? null)
                    )
                    ->collapsible()
                    ->columns(1),
            ]);
    }
    
        // ── Supply concentration bars (severity index with progress bars) ──────────

    private static function supplyConcentrationBarsBlock(): Block
    {
        return Block::make('supplyConcentrationBars')
            ->label('Supply concentration bars — severity index with progress bars')
            ->schema([
                // ── Section appearance ──────────────────────────────────
                \Filament\Schemas\Components\Grid::make(2)->schema([
                    Select::make('section_width')
                        ->label('Section background width')
                        ->options([
                            'contained' => 'Contained (centered, max width)',
                            'full'      => 'Full width (edge-to-edge)',
                        ])
                        ->default('full')
                        ->helperText('Full width makes the dark panel stretch edge-to-edge; its content stays centered and readable.')
                        ->native(false),

                    ColorPicker::make('section_bg_color')
                        ->label('Section background color')
                        ->default('#0b1220'),
                ]),

                static::blockLocaleTabs('scb_header_lang', [
                    ['name' => 'kicker',  'label' => 'Eyebrow / kicker text', 'type' => 'text'],
                    ['name' => 'heading', 'label' => 'Heading',               'type' => 'html'],
                ]),

                \Filament\Schemas\Components\Grid::make(3)->schema([
                    ColorPicker::make('kicker_color')
                        ->label('Kicker text color')
                        ->default('#f97316'),

                    ColorPicker::make('heading_color')
                        ->label('Heading text color')
                        ->default('#ffffff'),

                    ColorPicker::make('heading_accent_color')
                        ->label('Heading underline accent color')
                        ->default('#f97316'),
                ]),

                \Filament\Schemas\Components\Grid::make(3)->schema([
                    ColorPicker::make('title_text_color')
                        ->label('Row title text color')
                        ->default('#ffffff'),

                    ColorPicker::make('description1_text_color')
                        ->label('Row description text color')
                        ->default('#94a3b8'),
                        
                    ColorPicker::make('description2_text_color')
                        ->label('Row description text color')
                        ->default('#94a3b8'),

                    ColorPicker::make('divider_color')
                        ->label('Row divider line color')
                        ->default('#1e293b'),
                ]),

                ColorPicker::make('bar_track_color')
                    ->label('Bar track (unfilled portion) background color')
                    ->default('#1e293b'),

                // ── Items ────────────────────────────────────────────────
                Repeater::make('items')
                    ->label('Concentration items')
                    ->minItems(1)
                    ->schema([
                        static::blockLocaleTabs('scb_item_lang', [
                            ['name' => 'title',       'label' => 'Item title (e.g. Adiponitrile (ADN))', 'type' => 'html'],
                            ['name' => 'description1', 'label' => 'Description-1',                          'type' => 'html'],
                            ['name' => 'badge_label', 'label' => 'Badge label (e.g. Extreme — 4 producers · 100%)', 'type' => 'text'],
                            ['name' => 'description2', 'label' => 'Description-2',                          'type' => 'html'],
                        ]),

                        TextInput::make('percent')
                            ->label('Concentration percent (0–100, controls bar fill width)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->required(),

                        \Filament\Schemas\Components\Grid::make(4)->schema([
                            ColorPicker::make('bar_fill_color')
                                ->label('Bar fill color')
                                ->default('#ef4444'),

                            ColorPicker::make('badge_bg_color')
                                ->label('Badge background color')
                                ->default('#ffffff'),

                            ColorPicker::make('badge_text_color')
                                ->label('Badge text color')
                                ->default('#dc2626'),

                            ColorPicker::make('badge_border_color')
                                ->label('Badge border color')
                                ->default('#dc2626'),
                        ]),
                    ])
                    ->itemLabel(fn (array $state): ?string => is_array($state['title'] ?? null)
                        ? strip_tags($state['title']['en'] ?? '')
                        : strip_tags((string) ($state['title'] ?? '')))
                    ->collapsible()
                    ->columns(1),
            ]);
    }

    private static function richText2Block(): Block
    {
        return Block::make('richText2')
            ->label('Rich text 2')
            ->schema([
                static::blockLocaleTabs('dl_item_lang', [
                    ['name' => 'kicker',  'label' => 'Kicker', 'type' => 'text'],
                    ['name' => 'heading',  'label' => 'Heading', 'type' => 'text'],
                    ['name' => 'html',   'label' => 'HTML content',   'type' => 'html', 'rows' => 10, 'helper' => 'Use this for long-form content.'],
                ]),
            ]);
    }

    // ── Pull Quote ───────────────────────────────────────────────────────────

    private static function pullQuoteBlock(): Block
    {
        return Block::make('quoteBlock')
            ->label('Pull quote — full-width author quote')
            ->schema([
                Select::make('bg')
                    ->label('Background')
                    ->options([
                        'dark'  => 'Dark navy (default)',
                        'light' => 'Off-white',
                        'white' => 'White',
                    ])
                    ->default('dark')
                    ->required(),

                ColorPicker::make('accent_color')
                    ->label('Accent line colour')
                    ->default('#C8A96E')
                    ->helperText('The gold underline and quotation mark colour. Default: #C8A96E (Turkish gold).'),

                static::blockLocaleTabs('qb_lang', [
                    [
                        'name'  => 'quote',
                        'label' => 'Quote text (no quotation marks needed — added by template)',
                        'type'  => 'textarea',
                        'rows'  => 4,
                    ],
                    [
                        'name'  => 'author_name',
                        'label' => 'Author name',
                        'type'  => 'text',
                    ],
                    [
                        'name'  => 'author_title',
                        'label' => 'Author title / role (e.g. Founder & Managing Director, Global Trading Ltd.)',
                        'type'  => 'text',
                    ],
                ]),
            ]);
    }

    public static function fullWidthCardsBlock(): Block
    {
        return Block::make('fullWidthCards')
            ->label('Full-Width Card Grid')
            ->schema([

                \Filament\Schemas\Components\Grid::make(2)->schema([
                    Select::make('columns')
                        ->label('Columns')
                        ->options(['2' => '2', '3' => '3', '4' => '4'])
                        ->default('3'),
                    Select::make('grid_type')
                        ->label('Card spacing')
                        ->options(['gapless' => 'Gapless', 'gaped' => 'Gapped'])
                        ->default('gaped'),
                ]),

                \Filament\Schemas\Components\Grid::make(4)->schema([
                    \Filament\Forms\Components\ColorPicker::make('section_bg_color')
                        ->label('Section background color')
                        ->default('#dce9f5'),
                    \Filament\Forms\Components\ColorPicker::make('card_bg_color')
                        ->label('Card background color')
                        ->default('#00000000'),  // transparent
                    \Filament\Forms\Components\ColorPicker::make('section_kicker_color')
                        ->label('Section kicker color')
                        ->default('#64748B'),
                    \Filament\Forms\Components\ColorPicker::make('section_heading_color')
                        ->label('Section heading color')
                        ->default('#1E293B'),
                ]),
                
                \Filament\Schemas\Components\Grid::make(4)->schema([
                    \Filament\Forms\Components\ColorPicker::make('section_subtitle_color')
                        ->label('Section subtitle color')
                        ->default('#475569'),
                    \Filament\Forms\Components\ColorPicker::make('card_kicker_color')
                        ->label('Card kicker color')
                        ->default('#64748B'),
                    \Filament\Forms\Components\ColorPicker::make('card_title_color')
                        ->label('Card title color')
                        ->default('#1E293B'),
                    \Filament\Forms\Components\ColorPicker::make('card_body_color')
                        ->label('Card body color')
                        ->default('#475569'),
                ]),

                \Filament\Schemas\Components\Section::make('Section header font sizes')
                    ->collapsed()
                    ->schema([
                        \Filament\Schemas\Components\Grid::make(3)->schema([
                            Select::make('kicker_size')
                                ->label('Kicker size')
                                ->options(static::fontSizeOptions())
                                ->default('text-sm'),
                            Select::make('heading_size')
                                ->label('Heading size')
                                ->options(static::fontSizeOptions())
                                ->default('text-4xl'),
                            Select::make('subtitle_size')
                                ->label('Subtitle size')
                                ->options(static::fontSizeOptions())
                                ->default('text-xl'),
                        ]),
                    ]),

                static::blockLocaleTabs('fwc_heading_lang', [
                    ['name' => 'kicker',        'label' => 'Section kicker (optional)',   'type' => 'text'],
                    ['name' => 'heading_tabs',  'label' => 'Section heading (optional)',  'type' => 'text'],
                    ['name' => 'subtitle_tabs', 'label' => 'Section subtitle (optional)', 'type' => 'html', 'rows' => 3],
                ]),

                \Filament\Schemas\Components\Section::make('Card font sizes')
                    ->collapsed()
                    ->schema([
                        \Filament\Schemas\Components\Grid::make(4)->schema([
                            Select::make('item_kicker_size')
                                ->label('Kicker')
                                ->options(static::fontSizeOptions())
                                ->default('text-sm'),
                            Select::make('item_title_size')
                                ->label('Title')
                                ->options(static::fontSizeOptions())
                                ->default('text-2xl'),
                            Select::make('item_body_size')
                                ->label('Body')
                                ->options(static::fontSizeOptions())
                                ->default('text-base'),
                            Select::make('item_cta_size')
                                ->label('CTA')
                                ->options(static::fontSizeOptions())
                                ->default('text-sm'),
                        ]),
                    ]),

                Repeater::make('items')
                    ->label('Cards')
                    ->minItems(1)
                    ->schema([
                        static::blockLocaleTabs('fwc_item_lang', [
                            ['name' => 'kicker_tabs',  'label' => 'Kicker',     'type' => 'text'],
                            ['name' => 'title_tabs',   'label' => 'Title',      'type' => 'text'],
                            ['name' => 'excerpt_tabs', 'label' => 'Body html',  'type' => 'html', 'rows' => 8],
                            ['name' => 'cta_tabs',     'label' => 'CTA label',  'type' => 'text'],
                        ]),
                        FileUpload::make('cover_image_path')
                            ->label('Cover image')
                            ->disk('public')
                            ->directory('pages')
                            ->image(),
                        TextInput::make('link_url')
                            ->label('Card link URL (optional)')
                            ->nullable(),
                        TextInput::make('cta_url')
                            ->label('CTA URL (optional)')
                            ->nullable(),
                    ])
                    ->columns(1)
                    ->collapsible(),
            ]);
    }

    // ── Spec table (grouped multi-column comparison, e.g. valve types) ─────────
    public static function specTableBlock(): Block
    {
        $fontSizeOptions = [
            'text-xs'   => 'Extra small',
            'text-sm'   => 'Small',
            'text-base' => 'Base',
            'text-lg'   => 'Large',
            'text-xl'   => 'Extra large',
        ];

        return Block::make('specTable')
            ->label('Spec table — grouped multi-column comparison (e.g. valve/product types)')
            ->schema([
                static::blockLocaleTabs('spec_header_lang', [
                    ['name' => 'kicker',  'label' => 'Section kicker (optional)', 'type' => 'text'],
                    ['name' => 'heading', 'label' => 'Section heading (optional)', 'type' => 'text'],
                    ['name' => 'html',   'label' => 'HTML content',   'type' => 'html', 'rows' => 2, 'helper' => 'Use this for long-form content.'],
                ]),

                Toggle::make('show_column_headers')
                    ->label('Show a column header row above the groups')
                    ->default(false),

                static::blockLocaleTabs('spec_cols_lang', [
                    ['name' => 'col_name_label',      'label' => 'Column header — Name',                    'type' => 'text'],
                    ['name' => 'col_standards_label',  'label' => 'Column header — Standards',               'type' => 'text'],
                    ['name' => 'col_class_label',      'label' => 'Column header — Pressure class',          'type' => 'text'],
                    ['name' => 'col_materials_label',  'label' => 'Column header — Materials',               'type' => 'text'],
                    ['name' => 'col_trim_label',       'label' => 'Column header — Seat / trim',             'type' => 'text'],
                    ['name' => 'col_ends_label',       'label' => 'Column header — Ends',                    'type' => 'text'],
                    ['name' => 'col_features_label',   'label' => 'Column header — Features / certifications', 'type' => 'text'],
                ]),

                \Filament\Schemas\Components\Grid::make(2)->schema([
                    Select::make('section_width')
                        ->label('Section background width')
                        ->options([
                            'contained' => 'Contained (current — centered, max width)',
                            'full'      => 'Full width (edge-to-edge)',
                        ])
                        ->default('contained')
                        ->helperText('The table itself always stays centered at max-w-7xl — this only changes how far the background color extends.')
                        ->native(false),

                    ColorPicker::make('section_bg_color')
                        ->label('Section background color (optional)')
                        ->nullable(),
                ]),

                \Filament\Schemas\Components\Grid::make(3)->schema([
                    ColorPicker::make('header_row_bg_color')
                        ->label('Header row background color')
                        ->default('#f1f5f9'),

                    ColorPicker::make('header_text_color')
                        ->label('Header row text color')
                        ->default('#64748b'),

                    Select::make('header_font_size')
                        ->label('Header row font size')
                        ->options($fontSizeOptions)
                        ->default('text-xs')
                        ->native(false),
                ]),

                \Filament\Schemas\Components\Grid::make(4)->schema([
                    ColorPicker::make('group_title_bg_color')
                        ->label('Group title row background color')
                        ->default('#f0f9ff'),

                    ColorPicker::make('group_title_text_color')
                        ->label('Group title row text color')
                        ->default('#334155'),

                    ColorPicker::make('group_title_hover_bg_color')
                        ->label('Group title row hover/highlight background color')
                        ->default('#e0f2fe'),

                    Select::make('group_title_font_size')
                        ->label('Group title font size')
                        ->options($fontSizeOptions)
                        ->default('text-xs')
                        ->native(false),
                ]),

                ColorPicker::make('heading_accent_color')
                    ->label('Heading underline accent color')
                    ->default('#f97316'),

                \Filament\Schemas\Components\Grid::make(4)->schema([
                    ColorPicker::make('row_bg_color')
                        ->label('Data row background color')
                        ->default('#ffffff'),

                    ColorPicker::make('row_bg_color_alt')
                        ->label('Data row alternate background color (zebra striping)')
                        ->default('#f8fafc'),

                    ColorPicker::make('row_hover_bg_color')
                        ->label('Data row hover/highlight background color')
                        ->default('#eff6ff'),

                    Select::make('row_font_size')
                        ->label('Data row font size')
                        ->options($fontSizeOptions)
                        ->default('text-sm')
                        ->native(false),
                ]),

                \Filament\Schemas\Components\Section::make('Column text colors')
                    ->collapsed()
                    ->schema([
                        \Filament\Schemas\Components\Grid::make(4)->schema([
                            ColorPicker::make('col_name_text_color')->label('Name column')->default('#1e293b'),
                            ColorPicker::make('col_standards_text_color')->label('Standards column')->default('#475569'),
                            ColorPicker::make('col_class_text_color')->label('Pressure class column')->default('#475569'),
                            ColorPicker::make('col_materials_text_color')->label('Materials column')->default('#475569'),
                            ColorPicker::make('col_trim_text_color')->label('Seat / trim column')->default('#475569'),
                            ColorPicker::make('col_ends_text_color')->label('Ends column')->default('#475569'),
                            ColorPicker::make('col_features_text_color')->label('Features / certifications column')->default('#475569'),
                        ]),
                    ]),

                \Filament\Schemas\Components\Section::make('Column widths (relative — auto-redistributed among visible columns)')
                    ->collapsed()
                    ->schema([
                        \Filament\Schemas\Components\Grid::make(4)->schema([
                            TextInput::make('col_name_width')->label('Name')->numeric()->default(16),
                            TextInput::make('col_standards_width')->label('Standards')->numeric()->default(14),
                            TextInput::make('col_class_width')->label('Pressure class')->numeric()->default(13),
                            TextInput::make('col_materials_width')->label('Materials')->numeric()->default(16),
                            TextInput::make('col_trim_width')->label('Seat / trim')->numeric()->default(15),
                            TextInput::make('col_ends_width')->label('Ends')->numeric()->default(12),
                            TextInput::make('col_features_width')->label('Features / certifications')->numeric()->default(14),
                        ]),
                    ]),

                Repeater::make('groups')
                    ->label('Category groups')
                    ->minItems(1)
                    ->schema([
                        static::blockLocaleTabs('spec_group_lang', [
                            ['name' => 'group_title', 'label' => 'Group title (e.g. BALL VALVES)', 'type' => 'text'],
                        ]),

                        Repeater::make('rows')
                            ->label('Rows')
                            ->minItems(1)
                            ->schema([
                                static::blockLocaleTabs('spec_row_lang', [
                                    ['name' => 'name',           'label' => 'Product / type name',        'type' => 'html', 'rows' => 2],
                                    ['name' => 'standards',       'label' => 'Standards',                   'type' => 'html', 'rows' => 2],
                                    ['name' => 'pressure_class',  'label' => 'Pressure class',              'type' => 'html', 'rows' => 2],
                                    ['name' => 'materials',       'label' => 'Materials',                   'type' => 'html', 'rows' => 2],
                                    ['name' => 'seat_trim',       'label' => 'Seat / trim',                 'type' => 'html', 'rows' => 2],
                                    ['name' => 'ends',            'label' => 'Ends',                        'type' => 'html', 'rows' => 2],
                                    ['name' => 'features',        'label' => 'Features / certifications',   'type' => 'html', 'rows' => 2],
                                ]),
                            ])
                            ->itemLabel(fn (array $state): ?string => is_array($state['name'] ?? null)
                                ? ($state['name']['en'] ?? null)
                                : ($state['name'] ?? null))
                            ->collapsible()
                            ->columns(1),
                    ])
                    ->itemLabel(fn (array $state): ?string => is_array($state['group_title'] ?? null)
                        ? ($state['group_title']['en'] ?? null)
                        : ($state['group_title'] ?? null))
                    ->collapsible()
                    ->columns(1),
            ]);
    }

    public static function overlayCarouselBlock(): Block
    {
        return Block::make('overlayCarousel')
            ->label('Overlay Card Carousel')
            ->schema([

                // Section meta
                static::blockLocaleTabs('ovcar_meta_lang', [
                    ['name' => 'kicker',        'label' => 'Section kicker',   'type' => 'text'],
                    ['name' => 'heading_tabs',  'label' => 'Section heading',  'type' => 'text'],
                    ['name' => 'subtitle_tabs', 'label' => 'Section subtitle', 'type' => 'text'],
                ]),

                // Inner heading (displayed above cards inside the section)
                static::blockLocaleTabs('ovcar_inner_lang', [
                    ['name' => 'inner_heading', 'label' => 'Inner heading (shown above cards)', 'type' => 'text'],
                ]),

                \Filament\Schemas\Components\Grid::make(2)->schema([
                    FileUpload::make('bg_image_path')
                        ->label('Section background image')
                        ->disk('public')
                        ->directory('blocks/carousel')
                        ->image(),
                    \Filament\Forms\Components\ColorPicker::make('bg_overlay_color')
                        ->label('Background overlay color')
                        ->default('#8B0000CC'),
                ]),

                \Filament\Schemas\Components\Grid::make(3)->schema([
                    \Filament\Forms\Components\ColorPicker::make('card_border_color')
                        ->label('Card border color')
                        ->default('#ffffff'),
                    \Filament\Forms\Components\ColorPicker::make('card_overlay_color')
                        ->label('Card default overlay color')
                        ->default('#00000099'),
                    \Filament\Forms\Components\ColorPicker::make('nav_btn_color')
                        ->label('Prev / Next button color')
                        ->default('#DAA520'),
                ]),

                \Filament\Schemas\Components\Grid::make(2)->schema([
                    \Filament\Forms\Components\Toggle::make('autoplay')
                        ->label('Auto-play')
                        ->default(true),
                    \Filament\Forms\Components\TextInput::make('autoplay_ms')
                        ->label('Auto-play interval (ms)')
                        ->numeric()
                        ->default(4000),
                ]),

                Repeater::make('items')
                    ->label('Cards')
                    ->minItems(1)
                    ->schema([
                        static::blockLocaleTabs('ovcar_item_lang', [
                            ['name' => 'kicker_tabs',   'label' => 'Kicker',   'type' => 'text'],
                            ['name' => 'title_tabs',    'label' => 'Heading',  'type' => 'text'],
                            ['name' => 'subtitle_tabs', 'label' => 'Subtitle', 'type' => 'text'],
                        ]),
                        FileUpload::make('card_image_path')
                            ->label('Card image')
                            ->disk('public')
                            ->directory('blocks/carousel')
                            ->image(),
                        \Filament\Forms\Components\ColorPicker::make('card_overlay_color')
                            ->label('Card overlay color (overrides global if set)')
                            ->nullable(),
                        TextInput::make('link_url')
                            ->label('Card link URL (optional)')
                            ->nullable(),
                    ])
                    ->columns(1)
                    ->collapsible(),
            ]);
    }

    // ── Home-only blocks (not available on regular Pages) ─────────────────

    public static function homeOnlyBlocks(): array
    {
        return [
            static::marketBeltBlock(),
            static::industriesSliderBlock(),
            static::ctaBlock(),
            static::trendingTopicsBlock(),
            static::featuredNewsBlock(),
        ];
    }

    private static function marketBeltBlock(): Block
    {
        return Block::make('market_belt')
            ->label('Market belt (live FX / commodity ticker)')
            ->schema([
                Toggle::make('enabled')->default(true),
            ]);
    }

    private static function industriesSliderBlock(): Block
    {
        return Block::make('industries_slider')
            ->label('Industries slider (auto-populated from Industries CMS)')
            ->schema([
                // Section meta — matches overlayCarousel's kicker / heading / subtitle
                static::blockLocaleTabs('ind_slider_meta_lang', [
                    ['name' => 'kicker',        'label' => 'Section kicker',   'type' => 'text'],
                    ['name' => 'heading_tabs',  'label' => 'Section heading',  'type' => 'text'],
                    ['name' => 'subtitle_tabs', 'label' => 'Section subtitle', 'type' => 'text'],
                ]),

                // Inner heading (displayed above cards inside the section)
                static::blockLocaleTabs('ind_slider_inner_lang', [
                    ['name' => 'inner_heading', 'label' => 'Inner heading (shown above cards)', 'type' => 'text'],
                ]),

                \Filament\Schemas\Components\Grid::make(2)->schema([
                    FileUpload::make('bg_image_path')
                        ->label('Section background image')
                        ->disk('public')
                        ->directory('home/industries')
                        ->image(),
                    ColorPicker::make('bg_overlay_color')
                        ->label('Background overlay color')
                        ->default('#8B0000CC'),
                ]),

                \Filament\Schemas\Components\Grid::make(3)->schema([
                    ColorPicker::make('card_border_color')
                        ->label('Card border color')
                        ->default('#ffffff'),
                    ColorPicker::make('card_overlay_color')
                        ->label('Card overlay color')
                        ->default('#00000099'),
                    ColorPicker::make('nav_btn_color')
                        ->label('Prev / Next button color')
                        ->default('#DAA520'),
                ]),

                \Filament\Schemas\Components\Grid::make(2)->schema([
                    Toggle::make('autoplay')
                        ->label('Auto-play')
                        ->default(true),
                    TextInput::make('autoplay_ms')
                        ->label('Auto-play interval (ms)')
                        ->numeric()
                        ->default(4000),
                ]),

                TextInput::make('view_all_url')
                    ->label('View-all URL (supports {locale})')
                    ->default('/{locale}/industries'),
            ]);
    }

    private static function ctaBlock(): Block
    {
        return Block::make('cta')
            ->label('CTA section')
            ->schema([
                static::blockLocaleTabs('cta_lang', [
                    ['name' => 'title',        'label' => 'Title',         'type' => 'text'],
                    ['name' => 'text',         'label' => 'Body text',     'type' => 'textarea', 'rows' => 3],
                    ['name' => 'button_label', 'label' => 'Button label',  'type' => 'text'],
                ]),
                TextInput::make('button_url')->label('Button URL')->required(),
            ]);
    }

    private static function trendingTopicsBlock(): Block
    {
        return Block::make('trending_topics')
            ->label('Trending Topics (social media stage)')
            ->schema([
                static::blockLocaleTabs('tt_lang', [
                    ['name' => 'title', 'label' => 'Section title', 'type' => 'text'],
                ]),
                FileUpload::make('background_image_path')
                    ->label('Background image (optional)')
                    ->disk('public')
                    ->directory('home/trending')
                    ->image(),
                Repeater::make('topics')
                    ->label('Cards — exactly 5 (order: IG, IG, LI-big, LI, LI)')
                    ->minItems(5)
                    ->maxItems(5)
                    ->defaultItems(5)
                    ->schema([
                        Select::make('source')
                            ->options(['instagram' => 'Instagram', 'linkedin' => 'LinkedIn'])
                            ->required()
                            ->default('linkedin'),
                        FileUpload::make('image_path')
                            ->label('Card image')
                            ->disk('public')
                            ->directory('home/trending/cards')
                            ->image(),
                        static::blockLocaleTabs('tt_card_lang', [
                            ['name' => 'title', 'label' => 'Title', 'type' => 'text'],
                            ['name' => 'text',  'label' => 'Text',  'type' => 'textarea', 'rows' => 3],
                        ]),
                        TextInput::make('profile_name')
                            ->label('Profile name (e.g. globaltrding)')
                            ->default('Globaltrding'),
                        TextInput::make('time_ago')
                            ->label('Time label (e.g. 7 days ago)')
                            ->default('—'),
                        TextInput::make('original_url')
                            ->label('Original post URL')
                            ->url(),
                        TextInput::make('privacy_url')
                            ->label('Privacy URL (supports /{locale}/…)')
                            ->default('/{locale}/pages/privacy-policy'),
                    ])
                    ->itemLabel(fn (array $state): ?string =>
                        ucfirst($state['source'] ?? 'card')
                    ),
            ]);
    }

    private static function featuredNewsBlock(): Block
    {
        return Block::make('featuredNews')
            ->label('Featured News (auto-populated from News CMS)')
            ->schema([
                static::blockLocaleTabs('dl_item_lang', [
                        ['name' => 'title',  'label' => 'Section title', 'type' => 'text'],
                        ['name' => 'lead',   'label' => 'Section lead (optional)',   'type' => 'textarea', 'rows' => 2],
                        ['name' => 'view_all_label',   'label' => '"View all" link label',   'type' => 'text', 'default' => 'View all →'],
                    ]),
                TextInput::make('limit')
                    ->label('Max featured posts to show')
                    ->numeric()
                    ->default(3)
                    ->minValue(1)
                    ->maxValue(12),
                Toggle::make('show_view_all')
                    ->label('Show "View all" link')
                    ->default(true),
            ]);
    }

    public static function industryOnlyBlocks(): array
    {
        return [
            static::colsGridsBlocks(),
            static::richTextBlock(),
            static::imageBlock(),
            static::videoBlock(),
        ];
    }

    public static function colsGridsBlocks(): Block
    {
        return Block::make('colsGrids')
            ->label('Card grid (2–5 columns)')
            ->schema([
                Select::make('item-align')
                    ->label('Card content alignment')
                    ->options(['center' => 'Center', 'left' => 'Left'])
                    ->default('left'),
                Select::make('grid_type')
                    ->label('Grid type')
                    ->options(['gapless' => 'Gapless', 'gaped' => 'Gaped'])
                    ->default('gaped'),
                Select::make('columns')
                    ->label('Columns')
                    ->options(['2' => '2', '3' => '3', '4' => '4', '5' => '5'])
                    ->default('3'),

                static::blockLocaleTabs('colsGrids_heading_lang', [
                    ['name' => 'kicker',  'label' => 'Section kicker (optional)', 'type' => 'text'],
                    ['name' => 'heading_tabs',  'label' => 'Section heading (optional)', 'type' => 'text'],
                    ['name' => 'subtitle_tabs', 'label' => 'Section subtitle (optional)', 'type' => 'html', 'rows' => 8],
                ]),

                Repeater::make('items')
                    ->label('Cards')
                    ->minItems(1)
                    ->schema([
                        static::blockLocaleTabs('colsGrids_item_lang', [
                            ['name' => 'kicker_tabs',   'label' => 'Kicker',   'type' => 'text'],
                            ['name' => 'title_tabs',   'label' => 'Title',   'type' => 'text'],
                            ['name' => 'excerpt_tabs', 'label' => 'Excerpt', 'type' => 'html', 'rows' => 2],
                            ['name' => 'cta_tabs',   'label' => 'CTA Label',   'type' => 'text'],
                        ]),

                        FileUpload::make('cover_image_path')
                            ->label('Cover image')
                            ->disk('public')
                            ->directory('industries')
                            ->image(),

                        TextInput::make('link_url')
                            ->label('Link URL (optional)')
                            ->helperText('Absolute URL or path, e.g. /en/industries/oil-gas. Leave empty for a non-clickable card.')
                            ->nullable(),
                        TextInput::make('cta_url')
                            ->label('CTA URL (optional)')
                            ->helperText('Absolute URL or path, e.g. /en/industries/oil-gas. Leave empty for a non-clickable card.')
                            ->nullable(),
                        ColorPicker::make('img_bg_color')->label('Image background color')->default('#94A3B8'),
                        ColorPicker::make('kicker_color')->label('Kicker text color')->default('#94A3B8'),
                        ColorPicker::make('title_color')->label('Title text color')->default('#0f172a'),
                        ColorPicker::make('excerpt_color')->label('Excerpt text color')->default('#475569'),
                        ColorPicker::make('cta_color')->label('CTA label color')->default('#2563EB'),
                        ColorPicker::make('card_bg_color')->label('Card background color')->default('#ffffff'),
                        ColorPicker::make('card_border_color')->label('Card border color')->default('#e2e8f0'),
                        ColorPicker::make('card_hover_border_color')->label('Card hover border color')->default('#CBD5E1'),
                    ])
                    ->columns(1)
                    ->collapsible(),
            ]);
    }

    private static function richTextBlock(): Block
    {
        $fontSizeOptions = [
            'text-sm'  => 'Small',
            'text-base' => 'Base',
            'text-lg'  => 'Large',
            'text-xl'  => 'Extra large',
            'text-2xl' => '2X large',
            'text-3xl' => '3X large',
            'text-4xl' => '4X large',
            'text-5xl' => '5X large',
        ];

        $styleOptions = [
            'normal'      => 'Normal',
            'bold'        => 'Bold',
            'italic'      => 'Italic',
            'bold_italic' => 'Bold italic',
            'narrow'      => 'Narrow (tight letter-spacing)',
        ];

        return Block::make('richText')
            ->label('Rich text')
            ->schema([
                static::blockLocaleTabs('dl_item_lang', [
                        ['name' => 'kicker',  'label' => 'Kicker', 'type' => 'text'],
                        ['name' => 'heading',  'label' => 'Heading', 'type' => 'text'],
                        ['name' => 'html',   'label' => 'HTML content',   'type' => 'html', 'rows' => 10, 'helper' => 'Use this for long-form content.'],
                    ]),

                // ── Section width & background ──────────────────────────
                \Filament\Schemas\Components\Grid::make(2)->schema([
                    Select::make('section_type')
                        ->label('Section width')
                        ->options(['full' => 'Full width', 'nofull' => 'Contained'])
                        ->default('nofull')
                        ->helperText('Full width only stretches the background — kicker, heading and content always stay centered at max-w-7xl.')
                        ->native(false),

                    ColorPicker::make('section_bg_color')
                        ->label('Section background color')
                        ->default('#F3F4F6'),
                ]),

                // ── Kicker style ─────────────────────────────────────────
                \Filament\Schemas\Components\Grid::make(3)->schema([
                    ColorPicker::make('kicker_color')
                        ->label('Kicker text color')
                        ->default('#64748b'),
                    Select::make('kicker_font_size')
                        ->label('Kicker font size')
                        ->options($fontSizeOptions)
                        ->default('text-lg')
                        ->native(false),
                    Select::make('kicker_style')
                        ->label('Kicker style')
                        ->options($styleOptions)
                        ->default('bold')
                        ->native(false),
                ]),

                // ── Heading style ────────────────────────────────────────
                \Filament\Schemas\Components\Grid::make(3)->schema([
                    ColorPicker::make('heading_color')
                        ->label('Heading text color')
                        ->default('#0f172a'),
                    Select::make('heading_font_size')
                        ->label('Heading font size')
                        ->options($fontSizeOptions)
                        ->default('text-4xl')
                        ->native(false),
                    Select::make('heading_style')
                        ->label('Heading style')
                        ->options($styleOptions)
                        ->default('bold')
                        ->native(false),
                ]),

                // ── HTML content style ───────────────────────────────────
                \Filament\Schemas\Components\Grid::make(3)->schema([
                    ColorPicker::make('html_color')
                        ->label('Content text color')
                        ->default('#334155'),
                    Select::make('html_font_size')
                        ->label('Content font size')
                        ->options($fontSizeOptions)
                        ->default('text-base')
                        ->native(false),
                    Select::make('html_style')
                        ->label('Content style')
                        ->options($styleOptions)
                        ->default('normal')
                        ->native(false),
                ]),

                // ── CTAs ─────────────────────────────────────────────────
                Repeater::make('ctas')
                    ->label('Call-to-action buttons')
                    ->helperText('Added buttons are laid out in a horizontal row (wrapping on small screens) in the order listed below.')
                    ->schema([
                        static::blockLocaleTabs('rt_cta_lang', [
                            ['name' => 'label', 'label' => 'Button label', 'type' => 'text'],
                        ]),

                        TextInput::make('url')
                            ->label('Button URL')
                            ->required(),

                        \Filament\Schemas\Components\Grid::make(2)->schema([
                            ColorPicker::make('bg_color')
                                ->label('Button background color')
                                ->default('#0f172a'),
                            ColorPicker::make('text_color')
                                ->label('Button label text color')
                                ->default('#ffffff'),
                        ]),
                    ])
                    ->itemLabel(fn (array $state): ?string => is_array($state['label'] ?? null)
                        ? ($state['label']['en'] ?? null)
                        : ($state['label'] ?? null))
                    ->collapsible()
                    ->columns(1),
            ]);
    }

    private static function imageBlock(): Block
    {
        return Block::make('image')
            ->label('Image')
            ->schema([
                FileUpload::make('path')
                    ->disk('public')
                    ->maxSize((auth()->user()?->maxUploadMb() ?? 150) * 1024)
                    ->imageEditor()  // optional if you want crop/resize UI
                    ->directory('industries/blocks')
                    ->image()
                    ->required(),
                static::blockLocaleTabs('dl_item_lang', [
                        ['name' => 'caption',  'label' => 'Caption', 'type' => 'text', 'helper' => 'Optional caption text to display below the image.'],
                    ]),
            ]);
    }

    private static function videoBlock(): Block
    {
        return Block::make('video')
            ->label('Video')
            ->schema([
                FileUpload::make('path')
                    ->disk('public')
                    ->directory('industries/blocks')
                    ->maxSize((auth()->user()?->maxUploadMb() ?? 150) * 1024)
                    ->acceptedFileTypes(['video/mp4', 'video/webm', 'image/jpeg', 'image/png', 'image/webp'])
                    ->required(),
                static::blockLocaleTabs('dl_item_lang', [
                        ['name' => 'caption',  'label' => 'Caption', 'type' => 'text', 'helper' => 'Optional caption text to display below the image.'],
                    ]),
            ]);
    }
}