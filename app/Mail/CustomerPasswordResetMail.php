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
            ->subject('Your Global Trading portal password has been reset')
            ->view('emails.customer-password-reset');
    }
}