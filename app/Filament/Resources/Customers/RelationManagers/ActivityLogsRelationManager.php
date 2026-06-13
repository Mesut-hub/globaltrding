<?php

namespace App\Filament\Resources\Customers\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ActivityLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'activityLogs';

    protected static ?string $title = 'Activity Log';

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                BadgeColumn::make('action')
                    ->label('Event')
                    ->formatStateUsing(fn ($record) => $record->action_label)
                    ->colors([
                        'success' => fn ($state) => in_array($state, ['login', 'unblocked', 'unsuspended', 'access_granted']),
                        'danger'  => fn ($state) => in_array($state, ['blocked', 'access_revoked', 'login_failed', 'auto_logout_blocked']),
                        'warning' => fn ($state) => in_array($state, ['suspended', 'auto_logout_suspended']),
                        'info'    => fn ($state) => in_array($state, ['logout', 'force_logout', 'password_reset_sent']),
                    ]),

                TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->placeholder('—'),

                TextColumn::make('performer.name')
                    ->label('Performed By')
                    ->placeholder('System / Self'),

                TextColumn::make('context')
                    ->label('Details')
                    ->formatStateUsing(function ($state) {
                        if (! is_array($state)) return '—';
                        $parts = [];
                        if (! empty($state['reason']))  $parts[] = 'Reason: ' . $state['reason'];
                        if (! empty($state['until']))   $parts[] = 'Until: '  . $state['until'];
                        return implode(' | ', $parts) ?: '—';
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Date / Time')
                    ->dateTime()
                    ->sortable(),
            ])
            ->paginated([10, 25, 50]);
    }
}