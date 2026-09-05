<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerActivity extends Model
{
    protected $fillable = ['customer_id', 'type', 'description', 'actor'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public static function log(Customer $customer, string $type, string $description, ?string $actor = null): void
    {
        static::create([
            'customer_id' => $customer->id,
            'type' => $type,
            'description' => $description,
            'actor' => $actor,
        ]);
    }
}
