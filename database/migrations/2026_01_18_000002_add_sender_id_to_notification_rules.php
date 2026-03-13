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
        Schema::table('notification_rules', function (Blueprint $table) {
            $table->foreignId('sender_id')->nullable()
                ->after('template_id')
                ->constrained('notification_senders')
                ->onDelete('set null');

            $table->index('sender_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notification_rules', function (Blueprint $table) {
            $table->dropForeign(['sender_id']);
            $table->dropIndex(['sender_id']);
            $table->dropColumn('sender_id');
        });
    }
};
