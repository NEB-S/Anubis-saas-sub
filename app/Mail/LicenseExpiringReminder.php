<?php

namespace App\Mail;

use App\Models\License;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LicenseExpiringReminder extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public License $license)
    {
    }

    public function build()
    {
        $expiresAt = $this->license->status === 'trial'
            ? $this->license->trial_ends_at
            : $this->license->current_period_ends_at;

        return $this->subject('Your Anubis license expires soon')
            ->view('emails.license-expiring')
            ->with([
                'license' => $this->license,
                'expiresAt' => $expiresAt,
            ]);
    }
}
