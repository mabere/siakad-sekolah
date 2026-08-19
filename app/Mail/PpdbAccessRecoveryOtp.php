<?php

namespace App\Mail;

use App\Models\PpdbApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PpdbAccessRecoveryOtp extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public PpdbApplication $application,
        public string $code,
        public int $expiresInMinutes,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Kode pemulihan PIN PPDB');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.ppdb.access-recovery-otp');
    }
}
