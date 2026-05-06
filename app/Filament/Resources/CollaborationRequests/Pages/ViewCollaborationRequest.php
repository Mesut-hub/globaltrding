<?php

namespace App\Filament\Resources\CollaborationRequests\Pages;

use App\Filament\Resources\CollaborationRequests\CollaborationRequestResource;
use App\Jobs\SendAdminPanelReplyJob;
use Filament\Actions\Action;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewCollaborationRequest extends ViewRecord
{
    protected static string $resource = CollaborationRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('reply')
                ->label(__('Reply'))
                ->icon('heroicon-o-paper-airplane')
                ->color('primary')
                ->modalHeading(__('Reply to collaboration request'))
                ->modalSubmitActionLabel(__('Send'))
                ->form([
                    TextInput::make('to')
                        ->label(__('To'))
                        ->default(fn () => (string) $this->record->email)
                        ->disabled()
                        ->dehydrated(false),

                    TextInput::make('subject')
                        ->label(__('Subject'))
                        ->default(fn () => 'Re: Collaboration request')
                        ->required(),

                    RichEditor::make('body')
                        ->label(__('Body'))
                        ->required(),
                ])
                ->action(function (array $data) {
                    $subject = (string) $data['subject'];
                    $bodyHtml = (string) $data['body'];
                    $to = (string) $this->record->email;

                    // Email content: send as plain text; log full HTML
                    $bodyPlain = trim(strip_tags($bodyHtml));

                    SendAdminPanelReplyJob::dispatch('collaboration', $to, $subject, $bodyPlain);

                    $this->record->update([
                        'replied_at' => now(),
                        'reply_body' => $bodyHtml,
                        'status' => 'approved', // optional: DO NOT change status automatically if you don't want
                        'reviewed_at' => now(),
                        'reviewed_by' => auth()->id(),
                    ]);

                    Notification::make()
                        ->title(__('Reply queued and logged.'))
                        ->success()
                        ->send();
                }),
        ];
    }
}