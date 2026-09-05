<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code', 'extra_trial_days', 'discount_percent',
        'max_uses', 'used_count', 'expires_at', 'is_active',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * The single source of truth for "can this code still be redeemed".
     * Used at self-service signup today, and will be reused at real Paddle
     * checkout once that's live — same pattern as License::checkValidity().
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }
        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }
        if ($this->max_uses !== null && $this->used_count >= $this->max_uses) {
            return false;
        }

        return true;
    }

    public static function findValid(string $code): ?self
    {
        $coupon = static::where('code', strtoupper(trim($code)))->first();

        return ($coupon && $coupon->isValid()) ? $coupon : null;
    }
}
