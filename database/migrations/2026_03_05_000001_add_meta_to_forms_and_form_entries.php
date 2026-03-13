<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds meta collection config to forms and meta storage to form entries.
     * Each form defines which meta keys to collect (from cookies); each entry stores the collected values.
     */
    public function up(): void
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->json('meta_keys_to_collect')->nullable()->after('api_access_token')
                ->comment('Array of meta key names to collect from cookies when form is submitted');
        });

        Schema::table('form_entries', function (Blueprint $table) {
            $table->json('meta')->nullable()->after('data')
                ->comment('Collected meta key-value pairs (from cookies) per form config');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->dropColumn('meta_keys_to_collect');
        });

        Schema::table('form_entries', function (Blueprint $table) {
            $table->dropColumn('meta');
        });
    }
};
