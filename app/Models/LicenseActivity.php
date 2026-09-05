<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LicenseActivity extends Model
{
    protected $fillable = ['license_id', 'type', 'description', 'actor'];

    public function license()
    {
        return $this->belongsTo(License::class);
    }

    /**
     * The single entry point for logging. Every controller action that
     * changes something about a license (admin OR customer side) should
     * call this instead of creating LicenseActivity rows directly — keeps
     * the log format consistent as more features get added later.
     */
    public static function log(License $license, string $type, string $description, ?string $actor = null): void
    {
        static::create([
            'license_id' => $license->id,
            'type' => $type,
            'description' => $description,
            'actor' => $actor,
        ]);
    }
}
