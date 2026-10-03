<?php

namespace App\Mail;

use App\Models\Customer;
use App\Models\CustomerContact;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CustomerDetailsUpdatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Customer $customer,
        public CustomerContact $contact,
    ) {
        $this->locale($this->customer->preferred_locale);
    }

    public function build()
    {
        return $this
            ->subject(__('portal.mail.details_updated.subject'))
            ->view('emails.customer-details-updated');
    }
}