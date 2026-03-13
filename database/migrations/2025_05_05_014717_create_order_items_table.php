<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('product_sku_id');
            $table->string('reference');
            $table->string('summary');
            $table->unsignedInteger('original_price')->default(0);
            $table->unsignedInteger('price')->default(0);
            $table->unsignedInteger('quantity')->default(0);
            $table->unsignedInteger('discounted_amount')->default(0);
            $table->unsignedInteger('amount')->default(0);
            $table->json('product_snapshot')->nullable();
            $table->string('note')->nullable();
            $table->string('remark')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('order_id');
            $table->index('product_id');
            $table->index('product_sku_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
