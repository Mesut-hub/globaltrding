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
    ) {}

    public function build()
    {
        return $this
            ->subject("Order {$this->order->order_number} is accepted and registered")
            ->view('emails.order-confirmed');
    }
}