<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('licenses', function (Blueprint $table) {
            // Null = no pending request. When a customer requests a domain
            // change, this holds the NEW domain they're asking for, while
            // bound_domain keeps working as normal until an admin approves it.
            $table->string('pending_domain')->nullable()->after('bound_domain');
        });
    }

    public function down(): void
    {
        Schema::table('licenses', function (Blueprint $table) {
            $table->dropColumn('pending_domain');
        });
    }
};
