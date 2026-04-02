<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Password;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Account')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        TextInput::make('password')
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => filled($state) ? $state : null)
                            ->dehydrated(fn ($state) => filled($state))
                            ->rule(Password::default())
                            ->helperText('Leave blank to keep current password.')
                            ->columnSpanFull(),

                        Toggle::make('is_admin')
                            ->label('Administrator')
                            ->helperText('Admins can access the Admin panel and bypass all limits.')
                            ->default(false)
                            ->columnSpanFull(),
                    ]),
                ]),

            Section::make('Limits (non-admin only)')
                ->visible(fn ($get) => ! (bool) $get('is_admin'))
                ->schema([
                    Grid::make(3)->schema([
                        TextInput::make('limits.max_upload_mb')
                            ->label('Max upload (MB)')
                            ->numeric()
                            ->default(150)
                            ->minValue(1)
                            ->maxValue(2048),

                        TextInput::make('limits.max_products')
                            ->label('Max products')
                            ->numeric()
                            ->default(5000)
                            ->minValue(0),

                        TextInput::make('limits.max_news_posts')
                            ->label('Max news posts')
                            ->numeric()
                            ->default(500)
                            ->minValue(0),

                        TextInput::make('limits.max_pages')
                            ->label('Max pages')
                            ->numeric()
                            ->default(200)
                            ->minValue(0),

                        TextInput::make('limits.max_industries')
                            ->label('Max industries')
                            ->numeric()
                            ->default(200)
                            ->minValue(0),

                        TextInput::make('limits.max_home_sections')
                            ->label('Max home sections')
                            ->numeric()
                            ->default(50)
                            ->minValue(0),

                        Toggle::make('limits.can_publish')
                            ->label('Can access Editor panel')
                            ->helperText('If enabled, this user can log in at /editor.')
                            ->default(true)
                            ->columnSpanFull(),

                        Toggle::make('limits.can_manage_users')
                            ->label('Can manage users (future use)')
                            ->default(false)
                            ->columnSpanFull(),
                    ]),
                ]),

            Section::make('Editor permissions (non-admin only)')
                ->visible(fn ($get) => ! (bool) $get('is_admin'))
                ->schema([
                    Grid::make(3)->schema([
                        Toggle::make('limits.permissions.home_sections')->label('Home Sections')->default(true),
                        Toggle::make('limits.permissions.industries')->label('Industries')->default(true),
                        Toggle::make('limits.permissions.news_posts')->label('News Posts')->default(true),
                        Toggle::make('limits.permissions.pages')->label('Pages')->default(true),
                        Toggle::make('limits.permissions.products')->label('Products')->default(true),

                        Toggle::make('limits.permissions.brands')->label('Brands')->default(false),
                        Toggle::make('limits.permissions.collaboration_requests')->label('Collaboration Requests')->default(false),
                        Toggle::make('limits.permissions.users')->label('Users (not used)')->disabled()->default(false),
                    ]),
                ]),
        ]);
    }
}