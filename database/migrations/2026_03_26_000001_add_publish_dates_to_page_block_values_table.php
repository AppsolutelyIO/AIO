<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('page_block_values', function (Blueprint $table) {
            $table->dateTimeTz('published_at')->nullable()->after('template');
            $table->dateTimeTz('expired_at')->nullable()->after('published_at');
        });
    }

    public function down(): void
    {
        Schema::table('page_block_values', function (Blueprint $table) {
            $table->dropColumn(['published_at', 'expired_at']);
        });
    }
};
