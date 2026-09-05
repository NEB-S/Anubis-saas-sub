<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = ['slug', 'name', 'price_monthly_cents', 'default_period_days', 'currency', 'features', 'is_active', 'show_on_pricing'];

    protected $casts = [
        'features' => 'array',
        'is_active' => 'boolean',
        'show_on_pricing' => 'boolean',
    ];

    public function licenses()
    {
        return $this->hasMany(License::class);
    }
}
