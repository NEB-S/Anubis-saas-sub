<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('licenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained();

            $table->string('license_key')->unique(); // e.g. ANRX-XXXX-XXXX-XXXX-XXXX

            // Domain lock, per your decision — the client (Phase 2) sends its own domain on every
            // check and this must match exactly. Null until the customer's first successful check-in,
            // so a fresh key isn't accidentally locked to the wrong domain before first use.
            $table->string('bound_domain')->nullable();

            // active     — subscription is paid and current
            // past_due   — payment failed, Paddle is retrying (Phase 4 sets this automatically)
            // canceled   — subscription ended, key is dead
            // trial      — free trial period
            $table->enum('status', ['active', 'past_due', 'canceled', 'trial'])->default('trial');

            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('current_period_ends_at')->nullable(); // when the current paid period expires
            $table->timestamp('last_checked_at')->nullable(); // last time the customer's site pinged in
            $table->string('last_checked_ip')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('licenses');
    }
};
