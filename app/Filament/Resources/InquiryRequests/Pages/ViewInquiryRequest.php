<?php

namespace App\Filament\Resources\InquiryRequests\Pages;

use App\Filament\Resources\InquiryRequests\InquiryRequestResource;
use App\Jobs\SendAdminPanelReplyJob;
use Filament\Actions\Action;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewInquiryRequest extends ViewRecord
{
    protected static string $resource = InquiryRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('reply')
                ->label(__('Reply'))
                ->icon('heroicon-o-paper-airplane')
                ->color('primary')
                ->modalHeading(__('Reply to inquiry'))
                ->modalSubmitActionLabel(__('Send'))
                ->form([
                    TextInput::make('to')
                        ->label(__('To'))
                        ->default(fn () => (string) $this->record->email)
                        ->disabled()
                        ->dehydrated(false),

                    TextInput::make('subject')
                        ->label(__('Subject'))
                        ->default(fn () => 'Re: ' . (string) ($this->record->subject ?? 'Inquiry'))
                        ->required(),

                    RichEditor::make('body')
                        ->label(__('Body'))
                        ->required(),
                ])
                ->action(function (array $data) {
                    $subject = (string) $data['subject'];
                    $body = (string) $data['body'];
                    $to = (string) $this->record->email;

                    SendAdminPanelReplyJob::dispatch('inquiry', $to, $subject, strip_tags($body));

                    $this->record->update([
                        'replied_at' => now(),
                        'reply_body' => $body,
                        'status' => 'answered',
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