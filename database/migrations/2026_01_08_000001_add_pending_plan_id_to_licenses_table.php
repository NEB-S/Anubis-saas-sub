<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('licenses', function (Blueprint $table) {
            // Null = no pending request. When a customer requests an
            // upgrade/downgrade, this holds the NEW plan they're asking for,
            // while plan_id keeps working as normal until an admin approves
            // it — same pattern as pending_domain for domain changes.
            $table->foreignId('pending_plan_id')->nullable()->after('plan_id')->constrained('plans');
        });
    }

    public function down(): void
    {
        Schema::table('licenses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pending_plan_id');
        });
    }
};
