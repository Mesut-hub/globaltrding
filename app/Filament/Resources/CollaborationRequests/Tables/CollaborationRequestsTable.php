<?php

namespace App\Filament\Resources\CollaborationRequests\Tables;

use App\Mail\CollaborationDecisionMail;
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
use Illuminate\Support\Facades\Mail;

class CollaborationRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')   // (Improvement) newest first
            ->columns([
                TextColumn::make('full_name')->searchable(),
                TextColumn::make('email')->label('Email address')->searchable(),
                TextColumn::make('company')->searchable(),
                TextColumn::make('phone')->searchable(),
                TextColumn::make('country')->searchable(),

                // (Improvement) status badge instead of plain text
                BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger'  => 'rejected',
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
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),

                Action::make('approve')
                    ->label('Approve')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'approved',
                            'reviewed_at' => now(),
                            'reviewed_by' => Auth::id(),
                        ]);

                        Mail::to($record->email)->send(new CollaborationDecisionMail($record));

                        Notification::make()
                            ->title('Approved and email sent (log mailer).')
                            ->success()
                            ->send();
                    }),

                Action::make('reject')
                    ->label('Reject')
                    ->color('danger')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'rejected',
                            'reviewed_at' => now(),
                            'reviewed_by' => Auth::id(),
                        ]);

                        Mail::to($record->email)->send(new CollaborationDecisionMail($record));

                        Notification::make()
                            ->title('Rejected and email sent (log mailer).')
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