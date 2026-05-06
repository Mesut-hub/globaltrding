<?php

namespace App\Jobs;

use App\Mail\AdminRequestNotificationMail;
use App\Mail\RequesterAutoReplyMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable as FoundationQueueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendRequestMailsJob implements ShouldQueue
{
    use FoundationQueueable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $department,      // inquiry|collaboration
        public array $adminPayload,      // field/value pairs
        public string $requesterEmail,
        public array $autoReplyVars      // reference_id, subject, message, name, email, products_url
    ) {}

    public function handle(): void
    {
        $adminInbox = (string) config('departments.admin.inbox', 'info@globaltrding.com');

        Mail::to($adminInbox)->send(
            new AdminRequestNotificationMail($this->department, $this->adminPayload)
        );

        Mail::to($this->requesterEmail)->send(
            new RequesterAutoReplyMail($this->department, $this->autoReplyVars)
        );
    }
}