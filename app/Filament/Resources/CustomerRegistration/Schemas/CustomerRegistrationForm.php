<?php

namespace App\Filament\Resources\CustomerRegistration\Schemas;

use App\Models\CustomerContact;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerRegistrationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Company details')->schema([
                Grid::make(3)->schema([
                    TextInput::make('company_name')->label('Company name')->required(),
                    TextInput::make('full_commercial_name')->label('Full commercial name'),
                    TextInput::make('registration_number')->label('Registration number'),
                    TextInput::make('phone')->label('Phone')->tel(),
                    TextInput::make('website')->label('Website')->url(),
                    TextInput::make('email')->label('Company email')->email()->required(),
                ]),
            ]),

            Section::make('Contacts')->schema([
                Repeater::make('contacts')
                    ->relationship('contacts')
                    ->schema([
                        Grid::make(4)->schema([
                            Select::make('role')
                                ->options(CustomerContact::roleOptions())
                                ->default('other')
                                ->required(),
                            TextInput::make('name')->label('Name')->required(),
                            TextInput::make('phone')->label('Phone')->tel(),
                            TextInput::make('email')->label('Email')->email(),
                        ]),
                        Toggle::make('receives_notifications')
                            ->label('Receives registration / order / status emails')
                            ->default(true),
                    ])
                    ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                    ->reorderable('sort_order')
                    ->collapsible()
                    ->defaultItems(2)
                    ->addActionLabel('Add another person')
                    ->columns(1),
            ]),

            Textarea::make('notes')
                ->label('Internal notes (never shown to the customer)')
                ->rows(2)
                ->columnSpanFull(),
        ]);
    }
}