<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class AdminRequestNotificationMail extends Mailable
{
    public function __construct(
        public string $type, // inquiry|collaboration
        public array $payload
    ) {}

    public function build()
    {
        $adminName = (string) config('departments.admin.name', 'Global Trading');

        return $this
            ->subject("New {$this->type} request — Global Trading")
            ->from((string) config('mail.from.address'), $adminName)
            ->view('mail.admin-request-notification', [
                'type' => $this->type,
                'payload' => $this->payload,
            ]);
    }
}