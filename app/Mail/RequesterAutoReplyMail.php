<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class RequesterAutoReplyMail extends Mailable
{
    public function __construct(
        public string $department, // inquiry|collaboration
        public array $vars // all template vars
    ) {}

    public function build()
    {
        $cfg = (array) config("departments.{$this->department}", []);
        $from = (string) ($cfg['from'] ?? config('mail.from.address'));
        $fromName = (string) ($cfg['from_name'] ?? config('mail.from.name'));
        $replyTo = (string) ($cfg['reply_to'] ?? config('mail.from.address'));

        return $this
            ->subject('We have received your request — Global Trading')
            ->from($from, $fromName)
            ->replyTo($replyTo)
            ->view('mail.requester-auto-reply', $this->vars);
    }
}