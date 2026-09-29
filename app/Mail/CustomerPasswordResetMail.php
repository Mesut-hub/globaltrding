<?php

namespace App\Mail;

use App\Models\Customer;
use App\Models\CustomerContact;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CustomerPasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Customer $customer,
        public CustomerContact $contact,
        public string $plainPassword,
    ) {}

    public function build()
    {
        return $this
            ->locale($this->customer->preferred_locale)
            ->subject(__('portal.mail.password_reset.subject'))
            ->view('emails.customer-password-reset');
    }
}