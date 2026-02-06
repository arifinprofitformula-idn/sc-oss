<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EpiApeSyncReport extends Mailable
{
    use Queueable, SerializesModels;

    public $updates;
    public $errors;

    /**
     * Create a new message instance.
     */
    public function __construct(array $updates, array $errors)
    {
        $this->updates = $updates;
        $this->errors = $errors;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $status = empty($this->errors) ? 'Success' : 'Attention Needed';
        return new Envelope(
            subject: "[EPI APE] Sync Report - {$status}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.epi-ape-sync-report',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
