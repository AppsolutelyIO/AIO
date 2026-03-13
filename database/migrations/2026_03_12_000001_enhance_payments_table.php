<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('provider')->after('vendor')->nullable();
            $table->string('payment_method')->after('provider')->nullable();
            $table->string('currency', 3)->after('payment_method')->default('USD');
            $table->json('supported_currencies')->after('currency')->nullable();
            $table->boolean('is_test_mode')->after('setting')->default(false);
            $table->string('webhook_url')->after('is_test_mode')->nullable();
            $table->decimal('fee_percentage', 5, 2)->after('webhook_url')->default(0);
            $table->unsignedInteger('fee_fixed')->after('fee_percentage')->default(0);
            $table->unsignedInteger('min_amount')->after('fee_fixed')->default(0);
            $table->unsignedInteger('max_amount')->after('min_amount')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'provider',
                'payment_method',
                'currency',
                'supported_currencies',
                'is_test_mode',
                'webhook_url',
                'fee_percentage',
                'fee_fixed',
                'min_amount',
                'max_amount',
            ]);
        });
    }
};
