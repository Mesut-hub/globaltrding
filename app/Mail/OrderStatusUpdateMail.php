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
    ) {}

    public function build()
    {
        $subject = $this->update->stage_key === \App\Enums\OrderStatusStage::DELIVERED->value
            ? "Delivered — order {$this->order->order_number}"
            : "Shipment update — order {$this->order->order_number}";

        return $this->subject($subject)->view('emails.order-status-update');
    }
}