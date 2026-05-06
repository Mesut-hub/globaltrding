<?php

namespace App\Filament\Resources\InquiryRequests\Tables;

use App\Mail\InquiryReplyMail;
use App\Models\InquiryReply;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class InquiryRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('full_name')
                    ->searchable()
                    ->url(fn ($record) => \App\Filament\Resources\InquiryRequests\InquiryRequestResource::getUrl('view', ['record' => $record]))
                    ->openUrlInNewTab(false),
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

                TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'pending' => 'Pending',
                    'answered' => 'Answered',
                    'archived' => 'Archived',
                ]),
            ])
            ->recordActions([
                ViewAction::make(),

                // Copy-to-clipboard actions (require gtClipboard helper added earlier)
                Action::make('copy_email')
                    ->label('Copy email')
                    ->icon('heroicon-o-clipboard-document')
                    ->color('gray')
                    ->action(function ($record) {
                        $this->js("window.gtClipboard?.copy(" . json_encode((string) $record->email) . ")");
                        Notification::make()->title('Email copied to clipboard.')->success()->send();
                    }),

                Action::make('copy_phone')
                    ->label('Copy phone')
                    ->icon('heroicon-o-clipboard-document')
                    ->color('gray')
                    ->action(function ($record) {
                        $this->js("window.gtClipboard?.copy(" . json_encode((string) $record->phone) . ")");
                        Notification::make()->title('Phone copied to clipboard.')->success()->send();
                    }),

                // NEW: Reply action
                Action::make('reply')
                    ->label('Reply')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('primary')
                    ->modalHeading('Reply to inquiry')
                    ->modalSubmitActionLabel('Send reply')
                    ->form([
                        TextInput::make('to_email')
                            ->label('To')
                            ->email()
                            ->required()
                            ->default(fn ($record) => (string) $record->email),

                        TextInput::make('subject')
                            ->label('Subject')
                            ->required()
                            ->default(fn ($record) => 'Re: ' . (string) ($record->subject ?? 'Inquiry')),

                        Textarea::make('body')
                            ->label('Message')
                            ->rows(10)
                            ->required(),

                        Toggle::make('mark_answered')
                            ->label('Mark as answered after sending')
                            ->default(true),
                    ])
                    ->action(function ($record, array $data) {
                        $reply = InquiryReply::create([
                            'inquiry_request_id' => $record->id,
                            'to_email' => (string) $data['to_email'],
                            'subject' => (string) $data['subject'],
                            'body' => (string) $data['body'],
                            'sent_by' => Auth::id(),
                            'sent_at' => now(),
                        ]);

                        Mail::to($reply->to_email)->send(new InquiryReplyMail($reply));

                        if (!empty($data['mark_answered'])) {
                            $record->update([
                                'status' => 'answered',
                                'reviewed_at' => now(),
                                'reviewed_by' => Auth::id(),
                            ]);
                        }

                        Notification::make()
                            ->title('Reply sent and saved.')
                            ->success()
                            ->send();
                    }),

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

                        Notification::make()->title('Marked as answered.')->success()->send();
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

                        Notification::make()->title('Archived.')->success()->send();
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

                        Notification::make()->title('Reopened (set to pending).')->success()->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}