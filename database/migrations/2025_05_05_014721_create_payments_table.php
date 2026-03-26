<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('title');
            $table->string('display');
            $table->string('vendor')->nullable();
            $table->string('provider')->nullable();
            $table->string('handler')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('device')->nullable();
            $table->string('currency', 3)->default('USD');
            $table->string('merchant_id')->nullable();
            $table->string('merchant_key')->nullable();
            $table->text('merchant_secret')->nullable();
            $table->json('supported_currencies')->nullable();
            $table->json('setting')->nullable();
            $table->boolean('is_test_mode')->default(false);
            $table->string('webhook_url')->nullable();
            $table->decimal('fee_percentage', 5, 2)->default(0);
            $table->unsignedInteger('fee_fixed')->default(0);
            $table->unsignedInteger('min_amount')->default(0);
            $table->unsignedInteger('max_amount')->default(0);
            $table->text('instruction')->nullable();
            $table->string('remark')->nullable();
            $table->unsignedTinyInteger('sort')->nullable();
            $table->unsignedTinyInteger('status')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
