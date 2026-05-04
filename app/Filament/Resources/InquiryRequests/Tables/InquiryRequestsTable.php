<?php

namespace App\Filament\Resources\InquiryRequests\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class InquiryRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('full_name')->searchable(),
                TextColumn::make('email')->label('Email address')->searchable(),
                TextColumn::make('company')->searchable(),
                TextColumn::make('phone')->searchable(),
                TextColumn::make('subject')->searchable(),

                BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'answered',
                        'gray'    => 'archived',
                    ])
                    ->formatStateUsing(fn (?string $state) => ucfirst((string) $state))
                    ->sortable(),

                TextColumn::make('reviewed_at')->dateTime()->sortable(),
                TextColumn::make('reviewer.email')->label('Reviewed by'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'answered' => 'Answered',
                        'archived' => 'Archived',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),

                Action::make('mark_answered')
                    ->label('Mark answered')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'answered',
                            'reviewed_at' => now(),
                            'reviewed_by' => Auth::id(),
                        ]);

                        Notification::make()
                            ->title('Marked as answered.')
                            ->success()
                            ->send();
                    }),

                Action::make('archive')
                    ->label('Archive')
                    ->color('gray')
                    ->visible(fn ($record) => in_array($record->status, ['pending', 'answered'], true))
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'archived',
                            'reviewed_at' => now(),
                            'reviewed_by' => Auth::id(),
                        ]);

                        Notification::make()
                            ->title('Archived.')
                            ->success()
                            ->send();
                    }),

                Action::make('reopen')
                    ->label('Reopen')
                    ->color('warning')
                    ->visible(fn ($record) => $record->status === 'archived')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'pending',
                            'reviewed_at' => now(),
                            'reviewed_by' => Auth::id(),
                        ]);

                        Notification::make()
                            ->title('Reopened (set to pending).')
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}