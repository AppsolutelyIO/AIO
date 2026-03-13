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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('PHYSICAL');
            $table->json('shipment_methods')->nullable();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->string('cover')->nullable();
            $table->string('keywords')->nullable();
            $table->string('description')->nullable();
            $table->text('content')->nullable();
            $table->unsignedBigInteger('original_price')->nullable();
            $table->unsignedBigInteger('price')->nullable();
            $table->json('setting')->nullable();
            $table->json('payment_methods')->nullable();
            $table->json('additional_columns')->nullable();
            $table->unsignedTinyInteger('sort')->nullable();
            $table->unsignedTinyInteger('status')->default(0);

            $table->dateTimeTz('published_at')->useCurrent();
            $table->dateTimeTz('expired_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
