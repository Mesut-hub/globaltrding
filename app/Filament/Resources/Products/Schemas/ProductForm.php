<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Brand;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        $locales = config('locales.supported', ['en']);
        $default = config('locales.default', 'en');

        return $schema->components([

            // ── Non-translatable basics ──────────────────────────────────────
            Grid::make(2)->schema([
                Select::make('brand_id')
                    ->label('Brand')
                    ->options(fn () => Brand::query()
                        ->where('is_published', true)
                        ->orderBy('id')
                        ->get()
                        ->mapWithKeys(fn (Brand $b) => [
                            $b->id => (data_get($b->name, 'en') ?: "Brand #{$b->id}"),
                        ])
                        ->all()
                    )
                    ->searchable()
                    ->preload(),

                Toggle::make('is_published')
                    ->label('Published')
                    ->default(true)
                    ->required(),

                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]),

            TextInput::make('slug')
                ->label('Slug (URL-safe, language-neutral)')
                ->required()
                ->maxLength(255)
                ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')
                ->unique(ignoreRecord: true)
                ->helperText('Example: pd-al-5637-e-1-12  — shared across all locales')
                ->live(onBlur: true)
                ->afterStateUpdated(function (?string $state, callable $set) {
                    if (is_string($state)) {
                        $set('slug', Str::slug($state));
                    }
                }),

            TextInput::make('display_url')
                ->label('Product name URL (related page)')
                ->maxLength(2048),

            TextInput::make('prd_number')
                ->label('PRD Number')
                ->maxLength(64),

            // ── PDP access controls ──────────────────────────────────────────
            Grid::make(2)->schema([
                Toggle::make('pdp_public_overview')
                    ->label('Overview visible before login')
                    ->default(true),
                Toggle::make('pdp_public_sustainability')
                    ->label('Sustainability visible before login')
                    ->default(true),
                Toggle::make('pdp_public_properties')
                    ->label('Properties visible before login')
                    ->default(false),
                Toggle::make('pdp_public_documents')
                    ->label('Documents visible before login')
                    ->default(false),
            ]),

            Select::make('pdp_documents_logged_out_mode')
                ->label('Documents — logged-out behaviour')
                ->options([
                    'list_disabled' => 'A) Show list, disable downloads',
                    'hide_all'      => 'B) Hide content entirely (notice only)',
                ])
                ->default('list_disabled'),

            // ── Translatable top-level fields (one tab per locale) ───────────
            Tabs::make('Translations')
                ->persistTabInQueryString()
                ->tabs(
                    collect($locales)->map(function (string $locale) use ($default) {
                        $lbl = strtoupper($locale);

                        return Tab::make($lbl)->schema([

                            Grid::make(2)->schema([
                                TextInput::make("display_name.{$locale}")
                                    ->label("Product name ({$lbl})")
                                    ->required($locale === $default)
                                    ->maxLength(255),

                                TextInput::make("industry_label.{$locale}")
                                    ->label("Industry ({$lbl})")
                                    ->maxLength(255),
                            ]),

                            Grid::make(2)->schema([
                                TextInput::make("seo.title.{$locale}")
                                    ->label("SEO Title ({$lbl})")
                                    ->maxLength(60),
                                Textarea::make("seo.description.{$locale}")
                                    ->label("SEO Description ({$lbl})")
                                    ->rows(3)
                                    ->maxLength(160),
                            ]),

                            Grid::make(2)->schema([
                                Textarea::make("industries.{$locale}")
                                    ->label("Industries — one per line ({$lbl})")
                                    ->rows(4)
                                    ->formatStateUsing(fn ($state) => is_array($state) ? implode("\n", $state) : '')
                                    ->dehydrateStateUsing(fn ($state) => array_values(array_filter(
                                        array_map('trim', preg_split("/\r\n|\n|\r/", (string) $state))
                                    ))),

                                Textarea::make("applications.{$locale}")
                                    ->label("Applications — one per line ({$lbl})")
                                    ->rows(4)
                                    ->formatStateUsing(fn ($state) => is_array($state) ? implode("\n", $state) : '')
                                    ->dehydrateStateUsing(fn ($state) => array_values(array_filter(
                                        array_map('trim', preg_split("/\r\n|\n|\r/", (string) $state))
                                    ))),
                            ]),

                            Grid::make(2)->schema([
                                Textarea::make("product_groups.{$locale}")
                                    ->label("Product Groups — one per line ({$lbl})")
                                    ->rows(4)
                                    ->formatStateUsing(fn ($state) => is_array($state) ? implode("\n", $state) : '')
                                    ->dehydrateStateUsing(fn ($state) => array_values(array_filter(
                                        array_map('trim', preg_split("/\r\n|\n|\r/", (string) $state))
                                    ))),

                                Textarea::make("processes.{$locale}")
                                    ->label("Processes — one per line ({$lbl})")
                                    ->rows(4)
                                    ->formatStateUsing(fn ($state) => is_array($state) ? implode("\n", $state) : '')
                                    ->dehydrateStateUsing(fn ($state) => array_values(array_filter(
                                        array_map('trim', preg_split("/\r\n|\n|\r/", (string) $state))
                                    ))),
                            ]),

                            Grid::make(2)->schema([
                                Textarea::make("sustainability_tags.{$locale}")
                                    ->label("Sustainability — one per line ({$lbl})")
                                    ->rows(4)
                                    ->formatStateUsing(fn ($state) => is_array($state) ? implode("\n", $state) : '')
                                    ->dehydrateStateUsing(fn ($state) => array_values(array_filter(
                                        array_map('trim', preg_split("/\r\n|\n|\r/", (string) $state))
                                    ))),

                                Textarea::make("regulatory_tags.{$locale}")
                                    ->label("Regulatory — one per line ({$lbl})")
                                    ->rows(4)
                                    ->formatStateUsing(fn ($state) => is_array($state) ? implode("\n", $state) : '')
                                    ->dehydrateStateUsing(fn ($state) => array_values(array_filter(
                                        array_map('trim', preg_split("/\r\n|\n|\r/", (string) $state))
                                    ))),
                            ]),

                            Textarea::make("pdp_overview_html.{$locale}")
                                ->label("PDP — Overview HTML ({$lbl})")
                                ->rows(8),

                            Textarea::make("pdp_properties_html.{$locale}")
                                ->label("PDP — Properties HTML ({$lbl})")
                                ->rows(8),

                            Textarea::make("pdp_sustainability_html.{$locale}")
                                ->label("PDP — Sustainability HTML ({$lbl})")
                                ->rows(8),
                        ]);
                    })->values()->all()
                ),

            // ── PDP Block builders ───────────────────────────────────────────
            Builder::make('pdp_overview_blocks')
                ->label('PDP — Overview blocks')
                ->collapsed()
                ->blocks(self::pdpBlocks()),

            Builder::make('pdp_properties_blocks')
                ->label('PDP — Properties blocks')
                ->collapsed()
                ->blocks(self::pdpBlocks()),

            Builder::make('pdp_documents_blocks')
                ->label('PDP — Documents blocks')
                ->collapsed()
                ->blocks([self::docDropdownBlock()]),

            Builder::make('pdp_sustainability_blocks')
                ->label('PDP — Sustainability blocks')
                ->collapsed()
                ->blocks(self::pdpBlocks()),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Private helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Generate per-locale Tabs containing text fields for use inside a Block.
     *
     * $fields is an array of field definitions:
     *   ['name' => 'left_title',  'label' => 'Left title',  'type' => 'text']
     *   ['name' => 'left_html',   'label' => 'Left HTML',   'type' => 'html',  'rows' => 8]
     *   ['name' => 'excerpt',     'label' => 'Excerpt',     'type' => 'text',  'rows' => 3]
     *
     * Renders as locale-tabbed TextInput (type=text) or Textarea (type=html/textarea).
     * This replaces KeyValue components — values are stored as {"en": "...", "tr": "..."}.
     */
    private static function blockLocaleTabs(string $tabsId, array $fields): Tabs
    {
        $locales = config('locales.supported', ['en']);

        return Tabs::make($tabsId)
            ->columnSpanFull()
            ->tabs(
                collect($locales)->map(function (string $locale) use ($fields): Tab {
                    $lbl = strtoupper($locale);

                    $schema = collect($fields)->map(function (array $field) use ($locale, $lbl) {
                        $name  = $field['name'];
                        $label = ($field['label'] ?? ucwords(str_replace('_', ' ', $name))) . " ({$lbl})";
                        $type  = $field['type'] ?? 'text';
                        $rows  = (int) ($field['rows'] ?? 4);

                        return match ($type) {
                            'html', 'textarea' => Textarea::make("{$name}.{$locale}")
                                ->label($label)
                                ->rows($rows),
                            default => TextInput::make("{$name}.{$locale}")
                                ->label($label),
                        };
                    })->all();

                    return Tab::make($lbl)->schema($schema);
                })->values()->all()
            );
    }

    private static function pdpBlocks(): array
    {
        return [
            self::twoColsBlock(),
            self::pdCardsBlock(),
        ];
    }

    // ── Two Columns block ─────────────────────────────────────────────────────

    private static function twoColsBlock(): Block
    {
        return Block::make('twoCols')
            ->label('Two columns (Text / Text or Text / Media)')
            ->schema([
                // Non-translatable
                ColorPicker::make('bg')->label('Background')->default('#ffffff'),
                ColorPicker::make('card_bg')->label('Card background')->default('#ffffff'),
                ColorPicker::make('cta_bg')->label('CTA button background')->default('#0f172a'),
                ColorPicker::make('text')->label('Text colour')->default('#0f172a'),
                ColorPicker::make('html')->label('HTML content colour')->default('#0f172a'),

                Grid::make(2)->schema([
                    Toggle::make('public_visible')
                        ->label('Visible when logged out')
                        ->default(true),
                    Toggle::make('public_clickable')
                        ->label('CTA clickable when logged out')
                        ->default(false),
                ]),

                Select::make('layout')
                    ->label('Layout')
                    ->options([
                        'text_text'  => 'Text | Text',
                        'text_media' => 'Text | Media',
                        'media_text' => 'Media | Text',
                    ])
                    ->default('text_media')
                    ->required(),

                Select::make('media_type')
                    ->label('Media type')
                    ->options(['image' => 'Image', 'video' => 'Video'])
                    ->default('image')
                    ->visible(fn ($get) => $get('layout') !== 'text_text'),

                FileUpload::make('image')
                    ->label('Image')
                    ->disk('public')
                    ->directory('products/pdp/two-cols')
                    ->image()
                    ->visible(fn ($get) => $get('layout') !== 'text_text'
                        && $get('media_type') === 'image'),

                FileUpload::make('video')
                    ->label('Video (mp4/webm)')
                    ->disk('public')
                    ->directory('products/pdp/two-cols')
                    ->acceptedFileTypes(['video/mp4', 'video/webm'])
                    ->visible(fn ($get) => $get('layout') !== 'text_text'
                        && $get('media_type') === 'video'),

                FileUpload::make('poster')
                    ->label('Video poster (optional)')
                    ->disk('public')
                    ->directory('products/pdp/two-cols')
                    ->image()
                    ->visible(fn ($get) => $get('layout') !== 'text_text'
                        && $get('media_type') === 'video'),

                // ── Translatable text fields ──────────────────────────────
                self::blockLocaleTabs('twoCols_lang', [
                    ['name' => 'left_title', 'label' => 'Left title',  'type' => 'text'],
                    ['name' => 'left_html',  'label' => 'Left HTML',   'type' => 'html', 'rows' => 8],
                    ['name' => 'right_title','label' => 'Right title', 'type' => 'text'],
                    ['name' => 'right_html', 'label' => 'Right HTML',  'type' => 'html', 'rows' => 8],
                    ['name' => 'cta_label_l',  'label' => 'CTA label Left',   'type' => 'text'],
                    ['name' => 'cta_label_r',  'label' => 'CTA label Right',   'type' => 'text'],
                ]),

                TextInput::make('ctaL_url')->label('Left CTA URL'),
                TextInput::make('ctaR_url')
                    ->label('Right CTA URL')
                    ->visible(fn ($get) => $get('layout') === 'text_text'),

                Grid::make(2)->schema([
                    Toggle::make('public_clickable_l')
                        ->label('CTA clickable Left')
                        ->default(false),
                    Toggle::make('public_clickable_r')
                        ->label('CTA clickable Right')
                        ->visible(fn ($get) => $get('layout') === 'text_text')
                        ->default(false),
                ]),
            ]);
    }

    // ── PD Cards block ────────────────────────────────────────────────────────

    private static function pdCardsBlock(): Block
    {
        return Block::make('pdcards')
            ->label('Cards (2+) — Media top + text + CTA')
            ->schema([
                // Non-translatable
                ColorPicker::make('bg')->label('Section background')->default('#ffffff'),

                Grid::make(2)->schema([
                    Toggle::make('public_visible')
                        ->label('Visible when logged out')
                        ->default(true),
                    Toggle::make('public_clickable')
                        ->label('CTA clickable when logged out')
                        ->default(false),
                ]),

                // Section heading — translatable
                self::blockLocaleTabs('pdcards_heading_lang', [
                    ['name' => 'heading', 'label' => 'Section heading', 'type' => 'text'],
                ]),

                // ── Cards ─────────────────────────────────────────────────
                Repeater::make('items')
                    ->label('Cards')
                    ->minItems(2)
                    ->schema([
                        // Non-translatable per card
                        ColorPicker::make('card_bg')->label('Card background')->default('#ffffff'),
                        ColorPicker::make('cta_bg')->label('CTA background')->default('#0f172a'),
                        ColorPicker::make('text')->label('Text colour')->default('#0f172a'),
                        ColorPicker::make('html')->label('HTML colour')->default('#0f172a'),
                        ColorPicker::make('exrt')->label('Excerpt colour')->default('#475569'),

                        Grid::make(2)->schema([
                            Toggle::make('public_visible')
                                ->label('Visible when logged out')
                                ->default(true),
                            Toggle::make('public_clickable')
                                ->label('CTA clickable when logged out')
                                ->default(false),
                        ]),

                        Select::make('media_type')
                            ->options(['image' => 'Image', 'video' => 'Video'])
                            ->default('image'),

                        FileUpload::make('image')
                            ->disk('public')
                            ->directory('products/pdp/cards')
                            ->image()
                            ->visible(fn ($get) => $get('media_type') === 'image'),

                        FileUpload::make('video')
                            ->disk('public')
                            ->directory('products/pdp/cards')
                            ->acceptedFileTypes(['video/mp4', 'video/webm'])
                            ->visible(fn ($get) => $get('media_type') === 'video'),

                        FileUpload::make('poster')
                            ->disk('public')
                            ->directory('products/pdp/cards')
                            ->image()
                            ->visible(fn ($get) => $get('media_type') === 'video'),

                        // Translatable card text
                        self::blockLocaleTabs('card_lang', [
                            ['name' => 'title',     'label' => 'Title',    'type' => 'text'],
                            ['name' => 'excerpt',   'label' => 'Excerpt',  'type' => 'textarea', 'rows' => 3],
                            ['name' => 'body_html', 'label' => 'Body HTML','type' => 'html',     'rows' => 8],
                            ['name' => 'cta_label', 'label' => 'CTA label','type' => 'text'],
                        ]),

                        TextInput::make('cta_url')->label('CTA URL'),
                    ])
                    ->columns(1),
            ]);
    }

    // ── Doc Dropdown block ────────────────────────────────────────────────────

    private static function docDropdownBlock(): Block
    {
        return Block::make('docDropdown')
            ->label('Documents dropdown list')
            ->schema([
                // Section heading — translatable
                self::blockLocaleTabs('docDropdown_heading_lang', [
                    ['name' => 'heading', 'label' => 'Section heading', 'type' => 'text'],
                ]),

                Repeater::make('rows')
                    ->label('Document rows')
                    ->minItems(1)
                    ->schema([

                        // ── Upload OR paste URL ───────────────────────────────
                        FileUpload::make('file')
                            ->label('Upload document')
                            ->helperText('PDF, Word, Excel, PowerPoint, CSV, ZIP, etc. Max 20 MB. Leave empty if you paste a URL below.')
                            ->disk('public')
                            ->directory('products/documents')
                            ->visibility('public')
                            ->storeFileNamesIn('original_name')
                            ->acceptedFileTypes([
                                'application/pdf',
                                'application/msword',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                'application/vnd.ms-excel',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'application/vnd.ms-powerpoint',
                                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                                'text/csv',
                                'application/zip',
                                'application/x-zip-compressed',
                                'application/octet-stream',
                            ])
                            ->maxSize(20480)
                            ->columnSpanFull(),

                        TextInput::make('url')
                            ->label('…or paste a document URL')
                            ->helperText('Only used when no file is uploaded above. Absolute URL, e.g. https://example.com/spec.pdf')
                            ->url()
                            ->nullable()
                            ->columnSpanFull(),

                        Grid::make(2)->schema([
                            Toggle::make('downloadable')
                                ->label('Downloadable when logged out')
                                ->default(false),
                            Select::make('target')
                                ->options(['_blank' => '_blank', '_self' => '_self'])
                                ->default('_blank'),
                        ]),

                        // File name — translatable
                        self::blockLocaleTabs('doc_row_lang', [
                            ['name' => 'title', 'label' => 'File name / label', 'type' => 'text'],
                        ]),
                    ])
                    ->columns(1),
            ]);
    }
}