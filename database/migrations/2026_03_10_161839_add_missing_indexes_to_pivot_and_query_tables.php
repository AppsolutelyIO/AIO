<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        // Pivot tables: add indexes for JOIN performance
        Schema::table('article_category_pivot', function (Blueprint $table) {
            $table->index('article_id');
            $table->index('article_category_id');
        });

        Schema::table('product_category_pivot', function (Blueprint $table) {
            $table->index('product_id');
            $table->index('product_category_id');
        });

        // Products: composite index for frequent status + published_at queries
        Schema::table('products', function (Blueprint $table) {
            $table->index(['status', 'published_at']);
        });

        // Orders: index on status for filtering
        Schema::table('orders', function (Blueprint $table) {
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('article_category_pivot', function (Blueprint $table) {
            $table->dropIndex(['article_id']);
            $table->dropIndex(['article_category_id']);
        });

        Schema::table('product_category_pivot', function (Blueprint $table) {
            $table->dropIndex(['product_id']);
            $table->dropIndex(['product_category_id']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['status', 'published_at']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });
    }
};
