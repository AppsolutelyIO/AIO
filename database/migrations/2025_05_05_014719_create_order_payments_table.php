<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('order_payments', function (Blueprint $table) {
            $table->id();
            $table->string('reference');
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('payment_id');
            $table->string('vendor')->nullable();
            $table->string('vendor_reference')->nullable();
            $table->json('vendor_extra_info')->nullable();
            $table->unsignedInteger('payment_amount')->default(0);
            $table->string('status');
            $table->timestamps();
            $table->softDeletes();
            $table->index('order_id');
            $table->index('payment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_payments');
    }
};
