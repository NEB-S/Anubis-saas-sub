<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('licenses', function (Blueprint $table) {
            // Each column is added only if missing — this migration exists
            // because the ORIGINAL one got recorded as "already run" in the
            // migrations table without actually creating these columns
            // (likely run against a different/older database.sqlite at some
            // point). Guarding like this means it's safe to run regardless
            // of which columns, if any, already exist.
            if (!Schema::hasColumn('licenses', 'php_version')) {
                $table->string('php_version')->nullable();
            }
            if (!Schema::hasColumn('licenses', 'laravel_version')) {
                $table->string('laravel_version')->nullable();
            }
            if (!Schema::hasColumn('licenses', 'os')) {
                $table->string('os')->nullable();
            }
            if (!Schema::hasColumn('licenses', 'installation_id')) {
                $table->string('installation_id', 64)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('licenses', function (Blueprint $table) {
            foreach (['php_version', 'laravel_version', 'os', 'installation_id'] as $column) {
                if (Schema::hasColumn('licenses', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
