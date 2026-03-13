<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('assessables', function (Blueprint $table) {
            // Rename setting to config to match model
            $table->renameColumn('setting', 'config');
        });

        Schema::table('assessables', function (Blueprint $table) {
            // Add optimization fields
            $table->string('optimized_path')->nullable()->after('file_path');
            $table->string('optimized_format', 20)->nullable()->after('optimized_path');
            $table->unsignedBigInteger('optimized_size')->nullable()->after('optimized_format');
            $table->unsignedInteger('optimized_width')->nullable()->after('optimized_size');
            $table->unsignedInteger('optimized_height')->nullable()->after('optimized_width');
            $table->unsignedSmallInteger('sort_order')->default(0)->after('status');

            // Add composite index for efficient lookups
            $table->index(['assessable_type', 'assessable_id', 'type'], 'assessables_polymorphic_type_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assessables', function (Blueprint $table) {
            $table->dropIndex('assessables_polymorphic_type_index');
            $table->dropColumn([
                'optimized_path',
                'optimized_format',
                'optimized_size',
                'optimized_width',
                'optimized_height',
                'sort_order',
            ]);
        });

        Schema::table('assessables', function (Blueprint $table) {
            $table->renameColumn('config', 'setting');
        });
    }
};
