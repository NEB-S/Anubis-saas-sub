<?php

namespace App\Console\Commands;

use App\Mail\LicenseExpiringReminder;
use App\Models\License;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendExpiryReminders extends Command
{
    protected $signature = 'licenses:send-expiry-reminders {--days=3 : Send a reminder when this many days or fewer remain}';
    protected $description = 'Emails customers whose license is expiring soon and hasn\'t been reminded yet';

    public function handle()
    {
        $days = (int) $this->option('days');
        $cutoff = now()->addDays($days);
        $sent = 0;

        // Active/past_due licenses whose current_period_ends_at falls within
        // the window, and trial licenses whose trial_ends_at does — handled
        // as two separate queries since they're two different date columns,
        // then merged.
        $expiringPaid = License::whereIn('status', ['active', 'past_due'])
            ->whereNotNull('current_period_ends_at')
            ->where('current_period_ends_at', '<=', $cutoff)
            ->where('current_period_ends_at', '>', now()) // don't remind on already-expired ones — checkValidity() has already locked those out
            ->whereNull('reminder_sent_at')
            ->with(['customer', 'plan'])
            ->get();

        $expiringTrials = License::where('status', 'trial')
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '<=', $cutoff)
            ->where('trial_ends_at', '>', now())
            ->whereNull('reminder_sent_at')
            ->with(['customer', 'plan'])
            ->get();

        foreach ($expiringPaid->merge($expiringTrials) as $license) {
            if (!$license->customer || !$license->customer->email) {
                continue;
            }

            Mail::to($license->customer->email)->send(new LicenseExpiringReminder($license));
            $license->update(['reminder_sent_at' => now()]);
            $sent++;
        }

        $this->info("Sent {$sent} expiry reminder(s).");

        return self::SUCCESS;
    }
}
