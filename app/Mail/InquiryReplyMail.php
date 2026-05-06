<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InquiryReplyMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public InquiryReply $reply)
    {
        //
    }

    public function build()
    {
        $from = (string) config('departments.inquiry.from', config('mail.from.address'));
        $fromName = (string) config('departments.inquiry.from_name', config('mail.from.name'));
        $replyTo = (string) config('departments.inquiry.reply_to', config('mail.from.address'));

        return $this
            ->from($from, $fromName)
            ->replyTo($replyTo)
            ->subject($this->reply->subject)
            ->view('mail.inquiry-reply', [
                'reply' => $this->reply,
            ]);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Inquiry Reply Mail',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'view.name',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
