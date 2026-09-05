<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class License extends Model
{
    protected $fillable = [
        'customer_id', 'plan_id', 'pending_plan_id', 'license_key', 'bound_domain', 'pending_domain',
        'status', 'trial_ends_at', 'current_period_ends_at',
        'last_checked_at', 'last_checked_ip', 'reminder_sent_at',
        'file_fingerprint', 'tamper_detected_at',
        'php_version', 'laravel_version', 'os', 'installation_id', 'visit_count',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'current_period_ends_at' => 'datetime',
        'last_checked_at' => 'datetime',
        'reminder_sent_at' => 'datetime',
        'file_fingerprint' => 'array',
        'tamper_detected_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function pendingPlan()
    {
        return $this->belongsTo(Plan::class, 'pending_plan_id');
    }

    public function activities()
    {
        return $this->hasMany(LicenseActivity::class)->latest();
    }

    /**
     * Generates a license key in the format ANRX-XXXX-XXXX-XXXX-XXXX.
     * Called when you issue a new license from the admin dashboard (Phase 3).
     */
    public static function generateKey(): string
    {
        $segments = collect(range(1, 4))
            ->map(fn () => strtoupper(Str::random(4)));

        return 'ANRX-' . $segments->implode('-');
    }

    /**
     * The single source of truth for "is this license usable right now".
     * Both the API check (Phase 1/2) and the admin dashboard (Phase 3) call this
     * instead of re-implementing the rules in two places.
     *
     * @return array{valid: bool, reason: string}
     */
    public function checkValidity(?string $requestingDomain): array
    {
        if ($this->status === 'canceled') {
            return ['valid' => false, 'reason' => 'canceled'];
        }

        if ($this->status === 'trial') {
            if ($this->trial_ends_at && $this->trial_ends_at->isPast()) {
                return ['valid' => false, 'reason' => 'trial_expired'];
            }
        }

        if (in_array($this->status, ['active', 'past_due'])) {
            if ($this->current_period_ends_at && $this->current_period_ends_at->isPast()) {
                return ['valid' => false, 'reason' => 'subscription_expired'];
            }
        }

        // Domain lock: first successful check ever binds the key to that domain.
        // After that, any mismatch is rejected — this is what stops one key being
        // reused on a second, unpaid-for server. Lowercased on both sides so a
        // harmless capitalization difference doesn't wrongly reject a real customer.
        $normalizedIncoming = $requestingDomain !== null ? strtolower($requestingDomain) : null;
        $normalizedBound = $this->bound_domain !== null ? strtolower($this->bound_domain) : null;

        if ($normalizedBound === null) {
            // Not bound yet — this check will bind it (handled in the controller).
        } elseif ($normalizedIncoming !== null && $normalizedBound !== $normalizedIncoming) {
            return ['valid' => false, 'reason' => 'domain_mismatch'];
        }

        return ['valid' => true, 'reason' => 'ok'];
    }

    /**
     * Compares an incoming set of file hashes against this license's trusted
     * baseline. First call ever (file_fingerprint is null) SETS the baseline
     * rather than comparing — everything after that is a real comparison.
     *
     * @return bool true if tampering was newly detected THIS check (so the
     *              controller knows whether to log an activity entry for it).
     */
    public function checkFileFingerprint(?array $incomingHashes): bool
    {
        if (empty($incomingHashes)) {
            // Older client version that doesn't send hashes yet, or the
            // request simply omitted them — nothing to compare against.
            return false;
        }

        if ($this->file_fingerprint === null) {
            $this->update(['file_fingerprint' => $incomingHashes]);
            return false;
        }

        $baseline = $this->file_fingerprint;
        $incoming = $incomingHashes;
        ksort($baseline);
        ksort($incoming);

        if ($baseline != $incoming && $this->tamper_detected_at === null) {
            $this->update(['tamper_detected_at' => now()]);
            return true;
        }

        return false;
    }

    /** Called by the admin after a legitimate product update (which
     *  naturally changes the licensing files' hashes) — clears the tamper
     *  flag and lets the NEXT check-in establish a fresh trusted baseline. */
    public function resetFingerprintBaseline(): void
    {
        $this->update(['file_fingerprint' => null, 'tamper_detected_at' => null]);
    }
}
