<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Customer extends Authenticatable
{
    use Notifiable;

    protected $fillable = ['name', 'email', 'password', 'discord_username', 'paddle_customer_id', 'admin_notes'];

    // admin_notes is deliberately NOT in $hidden — it should never be
    // serialized to the customer anyway since no /portal/* route returns
    // it, but keeping it out of $hidden (rather than relying on that alone)
    // means a future accidental API/JSON response of this model would still
    // need someone to explicitly add it before it could leak. The real
    // safeguard is: only Admin-side views/controllers ever touch this field.
    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'password' => 'hashed',
    ];

    public function licenses()
    {
        return $this->hasMany(License::class);
    }

    public function activities()
    {
        return $this->hasMany(CustomerActivity::class)->latest();
    }

    public function supportTickets()
    {
        return $this->hasMany(SupportTicket::class);
    }
}
