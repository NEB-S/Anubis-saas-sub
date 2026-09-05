<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique(); // e.g. 'basic', 'advanced', 'premium'
            $table->string('name');
            $table->unsignedInteger('price_monthly_cents');
            $table->string('currency', 3)->default('USD');
            // A simple JSON list of feature flags this tier unlocks, e.g. ["black_market","auction","royal_pass"].
            // The client side (Phase 2) reads this to decide what to show/hide — nothing more than that.
            $table->json('features')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
