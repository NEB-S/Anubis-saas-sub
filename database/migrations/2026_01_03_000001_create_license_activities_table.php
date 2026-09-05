<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('license_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('license_id')->constrained()->cascadeOnDelete();

            // e.g. 'issued', 'canceled', 'reactivated', 'extended', 'deleted',
            // 'domain_change_requested', 'domain_change_approved', 'domain_change_rejected',
            // 'renew_attempted', 'name_changed', 'password_changed'
            $table->string('type');

            $table->text('description');

            // 'admin' or 'customer' — who triggered this. Null for system/automated
            // events (e.g. a scheduled job), though none exist yet.
            $table->string('actor')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('license_activities');
    }
};
