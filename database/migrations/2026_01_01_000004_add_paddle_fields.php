<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            // The Price ID you create in the Paddle dashboard for this plan's
            // monthly subscription. Needed so the webhook (below) can match an
            // incoming Paddle event back to the right plan.
            $table->string('paddle_price_id')->nullable()->after('price_monthly_cents');
        });

        Schema::table('licenses', function (Blueprint $table) {
            $table->string('paddle_subscription_id')->nullable()->index()->after('bound_domain');
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn('paddle_price_id');
        });
        Schema::table('licenses', function (Blueprint $table) {
            $table->dropColumn('paddle_subscription_id');
        });
    }
};
