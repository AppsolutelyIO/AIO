<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('reference');
            $table->string('summary');
            $table->unsignedInteger('amount')->default(0);
            $table->unsignedInteger('discounted_amount')->default(0);
            $table->unsignedInteger('total_amount')->default(0);
            $table->string('status')->nullable();
            $table->json('delivery_info')->nullable();
            $table->string('note')->nullable();
            $table->string('remark')->nullable();
            $table->string('ip')->nullable();
            $table->json('request')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('user_id');
            $table->index('reference');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
