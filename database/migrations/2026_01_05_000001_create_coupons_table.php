<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();

            // Usable right now, with no payment system live: bonus days
            // added on top of the normal 7-day trial at self-service signup.
            $table->unsignedInteger('extra_trial_days')->default(0);

            // Stored for later: once Paddle checkout is wired in (Phase H),
            // this percentage gets applied to the price at checkout. Doing
            // nothing yet, but the field exists so coupons don't need to be
            // redesigned when that day comes.
            $table->unsignedTinyInteger('discount_percent')->nullable();

            $table->unsignedInteger('max_uses')->nullable(); // null = unlimited
            $table->unsignedInteger('used_count')->default(0);
            $table->timestamp('expires_at')->nullable(); // null = never expires
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
