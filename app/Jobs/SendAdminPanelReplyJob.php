<?php

namespace App\Jobs;

use App\Mail\AdminPanelReplyMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable as FoundationQueueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendAdminPanelReplyJob implements ShouldQueue
{
    use FoundationQueueable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $department, // inquiry|collaboration
        public string $toEmail,
        public string $subjectLine,
        public string $body
    ) {}

    public function handle(): void
    {
        Mail::to($this->toEmail)->send(
            new AdminPanelReplyMail($this->department, $this->toEmail, $this->subjectLine, $this->body)
        );
    }
}