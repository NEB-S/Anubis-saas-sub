<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AdminActivity extends Model
{
    protected $fillable = ['admin_id', 'admin_name_snapshot', 'type', 'description'];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * Logs against whichever admin is currently signed in. Called from
     * anywhere in the admin panel that performs a real action (login,
     * managing other admins, coupons, deleting a customer, etc.) — the
     * per-license actions (cancel/extend/etc.) already log to
     * LicenseActivity with actor='admin', so this focuses on everything
     * ELSE an admin does.
     */
    public static function log(string $type, string $description): void
    {
        $admin = Auth::guard('admin')->user();

        static::create([
            'admin_id' => $admin?->id,
            'admin_name_snapshot' => $admin?->name ?? 'Unknown',
            'type' => $type,
            'description' => $description,
        ]);
    }
}
