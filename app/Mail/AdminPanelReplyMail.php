<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class AdminPanelReplyMail extends Mailable
{
    public function __construct(
        public string $department, // inquiry|collaboration
        public string $toEmail,
        public string $subjectLine,
        public string $body
    ) {}

    public function build()
    {
        $cfg = (array) config("departments.{$this->department}", []);
        $from = (string) ($cfg['from'] ?? config('mail.from.address'));
        $fromName = (string) ($cfg['from_name'] ?? config('mail.from.name'));
        $replyTo = (string) ($cfg['reply_to'] ?? config('mail.from.address'));

        return $this
            ->to($this->toEmail)
            ->subject($this->subjectLine)
            ->from($from, $fromName)
            ->replyTo($replyTo)
            ->view('mail.admin-panel-reply', [
                'body' => $this->body,
            ]);
    }
}