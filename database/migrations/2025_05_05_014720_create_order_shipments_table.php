<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('order_shipments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->string('product_type')->default('');
            $table->string('email')->nullable();
            $table->string('name')->nullable();
            $table->string('mobile')->nullable();
            $table->string('address')->nullable();
            $table->string('address_extra')->nullable();
            $table->string('town')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('postcode')->nullable();
            $table->string('country')->nullable();
            $table->string('delivery_vendor')->nullable();
            $table->string('delivery_reference')->nullable();
            $table->string('remark')->nullable();
            $table->string('status');
            $table->timestamps();
            $table->softDeletes();
            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_shipments');
    }
};
