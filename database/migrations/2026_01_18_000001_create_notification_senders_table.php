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
        Schema::create('notification_senders', function (Blueprint $table) {
            $table->id();

            // Sender identification
            $table->string('name');
            $table->string('slug')->unique()->comment('Unique identifier per tenant database');

            // Mailer configuration
            $table->enum('type', ['smtp', 'sendmail', 'mailgun', 'ses', 'postmark', 'resend', 'log'])
                ->default('smtp')
                ->comment('Mailer transport type');

            // SMTP Configuration (encrypted)
            $table->string('smtp_host')->nullable()->comment('SMTP server hostname');
            $table->integer('smtp_port')->nullable()->default(587)->comment('SMTP server port');
            $table->string('smtp_username')->nullable()->comment('SMTP username');
            $table->text('smtp_password')->nullable()->comment('Encrypted SMTP password');
            $table->enum('smtp_encryption', ['tls', 'ssl'])->nullable()->comment('SMTP encryption type');

            // Third-party service config (encrypted JSON)
            $table->text('service_config')->nullable()->comment('Encrypted JSON: API keys, tokens for third-party services');

            // From address
            $table->string('from_address')->comment('Default from email address');
            $table->string('from_name')->nullable()->comment('Default from name');

            // Categorization
            $table->enum('category', ['internal', 'external', 'system'])->default('external')
                ->comment('internal=staff/admin, external=customers/public, system=system alerts');

            // Usage settings
            $table->boolean('is_default')->default(false)->comment('Default sender for category');
            $table->integer('priority')->default(0)->comment('Priority when multiple senders exist (higher = preferred)');
            $table->boolean('is_active')->default(true)->comment('Whether sender is active');

            // Rate limiting (per tenant database)
            $table->integer('daily_limit')->nullable()->comment('Max emails per day (null = unlimited)');
            $table->integer('hourly_limit')->nullable()->comment('Max emails per hour (null = unlimited)');

            // Metadata
            $table->text('description')->nullable()->comment('Description of this sender');
            $table->json('metadata')->nullable()->comment('Additional configuration or notes');

            $table->timestamps();
            $table->softDeletes();

            // Performance indexes
            $table->index(['category', 'is_active']);
            $table->index(['is_default', 'category']);
            $table->index('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_senders');
    }
};
