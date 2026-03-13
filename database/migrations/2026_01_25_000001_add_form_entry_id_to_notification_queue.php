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
        Schema::table('notification_queue', function (Blueprint $table) {
            $table->foreignId('form_entry_id')->nullable()
                ->after('sender_id')
                ->constrained('form_entries')
                ->onDelete('set null')
                ->comment('Form entry that triggered this notification');

            $table->index('form_entry_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notification_queue', function (Blueprint $table) {
            $table->dropForeign(['form_entry_id']);
            $table->dropIndex(['form_entry_id']);
            $table->dropColumn('form_entry_id');
        });
    }
};
