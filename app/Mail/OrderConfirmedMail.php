<?php

namespace App\Mail;

use App\Models\CustomerContact;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderConfirmedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public CustomerContact $contact,
    ) {
        $this->locale($this->order->customer->preferred_locale);
    }

    public function build()
    {
        return $this
            ->subject(__('portal.mail.order_confirmed.subject', ['number' => $this->order->order_number]))
            ->view('emails.order-confirmed');
    }
}