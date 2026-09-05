<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ActivationCode extends Model
{
    protected $fillable = ['license_id', 'code', 'expires_at', 'used_at'];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    public function license()
    {
        return $this->belongsTo(License::class);
    }

    /** Generates a brand new code for a license and stores it — 5-minute
     *  lifetime is plenty for a browser redirect round-trip, and short
     *  enough that a leaked/logged code is useless shortly after. */
    public static function issueFor(License $license): self
    {
        return static::create([
            'license_id' => $license->id,
            'code' => Str::random(64),
            'expires_at' => now()->addMinutes(5),
        ]);
    }

    public function isValid(): bool
    {
        return $this->used_at === null && $this->expires_at->isFuture();
    }
}
