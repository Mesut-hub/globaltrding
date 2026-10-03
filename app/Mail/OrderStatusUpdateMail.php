<?php

namespace App\Mail;

use App\Models\CustomerContact;
use App\Models\Order;
use App\Models\OrderStatusUpdate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdateMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public OrderStatusUpdate $update,
        public CustomerContact $contact,
    ) {
        $this->locale($this->order->customer->preferred_locale);
    }

    public function build()
    {
        $key = $this->update->stage_key === \App\Enums\OrderStatusStage::DELIVERED->value
            ? 'portal.mail.status_update.subject_delivered'
            : 'portal.mail.status_update.subject';

        return $this
            ->subject(__($key, ['number' => $this->order->order_number]))
            ->view('emails.order-status-update');
    }
}