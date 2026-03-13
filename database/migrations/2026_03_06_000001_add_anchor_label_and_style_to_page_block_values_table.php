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
        Schema::table('page_block_values', function (Blueprint $table) {
            $table->string('view_style')->default('default')->after('view');
            $table->string('anchor_label')->nullable()->after('view_style');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('page_block_values', function (Blueprint $table) {
            $table->dropColumn(['anchor_label', 'view_style']);
        });
    }
};
