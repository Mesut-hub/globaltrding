<?php
// app/Filament/Resources/Promotions/Schemas/PromotionForm.php

namespace App\Filament\Resources\Promotions\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class PromotionForm
{
    public static function configure(Schema $schema): Schema
    {
        $locales = config('locales.supported', ['en']);
        $default = config('locales.default', 'en');

        return $schema->components([

            // ── Schedule & Status ───────────────────────────────────────────
            Section::make('Schedule & Status')
                ->columns(2)
                ->schema([
                    Toggle::make('is_active')
                        ->label('Active')
                        ->default(true)
                        ->columnSpanFull(),

                    TextInput::make('priority')
                        ->label('Priority')
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->maxValue(999)
                        ->helperText('Higher value = shown first when multiple promotions match.'),

                    DateTimePicker::make('starts_at')
                        ->label('Start Date / Time')
                        ->helperText('Leave empty to start immediately.'),

                    DateTimePicker::make('ends_at')
                        ->label('End Date / Time')
                        ->helperText('Leave empty for no expiry.'),
                ]),

            // ── Display Behaviour ───────────────────────────────────────────
            Section::make('Display Behaviour')
                ->columns(2)
                ->schema([
                    Select::make('display_mode')
                        ->label('Trigger Mode')
                        ->options([
                            'manual' => 'Manual — header button click only',
                            'auto'   => 'Auto — show on page load',
                        ])
                        ->default('manual')
                        ->required()
                        ->reactive()
                        ->helperText('Manual = only shows when visitor clicks the announcement icon. Auto = appears automatically after a delay.'),

                    Select::make('display_frequency')
                        ->label('Display Frequency')
                        ->options([
                            'always'           => 'Always (every trigger)',
                            'once_per_session' => 'Once per browser session',
                            'once_per_day'     => 'Once per 24 hours',
                            'once_per_week'    => 'Once per 7 days',
                            'once_ever'        => 'Once ever (permanent dismiss)',
                        ])
                        ->default('once_per_session')
                        ->required()
                        ->helperText('Frequency is tracked per visitor in localStorage / sessionStorage.'),

                    TextInput::make('auto_show_delay_ms')
                        ->label('Auto-Show Delay (ms)')
                        ->numeric()
                        ->default(2500)
                        ->minValue(500)
                        ->maxValue(30000)
                        ->helperText('Milliseconds after page load before auto-showing. Min: 500ms.')
                        ->visible(fn ($get) => $get('display_mode') === 'auto'),

                    Textarea::make('target_pages')
                        ->label('Target Pages (URL Patterns)')
                        ->helperText('One pattern per line. Use * to match all pages, or /en/products* for a section. Leave as * for all pages.')
                        ->rows(4)
                        ->placeholder("*\n/en/products*\n/en/industries")
                        ->columnSpanFull()
                        ->afterStateHydrated(function ($component, $state) {
                            if (is_array($state)) {
                                $component->state(implode("\n", $state));
                            } elseif (is_string($state)) {
                                $decoded = json_decode($state, true);
                                $component->state(is_array($decoded) ? implode("\n", $decoded) : ($state ?: '*'));
                            } else {
                                $component->state('*');
                            }
                        })
                        ->dehydrateStateUsing(function ($state): array {
                            $lines = array_values(array_filter(
                                array_map('trim', explode("\n", (string) ($state ?? '*')))
                            ));
                            return $lines ?: ['*'];
                        }),
                ]),

            // ── Media ───────────────────────────────────────────────────────
            Section::make('Media')
                ->columns(2)
                ->schema([
                    Select::make('media_type')
                        ->label('Media Type')
                        ->options([
                            'none'  => 'None — text only',
                            'image' => 'Image',
                            'video' => 'Video',
                        ])
                        ->default('none')
                        ->required()
                        ->reactive(),

                    FileUpload::make('media_path')
                        ->label('Image')
                        ->disk('public')
                        ->directory('promotions/media')
                        ->image()
                        ->imageEditor()
                        ->maxSize(10240)
                        ->helperText('Recommended: 1200×630px or 16:9 ratio. Max 10MB.')
                        ->visible(fn ($get) => $get('media_type') === 'image'),

                    FileUpload::make('media_path')
                        ->label('Video')
                        ->disk('public')
                        ->directory('promotions/media')
                        ->acceptedFileTypes(['video/mp4', 'video/webm'])
                        ->maxSize((auth()->user()?->maxUploadMb() ?? 150) * 1024)
                        ->helperText('Accepted: MP4, WebM. The video will autoplay muted and loop.')
                        ->visible(fn ($get) => $get('media_type') === 'video'),

                    FileUpload::make('thumbnail_path')
                        ->label('Poster / Thumbnail Image')
                        ->disk('public')
                        ->directory('promotions/thumbnails')
                        ->image()
                        ->maxSize(4096)
                        ->helperText('Displayed while video loads, or as image fallback.')
                        ->visible(fn ($get) => $get('media_type') === 'video'),
                ]),

            // ── Appearance ──────────────────────────────────────────────────
            Section::make('Overlay Appearance')
                ->columns(2)
                ->schema([
                    Select::make('animation_type')
                        ->label('Entry Animation')
                        ->options([
                            'fade'       => 'Fade in',
                            'slide_up'   => 'Slide up',
                            'slide_down' => 'Slide from top',
                            'zoom'       => 'Zoom in',
                        ])
                        ->default('slide_up')
                        ->required(),

                    Select::make('overlay_size')
                        ->label('Overlay Width')
                        ->options([
                            'sm'   => 'Small (400px)',
                            'md'   => 'Medium (560px)',
                            'lg'   => 'Large (760px)',
                            'xl'   => 'Extra large (960px)',
                            'full' => 'Full viewport width',
                        ])
                        ->default('md')
                        ->required(),

                    Select::make('overlay_position')
                        ->label('Screen Position')
                        ->options([
                            'center'       => 'Centered',
                            'bottom'       => 'Bottom center',
                            'bottom-left'  => 'Bottom left',
                            'bottom-right' => 'Bottom right',
                        ])
                        ->default('center')
                        ->required(),

                    Select::make('cta_target')
                        ->label('CTA Link Target')
                        ->options(['_self' => 'Same tab', '_blank' => 'New tab'])
                        ->default('_self'),

                    Toggle::make('show_close_button')
                        ->label('Show close (×) button')
                        ->default(true),

                    Toggle::make('close_on_backdrop')
                        ->label('Close when clicking backdrop')
                        ->default(true),

                    ColorPicker::make('bg_color')
                        ->label('Overlay background color')
                        ->default('#ffffff'),

                    ColorPicker::make('text_color')
                        ->label('Body text color')
                        ->default('#0f172a'),

                    ColorPicker::make('cta_bg_color')
                        ->label('CTA button background')
                        ->default('#0f172a'),

                    ColorPicker::make('cta_text_color')
                        ->label('CTA button text color')
                        ->default('#ffffff'),
                ]),

            // ── Multilingual Content ─────────────────────────────────────────
            Tabs::make('Translations')
                ->columnSpanFull()
                ->tabs(
                    collect($locales)->map(function (string $locale) use ($default): Tab {
                        $lbl = strtoupper($locale);

                        return Tab::make($lbl)->schema([
                            TextInput::make("title.{$locale}")
                                ->label("Headline ({$lbl})")
                                ->required($locale === $default)
                                ->maxLength(120)
                                ->helperText('Short, attention-grabbing headline.'),

                            Textarea::make("content.{$locale}")
                                ->label("Body Text ({$lbl})")
                                ->rows(4)
                                ->maxLength(600)
                                ->helperText('Promotional body copy. Keep it concise.'),

                            TextInput::make("cta_label.{$locale}")
                                ->label("CTA Button Label ({$lbl})")
                                ->maxLength(60)
                                ->helperText('e.g. "Discover Now", "Shop Sale", "Learn More"'),
                        ]);
                    })->values()->all()
                ),

            // ── CTA URL (language-neutral) ───────────────────────────────────
            TextInput::make('cta_url')
                ->label('CTA Button URL')
                ->url()
                ->maxLength(2048)
                ->helperText('Where the CTA button links to. Can include {locale} placeholder, e.g. /{locale}/products.')
                ->columnSpanFull(),
        ]);
    }
}