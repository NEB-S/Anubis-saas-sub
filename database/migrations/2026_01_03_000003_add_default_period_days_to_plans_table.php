<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            // How many days one billing cycle of this plan lasts. Basic renews
            // monthly (30), Advanced every 6 months (180), Premium yearly (365).
            // Used to auto-fill the period length when issuing/reactivating/renewing
            // a license, instead of assuming every plan is a flat 30-day cycle.
            $table->unsignedInteger('default_period_days')->default(30)->after('price_monthly_cents');
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn('default_period_days');
        });
    }
};
