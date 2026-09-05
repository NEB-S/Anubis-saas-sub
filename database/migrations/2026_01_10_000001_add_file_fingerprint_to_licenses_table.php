<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('licenses', function (Blueprint $table) {
            // The trusted "baseline" hashes of the critical licensing files,
            // captured automatically on this license's FIRST successful
            // check-in. Format: {"CheckLicense.php": "sha256hash", ...}.
            $table->json('file_fingerprint')->nullable()->after('last_checked_ip');

            // Set the moment an incoming check's hashes stop matching the
            // baseline. Null = no tampering detected (or not yet checked).
            $table->timestamp('tamper_detected_at')->nullable()->after('file_fingerprint');
        });
    }

    public function down(): void
    {
        Schema::table('licenses', function (Blueprint $table) {
            $table->dropColumn(['file_fingerprint', 'tamper_detected_at']);
        });
    }
};
