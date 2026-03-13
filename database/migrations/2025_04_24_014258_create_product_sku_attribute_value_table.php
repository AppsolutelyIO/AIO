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
        Schema::create('product_sku_attribute_value', function (Blueprint $table) {
            $table->foreignId('product_sku_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_attribute_value_id')->constrained()->onDelete('cascade');
            $table->primary(['product_sku_id', 'product_attribute_value_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_sku_attribute_value');
    }
};
