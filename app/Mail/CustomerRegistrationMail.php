<?php

namespace App\Mail;

use App\Models\Customer;
use App\Models\CustomerContact;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CustomerRegistrationMail extends Mailable
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
            ->subject(__('portal.mail.registration.subject'))
            ->view('emails.customer-registration');
    }
}