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
            // ===== Basics (NOT multilingual) =====
            Grid::make(2)->schema([
                Select::make('brand_id')
                    ->label('Brand')
                    ->required()
                    ->options(fn () => Brand::query()
                        ->where('is_published', true)
                        ->orderBy('id')
                        ->get()
                        ->mapWithKeys(fn (Brand $b) => [$b->id => (data_get($b->name, 'en') ?: "Brand #{$b->id}")])
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
                ->label('Slug')
                ->required()
                ->maxLength(255)
                ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')
                ->unique(ignoreRecord: true)
                ->helperText('Example: pd-al-5637-e-1-12')
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

            // ===== PDP Access controls (NOT multilingual) =====
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
                ->helperText('If Documents section is not visible before login, this setting applies only after login banner is shown.'),

            // ===== Translations Tabs (ONLY multilingual fields inside) =====
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

                            // Finder filters: store as locale -> list (array of strings)
                            Grid::make(2)->schema([
                                Textarea::make("industries.$locale")
                                    ->label("Industries (one per line) ($label)")
                                    ->rows(4)
                                    ->formatStateUsing(fn ($state) => is_array($state) ? implode("\n", $state) : '')
                                    ->dehydrateStateUsing(fn ($state) => array_values(array_filter(array_map('trim', preg_split("/\r\n|\n|\r/", (string) $state))))),

                                Textarea::make("applications.$locale")
                                    ->label("Applications (one per line) ($label)")
                                    ->rows(4)
                                    ->formatStateUsing(fn ($state) => is_array($state) ? implode("\n", $state) : '')
                                    ->dehydrateStateUsing(fn ($state) => array_values(array_filter(array_map('trim', preg_split("/\r\n|\n|\r/", (string) $state))))),
                            ]),

                            Grid::make(2)->schema([
                                Textarea::make("product_groups.$locale")
                                    ->label("Products Group (one per line) ($label)")
                                    ->rows(4)
                                    ->formatStateUsing(fn ($state) => is_array($state) ? implode("\n", $state) : '')
                                    ->dehydrateStateUsing(fn ($state) => array_values(array_filter(array_map('trim', preg_split("/\r\n|\n|\r/", (string) $state))))),

                                Textarea::make("processes.$locale")
                                    ->label("Processes (one per line) ($label)")
                                    ->rows(4)
                                    ->formatStateUsing(fn ($state) => is_array($state) ? implode("\n", $state) : '')
                                    ->dehydrateStateUsing(fn ($state) => array_values(array_filter(array_map('trim', preg_split("/\r\n|\n|\r/", (string) $state))))),
                            ]),

                            Grid::make(2)->schema([
                                Textarea::make("sustainability_tags.$locale")
                                    ->label("Sustainability (one per line) ($label)")
                                    ->rows(4)
                                    ->formatStateUsing(fn ($state) => is_array($state) ? implode("\n", $state) : '')
                                    ->dehydrateStateUsing(fn ($state) => array_values(array_filter(array_map('trim', preg_split("/\r\n|\n|\r/", (string) $state))))),

                                Textarea::make("regulatory_tags.$locale")
                                    ->label("Regulatory (one per line) ($label)")
                                    ->rows(4)
                                    ->formatStateUsing(fn ($state) => is_array($state) ? implode("\n", $state) : '')
                                    ->dehydrateStateUsing(fn ($state) => array_values(array_filter(array_map('trim', preg_split("/\r\n|\n|\r/", (string) $state))))),
                            ]),

                            // PDP sections (HTML) localized
                            Textarea::make("pdp_overview_html.$locale")
                                ->label("PDP - Overview (HTML) ($label)")
                                ->rows(8),

                            Textarea::make("pdp_properties_html.$locale")
                                ->label("PDP - Properties (HTML) ($label)")
                                ->rows(8),

                            Textarea::make("pdp_sustainability_html.$locale")
                                ->label("PDP - Sustainability (HTML) ($label)")
                                ->rows(8),
                        ]);
                    })->values()->all()
                ),

            // ===== PDP Builders (NOT inside translation tabs) =====
            // IMPORTANT: Builder blocks remain single structure; multilingual strings inside blocks are KeyValue fields
            Builder::make('pdp_overview_blocks')
                ->label('PDP - Overview blocks')
                ->collapsed()
                ->blocks(self::pdpBlocks()),

            Builder::make('pdp_properties_blocks')
                ->label('PDP - Properties blocks')
                ->collapsed()
                ->blocks(self::pdpBlocks()),

            Builder::make('pdp_documents_blocks')
                ->label('PDP - Documents blocks')
                ->collapsed()
                ->blocks([
                    Block::make('docDropdown')
                        ->label('Documents dropdown list')
                        ->schema([
                            // Multilingual heading stored as {en:..., tr:...}
                            KeyValue::make('heading')
                                ->label('Heading (multilingual)')
                                ->default([])
                                ->keyLabel('Locale')
                                ->valueLabel('Heading')
                                ->formatStateUsing(fn ($state) => MultiLangKeyValue::normalize($state))
                                ->dehydrateStateUsing(fn ($state) => MultiLangKeyValue::dehydrate($state)),

                            Repeater::make('rows')
                                ->label('Document rows')
                                ->minItems(1)
                                ->schema([
                                    KeyValue::make('title')
                                        ->label('File name (multilingual)')
                                        ->default([])
                                        ->keyLabel('Locale')
                                        ->valueLabel('Title')
                                        ->formatStateUsing(fn ($state) => MultiLangKeyValue::normalize($state))
                                        ->dehydrateStateUsing(fn ($state) => MultiLangKeyValue::dehydrate($state))
                                        ->rules([
                                            function () use ($default) {
                                                return function (string $attribute, $value, $fail) use ($default) {
                                                    $arr = MultiLangKeyValue::normalize($value);
                                                    if (trim((string) ($arr[$default] ?? '')) === '') {
                                                        $fail("Document title must include a non-empty '{$default}' value.");
                                                    }
                                                };
                                            },
                                        ]),

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
                        ]),
                ]),

            Builder::make('pdp_sustainability_blocks')
                ->label('PDP - Sustainability blocks')
                ->collapsed()
                ->blocks(self::pdpBlocks()),
        ]);
    }

    private static function pdpBlocks(): array
    {
        $default = config('locales.default', 'en');

        return [
            // 1) Two columns block
            Block::make('twoCols')
                ->label('Two columns (Text/Text or Text/Media)')
                ->schema([
                    ColorPicker::make('bg')->label('Background')->default('#ffffff'),

                    Toggle::make('public_visible')
                        ->label('Visible when logged out (if section is public)')
                        ->default(true),

                    Toggle::make('public_clickable')
                        ->label('CTA/clickable when logged out')
                        ->default(false),

                    Select::make('layout')
                        ->label('Layout')
                        ->options([
                            'text_text' => 'Text | Text',
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
                        ->visible(fn ($get) => in_array($get('layout'), ['text_media', 'media_text'], true) && $get('media_type') === 'image'),

                    FileUpload::make('video')
                        ->label('Video (mp4/webm)')
                        ->disk('public')
                        ->directory('products/pdp/two-cols')
                        ->acceptedFileTypes(['video/mp4', 'video/webm'])
                        ->visible(fn ($get) => in_array($get('layout'), ['text_media', 'media_text'], true) && $get('media_type') === 'video'),

                    FileUpload::make('poster')
                        ->label('Video poster (optional)')
                        ->disk('public')
                        ->directory('products/pdp/two-cols')
                        ->image()
                        ->visible(fn ($get) => in_array($get('layout'), ['text_media', 'media_text'], true) && $get('media_type') === 'video'),

                    KeyValue::make('left_title')
                        ->label('Left column title (multilingual)')
                        ->default([])
                        ->keyLabel('Locale')
                        ->valueLabel('Title')
                        ->formatStateUsing(fn ($state) => MultiLangKeyValue::normalize($state))
                        ->dehydrateStateUsing(fn ($state) => MultiLangKeyValue::dehydrate($state)),

                    KeyValue::make('left_html')
                        ->label('Left column HTML (multilingual)')
                        ->default([])
                        ->keyLabel('Locale')
                        ->valueLabel('HTML')
                        ->formatStateUsing(fn ($state) => MultiLangKeyValue::normalize($state))
                        ->dehydrateStateUsing(fn ($state) => MultiLangKeyValue::dehydrate($state)),

                    KeyValue::make('right_title')
                        ->label('Right column title (multilingual)')
                        ->default([])
                        ->keyLabel('Locale')
                        ->valueLabel('Title')
                        ->formatStateUsing(fn ($state) => MultiLangKeyValue::normalize($state))
                        ->dehydrateStateUsing(fn ($state) => MultiLangKeyValue::dehydrate($state)),

                    KeyValue::make('right_html')
                        ->label('Right column HTML (multilingual)')
                        ->default([])
                        ->keyLabel('Locale')
                        ->valueLabel('HTML')
                        ->formatStateUsing(fn ($state) => MultiLangKeyValue::normalize($state))
                        ->dehydrateStateUsing(fn ($state) => MultiLangKeyValue::dehydrate($state)),

                    KeyValue::make('cta_label')
                        ->label('CTA label (multilingual)')
                        ->default([])
                        ->keyLabel('Locale')
                        ->valueLabel('Label')
                        ->formatStateUsing(fn ($state) => MultiLangKeyValue::normalize($state))
                        ->dehydrateStateUsing(fn ($state) => MultiLangKeyValue::dehydrate($state))
                        ->rules([
                            function () use ($default) {
                                return function (string $attribute, $value, $fail) use ($default) {
                                    $arr = MultiLangKeyValue::normalize($value);
                                    // allow empty cta entirely; if present, require default
                                    if (count($arr) && trim((string) ($arr[$default] ?? '')) === '') {
                                        $fail("CTA label must include a non-empty '{$default}' value (or leave CTA empty).");
                                    }
                                };
                            },
                        ]),

                    TextInput::make('cta_url')->label('CTA URL'),
                ]),

            // 2) Cards block
            Block::make('pdcards')
                ->label('Cards (2+), Media top + text + CTA')
                ->schema([
                    ColorPicker::make('bg')->label('Background')->default('#ffffff'),

                    Toggle::make('public_visible')
                        ->label('Visible when logged out (if section is public)')
                        ->default(true),

                    Toggle::make('public_clickable')
                        ->label('CTA/clickable when logged out')
                        ->default(false),

                    KeyValue::make('heading')
                        ->label('Heading (multilingual)')
                        ->default([])
                        ->keyLabel('Locale')
                        ->valueLabel('Heading')
                        ->formatStateUsing(fn ($state) => MultiLangKeyValue::normalize($state))
                        ->dehydrateStateUsing(fn ($state) => MultiLangKeyValue::dehydrate($state)),

                    Repeater::make('items')
                        ->label('Cards')
                        ->minItems(2)
                        ->schema([
                            ColorPicker::make('bg')->label('Card Background')->default('#ffffff'),

                            Toggle::make('public_visible')
                                ->label('Visible when logged out (if section is public)')
                                ->default(true),

                            Toggle::make('public_clickable')
                                ->label('CTA/clickable when logged out')
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

                            KeyValue::make('title')
                                ->label('Title (multilingual)')
                                ->default([])
                                ->keyLabel('Locale')
                                ->valueLabel('Title')
                                ->formatStateUsing(fn ($state) => MultiLangKeyValue::normalize($state))
                                ->dehydrateStateUsing(fn ($state) => MultiLangKeyValue::dehydrate($state))
                                ->rules([
                                    function () use ($default) {
                                        return function (string $attribute, $value, $fail) use ($default) {
                                            $arr = MultiLangKeyValue::normalize($value);
                                            if (trim((string) ($arr[$default] ?? '')) === '') {
                                                $fail("Card title must include a non-empty '{$default}' value.");
                                            }
                                        };
                                    },
                                ]),

                            KeyValue::make('excerpt')
                                ->label('Excerpt (multilingual)')
                                ->default([])
                                ->keyLabel('Locale')
                                ->valueLabel('Excerpt')
                                ->formatStateUsing(fn ($state) => MultiLangKeyValue::normalize($state))
                                ->dehydrateStateUsing(fn ($state) => MultiLangKeyValue::dehydrate($state)),

                            KeyValue::make('body_html')
                                ->label('Body (HTML) (multilingual)')
                                ->default([])
                                ->keyLabel('Locale')
                                ->valueLabel('HTML')
                                ->formatStateUsing(fn ($state) => MultiLangKeyValue::normalize($state))
                                ->dehydrateStateUsing(fn ($state) => MultiLangKeyValue::dehydrate($state)),

                            KeyValue::make('cta_label')
                                ->label('CTA label (multilingual)')
                                ->default([])
                                ->keyLabel('Locale')
                                ->valueLabel('Label')
                                ->formatStateUsing(fn ($state) => MultiLangKeyValue::normalize($state))
                                ->dehydrateStateUsing(fn ($state) => MultiLangKeyValue::dehydrate($state)),

                            TextInput::make('cta_url')->label('CTA URL'),
                        ])
                        ->columns(2),
                ]),
        ];
    }
}