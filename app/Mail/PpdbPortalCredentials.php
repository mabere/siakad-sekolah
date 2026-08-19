<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PpdbPortalCredentials extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $accountName,
        public string $role,
        public string $username,
        public string $password,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Akun portal SIAKAD sekolah');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.ppdb.portal-credentials');
    }
}
