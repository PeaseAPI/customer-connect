<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmployeeInvitationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $employeeName,
        public string $inviterName,
        public string $companyName,
        public string $token
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "invites you to join {$this->companyName}",
        );
    }

    public function content(): Content
    {
        $acceptUrl = url("/api/auth/accept-invite?token={$this->token}");

        return new Content(
            markdown: 'emails.employee-invitation',
            with: [
                'employeeName' => $this->employeeName,
                'inviterName' => $this->inviterName,
                'companyName' => $this->companyName,
                'acceptUrl' => $acceptUrl,
            ],
        );
    }
}
