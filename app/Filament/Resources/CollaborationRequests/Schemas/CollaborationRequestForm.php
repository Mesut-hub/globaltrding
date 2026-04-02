<?php

namespace App\Filament\Resources\CollaborationRequests\Schemas;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class CollaborationRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(2)->schema([
                TextInput::make('full_name')->disabled(),
                TextInput::make('email')->disabled(),
                TextInput::make('company')->disabled(),
                TextInput::make('phone')->disabled(),
                TextInput::make('country')->disabled(),

                TextInput::make('status')->disabled(),

                TextInput::make('reviewed_at')->disabled(),
                TextInput::make('reviewed_by')->disabled(),
            ]),

            Textarea::make('message')
                ->disabled()
                ->columnSpanFull()
                ->rows(8),

            Placeholder::make('created_at')
                ->content(fn ($record) => $record?->created_at?->toDateTimeString()),

            Placeholder::make('updated_at')
                ->content(fn ($record) => $record?->updated_at?->toDateTimeString()),
        ]);
    }
}