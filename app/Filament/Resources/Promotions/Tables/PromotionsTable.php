<?php
// app/Filament/Resources/Promotions/Tables/PromotionsTable.php

namespace App\Filament\Resources\Promotions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PromotionsTable
{
    public static function configure(Table $table): Table
    {
        $locale   = app()->getLocale();
        $fallback = config('locales.default', 'en');

        return $table
            ->defaultSort('priority', 'desc')
            ->columns([
                TextColumn::make('title')
                    ->label('Headline')
                    ->formatStateUsing(function ($state) use ($locale, $fallback) {
                        if (!is_array($state)) return (string) ($state ?? '—');
                        return $state[$locale] ?? $state[$fallback] ?? reset($state) ?? '—';
                    })
                    ->searchable(query: fn ($query, $search) =>
                        $query->whereRaw("JSON_SEARCH(LOWER(title), 'one', LOWER(?)) IS NOT NULL", ["%{$search}%"])
                    )
                    ->limit(50),

                BadgeColumn::make('display_mode')
                    ->label('Trigger')
                    ->colors([
                        'primary' => 'manual',
                        'warning' => 'auto',
                    ])
                    ->formatStateUsing(fn ($state) => ucfirst($state)),

                BadgeColumn::make('display_frequency')
                    ->label('Frequency')
                    ->formatStateUsing(fn ($state) => match($state) {
                        'always'           => 'Always',
                        'once_per_session' => 'Per Session',
                        'once_per_day'     => 'Per Day',
                        'once_per_week'    => 'Per Week',
                        'once_ever'        => 'Once Ever',
                        default            => ucfirst($state),
                    })
                    ->colors([
                        'danger'  => 'always',
                        'success' => 'once_ever',
                        'warning' => 'once_per_day',
                        'info'    => 'once_per_session',
                    ]),

                TextColumn::make('priority')
                    ->sortable()
                    ->label('Priority'),

                IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),

                TextColumn::make('starts_at')
                    ->label('Starts')
                    ->dateTime()
                    ->placeholder('Immediately')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('ends_at')
                    ->label('Expires')
                    ->dateTime()
                    ->placeholder('Never')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Inactive',
                    ]),

                SelectFilter::make('display_mode')
                    ->label('Trigger Mode')
                    ->options([
                        'manual' => 'Manual',
                        'auto'   => 'Auto',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}