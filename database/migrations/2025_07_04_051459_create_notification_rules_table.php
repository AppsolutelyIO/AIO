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
        Schema::create('notification_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('trigger_type', 100)->comment('form_submission, user_registration, order_placed, etc.');
            $table->string('trigger_reference')->comment('form_slug, product_category, etc.');
            $table->unsignedBigInteger('template_id');
            $table->enum('recipient_type', ['admin', 'user', 'custom', 'conditional'])->default('admin');
            $table->json('recipient_emails')->nullable()->comment('Static email list for custom type');
            $table->json('conditions')->nullable()->comment('Conditional logic for when to trigger');
            $table->integer('delay_minutes')->default(0)->comment('Send immediately or delay');
            $table->tinyInteger('status')->default(1);
            $table->timestamps();

            $table->foreign('template_id')->references('id')->on('notification_templates')->onDelete('cascade');
            $table->index(['trigger_type', 'status']);
            $table->index(['trigger_type', 'trigger_reference']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_rules');
    }
};
