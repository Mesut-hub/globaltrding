<?php

namespace App\Filament\Resources\Pages\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\ColorPicker;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        $locales = config('locales.supported', ['en']);
        $default = config('locales.default', 'en');

        return $schema
            ->components([
                Grid::make(2)
                    ->schema([
                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')
                            ->unique(ignoreRecord: true)
                            ->helperText('URL identifier. Example: about-us')
                            ->afterStateUpdated(function (?string $state, callable $set) {
                                if (! is_string($state)) return;
                                $set('slug', Str::slug($state));
                            }),

                        Toggle::make('is_published')
                            ->label('Published')
                            ->default(true)
                            ->required(),
                        Toggle::make('show_in_footer')
                            ->label('Show in footer')
                            ->default(false),
                    ]),

                Builder::make('blocks')
                    ->label('Content blocks')
                    ->collapsed()
                    ->blocks([
                        Block::make('hero')
                            ->label('Hero (video or slider)')
                            ->schema([
                                Select::make('height')
                                    ->label('Hero height')
                                    ->options([
                                        'screen' => 'Full screen (100vh)',
                                        'xl' => 'Large',
                                        'lg' => 'Medium',
                                    ])
                                    ->default('screen')
                                    ->required(),

                                Select::make('content_position')
                                    ->label('Content position')
                                    ->options([
                                        'left' => 'Left',
                                        'center' => 'Center',
                                        'right' => 'Right',
                                    ])
                                    ->default('left')
                                    ->required(),

                                Select::make('content_align')
                                    ->label('Text alignment')
                                    ->options([
                                        'left' => 'Left',
                                        'center' => 'Center',
                                        'right' => 'Right',
                                    ])
                                    ->default('left')
                                    ->required(),

                                Select::make('title_size')
                                    ->label('Title size')
                                    ->options([
                                        'md' => 'Medium',
                                        'lg' => 'Large',
                                        'xl' => 'Extra large',
                                    ])
                                    ->default('xl')
                                    ->required(),

                                Select::make('lead_size')
                                    ->label('Lead size')
                                    ->options([
                                        'sm' => 'Small',
                                        'md' => 'Medium',
                                        'lg' => 'Large',
                                    ])
                                    ->default('md')
                                    ->required(),

                                ColorPicker::make('overlay_color')
                                    ->label('Overlay color')
                                    ->default('#000000'),

                                TextInput::make('overlay_opacity')
                                    ->label('Overlay opacity (0..1)')
                                    ->numeric()
                                    ->default(0.45),

                                TextInput::make('content_max_width')
                                    ->label('Content max width (px)')
                                    ->numeric()
                                    ->default(760)
                                    ->helperText('Example: 760'),

                                TextInput::make('content_offset_x')
                                    ->label('Content offset X (px)')
                                    ->numeric()
                                    ->default(140)
                                    ->helperText('Moves content right (+) or left (-).'),

                                TextInput::make('content_offset_y')
                                    ->label('Content offset Y (px)')
                                    ->numeric()
                                    ->default(-90)
                                    ->helperText('Moves content up (-) or down (+).'),

                                FileUpload::make('video')
                                    ->label('Hero video (optional, 1 file). If set, slider images are ignored.')
                                    ->disk('public')
                                    ->directory('pages/hero')
                                    ->acceptedFileTypes(['video/mp4', 'video/webm'])
                                    ->maxSize((auth()->user()?->maxUploadMb() ?? 150) * 1024),

                                FileUpload::make('images')
                                    ->label('Hero slider images (optional, multiple). Used only if no video.')
                                    ->disk('public')
                                    ->directory('pages/hero')
                                    ->image()
                                    ->multiple()
                                    ->reorderable()
                                    ->maxFiles(12),

                                Toggle::make('autoplay')
                                    ->label('Autoplay slider')
                                    ->default(true),

                                TextInput::make('interval_ms')
                                    ->label('Autoplay interval (ms)')
                                    ->numeric()
                                    ->default(4500),

                                Toggle::make('pause_on_hover')
                                    ->label('Pause autoplay on hover')
                                    ->default(true),

                                \Filament\Forms\Components\Repeater::make('slides')
                                    ->label('Slide text (one per image)')
                                    ->helperText('Order must match images. If fewer items than images, the first slide text will be reused.')
                                    ->schema([
                                        TextInput::make('kicker')->label('Kicker'),
                                        TextInput::make('title')->label('Title')->required(),
                                        Textarea::make('lead')->label('Lead')->rows(3),
                                        TextInput::make('cta_label')->label('CTA label'),
                                        TextInput::make('cta_url')->label('CTA URL'),
                                    ])
                                    ->minItems(1)
                                    ->defaultItems(1),
                            ]),

                        Block::make('sectionHeading')
                            ->label('Section heading')
                            ->schema([
                                TextInput::make('title')->required(),
                                Textarea::make('lead')->rows(3),
                            ]),

                        Block::make('richText')
                            ->label('Rich text')
                            ->schema([
                                TextInput::make('heading')->label('Heading'),
                                Textarea::make('html')
                                    ->label('HTML content')
                                    ->rows(10)
                                    ->helperText('Paste HTML content.'),
                            ]),

                        Block::make('insightsGrid')
                            ->label('Insights Grid (BASF-style)')
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
                                            ->visible(fn ($get) => $get('type') === 'image'),

                                        TextInput::make('title')
                                            ->required(),

                                        Textarea::make('text')
                                            ->rows(3),

                                        TextInput::make('cta_label')->default('Learn more'),
                                        TextInput::make('cta_url'),
                                    ])
                                    ->columns(2),
                            ]),

                        Block::make('splitV2')
                            ->label('Split (media + text)')
                            ->schema([
                                Select::make('bg')
                                    ->label('Background')
                                    ->options([
                                        'white' => 'White',
                                        'slate' => 'Light gray',
                                        'dark' => 'Dark',
                                    ])
                                    ->default('white')
                                    ->required(),

                                Select::make('media_side')
                                    ->label('Media side')
                                    ->options(['left' => 'Left', 'right' => 'Right'])
                                    ->default('left')
                                    ->required(),

                                FileUpload::make('video')
                                    ->label('Video (optional, max 1)')
                                    ->disk('public')
                                    ->directory('pages/split')
                                    ->acceptedFileTypes(['video/mp4', 'video/webm'])
                                    ->maxSize((auth()->user()?->maxUploadMb() ?? 150) * 1024),

                                FileUpload::make('images')
                                    ->label('Images (multiple)')
                                    ->disk('public')
                                    ->directory('pages/split')
                                    ->image()
                                    ->multiple()
                                    ->reorderable()
                                    ->maxFiles(12),

                                Repeater::make('thumbs')
                                    ->label('Thumbnails (image + title + text + button)')
                                    ->schema([
                                        FileUpload::make('images')
                                            ->disk('public')
                                            ->directory('pages/split/thumbs')
                                            ->image()
                                            ->required(),
                                        TextInput::make('title')->required(),
                                        Textarea::make('text')->rows(2),
                                        TextInput::make('cta_label')->default('Learn more'),
                                        TextInput::make('cta_url'),
                                    ])
                                    ->minItems(0)
                                    ->columns(2),

                                TextInput::make('kicker')->label('Kicker'),
                                TextInput::make('title')->label('Title')->required(),
                                Textarea::make('lead')->label('Lead')->rows(3),

                                Select::make('title_size')
                                    ->label('Title size')
                                    ->options(['md' => 'Medium', 'lg' => 'Large'])
                                    ->default('lg')
                                    ->required(),

                                Select::make('text_size')
                                    ->label('Text size')
                                    ->options(['sm' => 'Small', 'md' => 'Medium'])
                                    ->default('md')
                                    ->required(),

                                TextInput::make('cta_label')->label('CTA label'),
                                TextInput::make('cta_url')->label('CTA URL'),
                            ]),

                        Block::make('cardsCarousel')
                            ->label('Cards carousel (BASF-style)')
                            ->schema([
                                Select::make('bg')
                                    ->label('Background')
                                    ->options(['white' => 'White', 'slate' => 'Light gray', 'dark' => 'Dark'])
                                    ->default('white')
                                    ->required(),

                                TextInput::make('title')->label('Section title'),
                                Textarea::make('lead')->label('Section lead')->rows(2),

                                Select::make('title_size')
                                    ->label('Title size')
                                    ->options(['md' => 'Medium', 'lg' => 'Large'])
                                    ->default('lg')
                                    ->required(),

                                Select::make('text_size')
                                    ->label('Text size')
                                    ->options(['sm' => 'Small', 'md' => 'Medium'])
                                    ->default('md')
                                    ->required(),

                                Toggle::make('autoplay')
                                    ->label('Autoplay')
                                    ->default(false),

                                TextInput::make('autoplay_ms')
                                    ->label('Autoplay interval (ms)')
                                    ->numeric()
                                    ->default(4500),

                                Toggle::make('pause_on_hover')
                                    ->label('Pause on hover')
                                    ->default(true),

                                Repeater::make('items')
                                    ->minItems(1)
                                    ->schema([
                                        FileUpload::make('image')
                                            ->disk('public')
                                            ->directory('pages/cards')
                                            ->image(),
                                        TextInput::make('title')->required(),
                                        Textarea::make('text')->rows(3),
                                        TextInput::make('url')->label('Link URL'),
                                    ])
                                    ->columns(2),
                            ]),

                        Block::make('metrics')
                            ->label('Metrics bar')
                            ->schema([
                                Select::make('bg')
                                    ->label('Background')
                                    ->options(['white' => 'White', 'slate' => 'Light gray', 'dark' => 'Dark'])
                                    ->default('slate')
                                    ->required(),

                                TextInput::make('title')->label('Section title'),

                                Toggle::make('animate')
                                    ->label('Count-up animation')
                                    ->default(true),

                                Repeater::make('items')
                                    ->minItems(1)
                                    ->schema([
                                        TextInput::make('value')->label('Value')->required()->helperText('Example: 1500 or 12,000+'),
                                        TextInput::make('label')->label('Label')->required(),
                                    ])
                                    ->columns(2),
                            ]),

                        Block::make('cta')
                            ->label('Button / CTA')
                            ->schema([
                                TextInput::make('label')->required()->default('Learn more'),
                                TextInput::make('url')->required(),
                            ]),
                    ]),

                Tabs::make('Translations')
                    ->persistTabInQueryString()
                    ->tabs(
                        collect($locales)->map(function (string $locale) use ($default) {
                            $label = strtoupper($locale);

                            return Tab::make($label)
                                ->schema([
                                    TextInput::make("title.$locale")
                                        ->label("Title ($label)")
                                        ->required($locale === $default)
                                        ->maxLength(255),

                                    Textarea::make("content.$locale")
                                        ->label("Content ($label)")
                                        ->rows(10)
                                        ->required($locale === $default),

                                    Grid::make(2)
                                        ->schema([
                                            TextInput::make("seo.title.$locale")
                                                ->label("SEO Title ($label)")
                                                ->maxLength(60),

                                            Textarea::make("seo.description.$locale")
                                                ->label("SEO Description ($label)")
                                                ->rows(3)
                                                ->maxLength(160),
                                        ]),
                                ]);
                        })->values()->all()
                    ),
            ]);
    }
}