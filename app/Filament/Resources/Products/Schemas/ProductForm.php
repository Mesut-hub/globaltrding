<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Brand;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        $locales = config('locales.supported', ['en']);
        $default = config('locales.default', 'en');

        return $schema->components([
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
                ->required()
                ->maxLength(255)
                ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')
                ->unique(ignoreRecord: true)
                ->helperText('Example: pd-al-5637-e-1-12'),

            Tabs::make('Translations')
                    ->persistTabInQueryString()
                    ->tabs(
                        collect($locales)->map(function (string $locale) use ($default) {
                            $label = strtoupper($locale);

                            return Tab::make($label)
                                ->schema([
            Grid::make(2)->schema([
                TextInput::make("display_name.$locale")
                    ->label("Product name ($label)")
                    ->required($locale === $default),

                TextInput::make('display_url')
                    ->label('Product name URL (related page)')
                    ->helperText('Example: /en/pages/my-product or https://...')
                    ->maxLength(2048),
            ]),

            Grid::make(2)->schema([
                TextInput::make('prd_number')
                    ->label('PRD Number')
                    ->maxLength(64),

                TextInput::make("industry_label.$locale")
                    ->label("Industry ($label)"),
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

            // ===== PDP Access controls =====
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
                    // Documents dropdown list (exactly what you requested)
                    Block::make('docDropdown')
                        ->label('Documents dropdown list')
                        ->schema([
                            TextInput::make("heading.$locale")->label("Heading ($label)")->default('Statements - Regulatory'),
                            Repeater::make('rows')
                                ->label('Document rows')
                                ->minItems(1)
                                ->schema([
                                    TextInput::make("title.$locale")->label("File name ($label)")->required(),
                                    TextInput::make('url')->label('Document URL')->required(),
                                    Toggle::make('downloadable')
                                        ->label('Downloadable when logged out (if Documents is public)')
                                        ->default(false),
                                    Select::make('target')->options(['_blank' => '_blank', '_self' => '_self'])->default('_blank'),
                                ])
                                ->columns(2),
                        ]),
                ]),

            Builder::make('pdp_sustainability_blocks')
                ->label('PDP - Sustainability blocks')
                ->collapsed()
                ->blocks(self::pdpBlocks()),

            // Finder filters stored as arrays
            Grid::make(2)->schema([
                Textarea::make("industries.$locale")
                    ->label("Industries (one per line) ($label)")
                    ->rows(4),

                Textarea::make("applications.$locale")
                    ->label("Applications (one per line) ($label)")
                    ->rows(4),
            ]),

            Grid::make(2)->schema([
                Textarea::make("product_groups.$locale")
                    ->label("Products Group (one per line) ($label)")
                    ->rows(4)
                    ->formatStateUsing(fn ($state) => is_array($state) ? implode("\n", $state) : '')
                    ->dehydrateStateUsing(fn ($state) => array_values(array_filter(array_map('trim',
                        preg_split("/\r\n|\n|\r/", (string) $state)
                    )))),

                Textarea::make("processes.$locale")
                    ->label("Processes (one per line) ($label)")
                    ->rows(4),
            ]),

            Grid::make(2)->schema([
                Textarea::make("sustainability_tags.$locale")
                    ->label("Sustainability (one per line) ($label)")
                    ->rows(4),

                Textarea::make("regulatory_tags.$locale")
                    ->label("Regulatory (one per line) ($label)")
                    ->rows(4),
            ]),

            // PDP sections
            Textarea::make("pdp_overview_html.$locale")->label("PDP - Overview (HTML) ($label)")->rows(8),
            Textarea::make("pdp_properties_html.$locale")->label("PDP - Properties (HTML) ($label)")->rows(8),
            Textarea::make("pdp_sustainability_html.$locale")->label("PDP - Sustainability (HTML) ($label)")->rows(8),

            Repeater::make('pdp_documents')
                ->label('PDP - Documents')
                ->minItems(0)
                ->schema([
                    TextInput::make("title.$locale")->required(),
                    TextInput::make('url')->required(),
                    TextInput::make('language')->label('Language')->default('English'),
                    TextInput::make('category')->default('Statements - Regulatory'),
                ])
                ->columns(2),
                ]);
                        })->values()->all()
                    ),
        ]);
    }

    private static function pdpBlocks(): array
    {
        return [
            // 1) Two columns block (text/text OR text/media) with CTA + bg color + media side
            \Filament\Forms\Components\Builder\Block::make('twoCols')
                ->label('Two columns (Text/Text or Text/Media)')
                ->schema([
                    ColorPicker::make('bg')->label('Background')->default('#ffffff'),
                    ColorPicker::make('card_bg')->label('Card_Background')->default('#ffffff'),
                    ColorPicker::make('cta_bg')->label('CTA_Background')->default('#ffffff'),
                    ColorPicker::make('text')->label('CTA_Color')->default('#ffffff'),
                    ColorPicker::make('html')->label('HTML_Color')->default('#ffffff'),
                    Toggle::make('public_visible')->label('Visible when logged out (if section is public)')->default(true),
                    Toggle::make('public_clickable_r')->label('CTA/clickable when logged out Right')->default(false),
                    Toggle::make('public_clickable_l')->label('CTA/clickable when logged out Left')->default(false),

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
                        ->visible(fn ($get) => in_array($get('layout'), ['text_media','media_text'], true) && $get('media_type') === 'image'),

                    FileUpload::make('video')
                        ->label('Video (mp4/webm)')
                        ->disk('public')
                        ->directory('products/pdp/two-cols')
                        ->acceptedFileTypes(['video/mp4','video/webm'])
                        ->visible(fn ($get) => in_array($get('layout'), ['text_media','media_text'], true) && $get('media_type') === 'video'),

                    FileUpload::make('poster')
                        ->label('Video poster (optional)')
                        ->disk('public')
                        ->directory('products/pdp/two-cols')
                        ->image()
                        ->visible(fn ($get) => in_array($get('layout'), ['text_media','media_text'], true) && $get('media_type') === 'video'),

                    TextInput::make('left_title')->label('Left column title'),
                    Textarea::make('left_html')->label('Left column HTML')->rows(6),

                    TextInput::make('right_title')->label('Right column title'),
                    Textarea::make('right_html')->label('Right column HTML')->rows(6),

                    TextInput::make('cta_label')->label('CTA label'),
                    TextInput::make('ctaL_url')->label('CTA Left URL'),
                    TextInput::make('ctaR_url')->label('CTA Right URL'),
                ]),

            // 2) Cards block (2+ pdcards) image/video top + content + CTA
            \Filament\Forms\Components\Builder\Block::make('pdcards')
                ->label('Cards (2+), Media top + text + CTA')
                ->schema([
                    ColorPicker::make('bg')->label('Background')->default('#ffffff'),
                    Toggle::make('public_visible')->label('Visible when logged out (if section is public)')->default(true),
                    Toggle::make('public_clickable')->label('CTA/clickable when logged out')->default(false),
                    TextInput::make('heading')->label('Heading'),
                    Repeater::make('items')
                        ->label('Cards')
                        ->minItems(2)
                        ->schema([
                            ColorPicker::make('card_bg')->label('Card_Background')->default('#ffffff'),
                            ColorPicker::make('cta_bg')->label('CTA_Background')->default('#ffffff'),
                            ColorPicker::make('text')->label('CTA_Color')->default('#ffffff'),
                            ColorPicker::make('html')->label('HTML_Color')->default('#ffffff'),
                            ColorPicker::make('exrt')->label('EXRT_Color')->default('#ffffff'),
                            Toggle::make('public_visible')->label('Visible when logged out (if section is public)')->default(true),
                            Toggle::make('public_clickable')->label('CTA/clickable when logged out')->default(false),
                            Select::make('media_type')->options(['image'=>'Image','video'=>'Video'])->default('image'),
                            FileUpload::make('image')
                                ->disk('public')->directory('products/pdp/cards')->image()
                                ->visible(fn ($get) => $get('media_type') === 'image'),
                            FileUpload::make('video')
                                ->disk('public')->directory('products/pdp/cards')
                                ->acceptedFileTypes(['video/mp4','video/webm'])
                                ->visible(fn ($get) => $get('media_type') === 'video'),
                            FileUpload::make('poster')
                                ->disk('public')->directory('products/pdp/cards')->image()
                                ->visible(fn ($get) => $get('media_type') === 'video'),

                            TextInput::make('title')->required(),
                            Textarea::make('body_html')->label('Body (HTML)')->rows(4),
                            TextInput::make('excerpt')->label('Excerpt'),
                            TextInput::make('cta_label')->label('CTA label'),
                            TextInput::make('cta_url')->label('CTA URL'),
                        ])
                        ->columns(2),
                ]),
        ];
    }
}