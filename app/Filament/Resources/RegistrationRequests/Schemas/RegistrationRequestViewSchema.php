<?php

namespace App\Filament\Resources\RegistrationRequests\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RegistrationRequestViewSchema
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(2)->schema([
                Section::make('Step 1')
                    ->schema([
                        TextInput::make('first_name')->disabled(),
                        TextInput::make('last_name')->disabled(),
                        TextInput::make('email')->disabled(),
                        TextInput::make('occupation')->disabled(),
                        TextInput::make('mobile_phone')->disabled(),
                        TextInput::make('primary_product_interest')->disabled(),
                        TextInput::make('preferred_language')->disabled(),
                        TextInput::make('accepted_terms')
                            ->disabled()
                            ->formatStateUsing(fn ($state) => $state ? 'Yes' : 'No'),
                    ]),

                Section::make('Step 2')
                    ->schema([
                        TextInput::make('company')->disabled(),
                        TextInput::make('existing_customer')
                            ->disabled()
                            ->formatStateUsing(fn ($state) => $state === null ? '—' : ($state ? 'Yes' : 'No')),
                        TextInput::make('location')->disabled(),
                        TextInput::make('city')->disabled(),
                        TextInput::make('street_and_number')->disabled(),
                        TextInput::make('zip_code')->disabled(),
                        TextInput::make('industries_operate')->disabled(),
                        Textarea::make('message')->rows(4)->disabled(),
                    ]),
            ]),

            Section::make('Review')
                ->schema([
                    TextInput::make('status')->disabled(),
                    TextInput::make('reviewed_at')->disabled(),
                    TextInput::make('reviewed_by')->disabled(),
                    TextInput::make('created_at')->disabled(),
                ]),
        ]);
    }
}