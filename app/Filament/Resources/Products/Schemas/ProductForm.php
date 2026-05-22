<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Brand;
use App\Support\Filament\MultiLangKeyValue;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
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
                    ->required()
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
                ->helperText('Example: /en/pages/my-product or https://...')
                ->maxLength(2048),

            TextInput::make('prd_number')
                ->label('PRD Number')
                ->maxLength(64),

            // ── PDP access controls (non-translatable) ───────────────────────
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
                    ->label('Documents section visible before login')
                    ->default(false),
            ]),

            Select::make('pdp_documents_logged_out_mode')
                ->label('When logged out: Documents behavior')
                ->options([
                    'list_disabled' => 'A) Show documents list, disable downloads',
                    'hide_all'      => 'B) Hide documents content (show notice only)',
                ])
                ->default('list_disabled')
                ->helperText('Only applies when Documents section is not public.'),

            // ── Translatable fields (one tab per locale) ─────────────────────
            Tabs::make('Translations')
                ->persistTabInQueryString()
                ->tabs(
                    collect($locales)->map(function (string $locale) use ($default) {
                        $label = strtoupper($locale);

                        return Tab::make($label)->schema([

                            Grid::make(2)->schema([
                                TextInput::make("display_name.$locale")
                                    ->label("Product name ($label)")
                                    ->required($locale === $default)
                                    ->maxLength(255),

                                TextInput::make("industry_label.$locale")
                                    ->label("Industry ($label)")
                                    ->maxLength(255),
                            ]),

                            Grid::make(2)->schema([
                                TextInput::make("seo.title.$locale")
                                    ->label("SEO Title ($label)")
                                    ->maxLength(60),
                                Textarea::make("seo.description.$locale")
                                    ->label("SEO Description ($label)")
                                    ->rows(3)
                                    ->maxLength(160),
                            ]),

                            // Finder filter tags (one value per line, stored as locale→string[])
                            Grid::make(2)->schema([
                                Textarea::make("industries.$locale")
                                    ->label("Industries — one per line ($label)")
                                    ->rows(4)
                                    ->formatStateUsing(fn ($state) => is_array($state) ? implode("\n", $state) : '')
                                    ->dehydrateStateUsing(fn ($state) => array_values(array_filter(
                                        array_map('trim', preg_split("/\r\n|\n|\r/", (string) $state))
                                    ))),

                                Textarea::make("applications.$locale")
                                    ->label("Applications — one per line ($label)")
                                    ->rows(4)
                                    ->formatStateUsing(fn ($state) => is_array($state) ? implode("\n", $state) : '')
                                    ->dehydrateStateUsing(fn ($state) => array_values(array_filter(
                                        array_map('trim', preg_split("/\r\n|\n|\r/", (string) $state))
                                    ))),
                            ]),

                            Grid::make(2)->schema([
                                Textarea::make("product_groups.$locale")
                                    ->label("Products Group — one per line ($label)")
                                    ->rows(4)
                                    ->formatStateUsing(fn ($state) => is_array($state) ? implode("\n", $state) : '')
                                    ->dehydrateStateUsing(fn ($state) => array_values(array_filter(
                                        array_map('trim', preg_split("/\r\n|\n|\r/", (string) $state))
                                    ))),

                                Textarea::make("processes.$locale")
                                    ->label("Processes — one per line ($label)")
                                    ->rows(4)
                                    ->formatStateUsing(fn ($state) => is_array($state) ? implode("\n", $state) : '')
                                    ->dehydrateStateUsing(fn ($state) => array_values(array_filter(
                                        array_map('trim', preg_split("/\r\n|\n|\r/", (string) $state))
                                    ))),
                            ]),

                            Grid::make(2)->schema([
                                Textarea::make("sustainability_tags.$locale")
                                    ->label("Sustainability — one per line ($label)")
                                    ->rows(4)
                                    ->formatStateUsing(fn ($state) => is_array($state) ? implode("\n", $state) : '')
                                    ->dehydrateStateUsing(fn ($state) => array_values(array_filter(
                                        array_map('trim', preg_split("/\r\n|\n|\r/", (string) $state))
                                    ))),

                                Textarea::make("regulatory_tags.$locale")
                                    ->label("Regulatory — one per line ($label)")
                                    ->rows(4)
                                    ->formatStateUsing(fn ($state) => is_array($state) ? implode("\n", $state) : '')
                                    ->dehydrateStateUsing(fn ($state) => array_values(array_filter(
                                        array_map('trim', preg_split("/\r\n|\n|\r/", (string) $state))
                                    ))),
                            ]),

                            // PDP section HTML (per locale)
                            Textarea::make("pdp_overview_html.$locale")
                                ->label("PDP — Overview HTML ($label)")
                                ->rows(8),

                            Textarea::make("pdp_properties_html.$locale")
                                ->label("PDP — Properties HTML ($label)")
                                ->rows(8),

                            Textarea::make("pdp_sustainability_html.$locale")
                                ->label("PDP — Sustainability HTML ($label)")
                                ->rows(8),

                        ]);
                    })->values()->all()
                )->columnSpanFull(),

            // ── PDP Builders (block structure is language-neutral;
            //    multilingual text lives inside KeyValue fields) ──────────────
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
    // Shared KeyValue helper — all KeyValue multilingual fields go through here
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Build a standardised multilingual KeyValue component.
     *
     * NOTE: We use ONLY ->formatStateUsing + ->dehydrateStateUsing.
     *
     * DO NOT add ->afterStateHydrated here.
     * Reason: afterStateHydrated fires AFTER KeyValueStateCast::get() already
     * ran. If a null state reaches the cast it crashes before afterStateHydrated
     * can guard it. The null-safety is handled upstream in
     * EditProduct::mutateFormDataBeforeFill() which normalises all block data
     * before Filament ever touches it.
     */
    private static function mlKeyValue(
        string $fieldName,
        string $label,
        ?string $valueLabelOverride = null,
        bool $required = false,
    ): KeyValue {
        $default = config('locales.default', 'en');

        $component = KeyValue::make($fieldName)
            ->label($label)
            ->default([])
            ->keyLabel('Locale')
            ->valueLabel($valueLabelOverride ?? 'Value')
            ->formatStateUsing(fn ($state) => MultiLangKeyValue::normalize($state))
            ->dehydrateStateUsing(fn ($state) => MultiLangKeyValue::dehydrate($state));

        if ($required) {
            $component->rules([
                function () use ($default, $label) {
                    return function (string $attribute, mixed $value, \Closure $fail) use ($default, $label) {
                        $arr = MultiLangKeyValue::normalize($value);
                        if (trim((string) ($arr[$default] ?? '')) === '') {
                            $fail("{$label} must have a non-empty '{$default}' value.");
                        }
                    };
                },
            ]);
        }

        return $component;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PDP block definitions
    // ─────────────────────────────────────────────────────────────────────────

    private static function pdpBlocks(): array
    {
        return [
            self::twoColsBlock(),
            self::pdCardsBlock(),
        ];
    }

    private static function twoColsBlock(): Block
    {
        return Block::make('twoCols')
            ->label('Two columns (Text / Text or Text / Media)')
            ->schema([
                ColorPicker::make('bg')->label('Background')->default('#ffffff'),

                Toggle::make('public_visible')
                    ->label('Visible when logged out (if section is public)')
                    ->default(true),
                Toggle::make('public_clickable')
                    ->label('CTA clickable when logged out')
                    ->default(false),

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
                    ->default('image'),

                FileUpload::make('image')
                    ->label('Image')
                    ->disk('public')
                    ->directory('products/pdp/two-cols')
                    ->image()
                    ->visible(fn ($get) => in_array($get('layout'), ['text_media', 'media_text'], true)
                        && $get('media_type') === 'image'),

                FileUpload::make('video')
                    ->label('Video (mp4/webm)')
                    ->disk('public')
                    ->directory('products/pdp/two-cols')
                    ->acceptedFileTypes(['video/mp4', 'video/webm'])
                    ->visible(fn ($get) => in_array($get('layout'), ['text_media', 'media_text'], true)
                        && $get('media_type') === 'video'),

                FileUpload::make('poster')
                    ->label('Video poster (optional)')
                    ->disk('public')
                    ->directory('products/pdp/two-cols')
                    ->image()
                    ->visible(fn ($get) => in_array($get('layout'), ['text_media', 'media_text'], true)
                        && $get('media_type') === 'video'),

                self::mlKeyValue('left_title',  'Left column title (multilingual)',  'Title'),
                self::mlKeyValue('left_html',   'Left column HTML (multilingual)',   'HTML'),
                self::mlKeyValue('right_title', 'Right column title (multilingual)', 'Title'),
                self::mlKeyValue('right_html',  'Right column HTML (multilingual)',  'HTML'),
                self::mlKeyValue('cta_label',   'CTA label (multilingual)',          'Label'),

                TextInput::make('cta_url')->label('CTA URL'),
            ]);
    }

    private static function pdCardsBlock(): Block
    {
        return Block::make('pdcards')
            ->label('Cards (2+) — Media top + text + CTA')
            ->schema([
                ColorPicker::make('bg')->label('Background')->default('#ffffff'),

                Toggle::make('public_visible')
                    ->label('Visible when logged out (if section is public)')
                    ->default(true),
                Toggle::make('public_clickable')
                    ->label('CTA clickable when logged out')
                    ->default(false),

                self::mlKeyValue('heading', 'Heading (multilingual)', 'Heading'),

                Repeater::make('items')
                    ->label('Cards')
                    ->minItems(2)
                    ->schema([
                        ColorPicker::make('bg')->label('Card background')->default('#ffffff'),

                        Toggle::make('public_visible')
                            ->label('Visible when logged out')
                            ->default(true),
                        Toggle::make('public_clickable')
                            ->label('CTA clickable when logged out')
                            ->default(false),

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

                        self::mlKeyValue('title',    'Title (multilingual)',    'Title',   required: true),
                        self::mlKeyValue('excerpt',  'Excerpt (multilingual)',  'Excerpt'),
                        self::mlKeyValue('body_html','Body HTML (multilingual)','HTML'),
                        self::mlKeyValue('cta_label','CTA label (multilingual)','Label'),

                        TextInput::make('cta_url')->label('CTA URL'),
                    ])
                    ->columns(2),
            ]);
    }

    private static function docDropdownBlock(): Block
    {
        $default = config('locales.default', 'en');

        return Block::make('docDropdown')
            ->label('Documents dropdown list')
            ->schema([
                self::mlKeyValue('heading', 'Heading (multilingual)', 'Heading'),

                Repeater::make('rows')
                    ->label('Document rows')
                    ->minItems(1)
                    ->schema([
                        self::mlKeyValue('title', 'File name (multilingual)', 'Title', required: true),

                        TextInput::make('url')
                            ->label('Document URL')
                            ->required(),

                        Toggle::make('downloadable')
                            ->label('Downloadable when logged out (if Documents is public)')
                            ->default(false),

                        Select::make('target')
                            ->options(['_blank' => '_blank', '_self' => '_self'])
                            ->default('_blank'),
                    ])
                    ->columns(2),
            ]);
    }
}