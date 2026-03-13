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
        Schema::create('notification_queue', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rule_id')->nullable();
            $table->unsignedBigInteger('template_id');
            $table->string('recipient_email');
            $table->string('subject');
            $table->longText('body_html');
            $table->longText('body_text')->nullable();
            $table->json('trigger_data')->nullable()->comment('Original data that triggered the notification');
            $table->enum('status', ['pending', 'processing', 'sent', 'failed', 'cancelled'])->default('pending');
            $table->timestamp('scheduled_at');
            $table->timestamp('sent_at')->nullable();
            $table->text('error_message')->nullable();
            $table->tinyInteger('attempts')->default(0);
            $table->timestamps();

            $table->foreign('rule_id')->references('id')->on('notification_rules')->onDelete('set null');
            $table->foreign('template_id')->references('id')->on('notification_templates')->onDelete('cascade');
            $table->index(['status', 'scheduled_at']);
            $table->index(['recipient_email', 'status']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_queue');
    }
};
