<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('licenses', function (Blueprint $table) {
            // Null until the first reminder goes out. Once set, the command
            // below skips this license until its period changes again
            // (renewed/extended), so the customer doesn't get the same
            // "expiring soon" email every single day.
            $table->timestamp('reminder_sent_at')->nullable()->after('last_checked_at');
        });
    }

    public function down(): void
    {
        Schema::table('licenses', function (Blueprint $table) {
            $table->dropColumn('reminder_sent_at');
        });
    }
};
