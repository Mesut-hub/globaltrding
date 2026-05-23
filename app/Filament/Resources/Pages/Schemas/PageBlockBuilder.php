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
        ];
    }

    // ── Hero ─────────────────────────────────────────────────────────────────

    private static function heroBlock(): Block
    {
        return Block::make('hero')
            ->label('Hero — full-width banner with slides')
            ->schema([
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
                    ->disk('public')->directory('pages/hero')->image()->multiple()->reorderable(),

                FileUpload::make('video')
                    ->label('Background video (mp4/webm)')
                    ->disk('public')->directory('pages/hero')
                    ->acceptedFileTypes(['video/mp4', 'video/webm']),

                // ── Slides (text per slide, per locale) ───────────────────
                Repeater::make('slides')
                    ->label('Slides (text content)')
                    ->minItems(1)
                    ->schema([
                        TextInput::make('cta_url')->label('CTA URL'),

                        static::blockLocaleTabs('hero_slide_lang', [
                            ['name' => 'kicker',    'label' => 'Kicker',    'type' => 'text'],
                            ['name' => 'title',     'label' => 'Title',     'type' => 'text'],
                            ['name' => 'lead',      'label' => 'Lead text', 'type' => 'textarea', 'rows' => 2],
                            ['name' => 'cta_label', 'label' => 'CTA label', 'type' => 'text'],
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
                    ->options(['blue' => 'Blue', 'dark' => 'Dark', 'slate' => 'Slate'])
                    ->default('blue'),

                ColorPicker::make('panel_text_color')->label('Panel text colour')->default('#ffffff'),
                ColorPicker::make('row2_link_color')->label('Row 2 link colour')->default('#0ea5e9'),

                FileUpload::make('top_left_image')
                    ->label('Top-left image')->disk('public')->directory('pages/insights')->image(),

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
                    ]),
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
                static::blockLocaleTabs('cards_lang', [
                    ['name' => 'title', 'label' => 'Heading', 'type' => 'text'],
                    ['name' => 'lead',  'label' => 'Lead',    'type' => 'textarea', 'rows' => 2],
                ]),

                Repeater::make('items')->label('Cards')->minItems(1)->schema([
                    FileUpload::make('image')->disk('public')->directory('pages/cards')->image(),
                    TextInput::make('url')->label('Card URL'),

                    static::blockLocaleTabs('cards_item_lang', [
                        ['name' => 'title', 'label' => 'Title', 'type' => 'text'],
                        ['name' => 'text',  'label' => 'Text',  'type' => 'textarea', 'rows' => 2],
                    ]),
                ]),
            ]);
    }

    // ── Metrics ───────────────────────────────────────────────────────────────

    private static function metricsBlock(): Block
    {
        return Block::make('metrics')
            ->label('Metrics / statistics')
            ->schema([
                Select::make('bg')->options(['slate' => 'Slate', 'white' => 'White', 'dark' => 'Dark'])->default('slate'),
                Toggle::make('animate')->label('Animate numbers')->default(true),

                static::blockLocaleTabs('metrics_lang', [
                    ['name' => 'title', 'label' => 'Section heading', 'type' => 'text'],
                ]),

                Repeater::make('items')->label('Metric items')->minItems(1)->schema([
                    TextInput::make('value')->label('Value (e.g. 12,000+)')->required(),

                    static::blockLocaleTabs('metrics_item_lang', [
                        ['name' => 'label', 'label' => 'Label', 'type' => 'text'],
                    ]),
                ])->columns(2),
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
                        ->options(['30-70' => '30/70', '40-60' => '40/60', '50-50' => '50/50', '60-40' => '60/40', '70-30' => '70/30'])
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
}