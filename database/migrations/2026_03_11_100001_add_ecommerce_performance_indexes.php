<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasIndex('orders', 'orders_status_index')) {
                $table->index('status');
            }
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'created_at']);
            if (! Schema::hasIndex('orders', 'orders_coupon_id_index')) {
                $table->index('coupon_id');
            }
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->index(['order_id', 'product_id']);
        });

        Schema::table('refunds', function (Blueprint $table) {
            $table->index(['order_id', 'status']);
        });

        Schema::table('coupon_usages', function (Blueprint $table) {
            $table->index(['coupon_id', 'user_id']);
        });

        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->index(['product_sku_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'status']);
            $table->dropIndex(['user_id', 'created_at']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex(['order_id', 'product_id']);
        });

        Schema::table('refunds', function (Blueprint $table) {
            $table->dropIndex(['order_id', 'status']);
        });

        Schema::table('coupon_usages', function (Blueprint $table) {
            $table->dropIndex(['coupon_id', 'user_id']);
        });

        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->dropIndex(['product_sku_id', 'created_at']);
        });
    }
};
