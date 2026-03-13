<?php

declare(strict_types=1);

use Appsolutely\AIO\Enums\OrderStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     * Normalize legacy numeric status (0,1,2) to enum values, then alter column.
     */
    public function up(): void
    {
        $pending    = OrderStatus::Pending->value;
        $completed  = OrderStatus::Completed->value;
        $cancelled  = OrderStatus::Cancelled->value;
        $enumValues = array_column(OrderStatus::cases(), 'value');

        DB::table('orders')->whereIn('status', ['0', ''])->update(['status' => $cancelled]);
        DB::table('orders')->where('status', '1')->update(['status' => $pending]);
        DB::table('orders')->where('status', '2')->update(['status' => $completed]);
        DB::table('orders')->whereNotNull('status')->whereNotIn('status', $enumValues)->update(['status' => $pending]);

        DB::table('order_items')->whereIn('status', ['0', ''])->update(['status' => $cancelled]);
        DB::table('order_items')->where('status', '1')->update(['status' => $pending]);
        DB::table('order_items')->where('status', '2')->update(['status' => $completed]);
        DB::table('order_items')->whereNotNull('status')->whereNotIn('status', $enumValues)->update(['status' => $pending]);

        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        $values   = array_map(fn (\BackedEnum $case) => $case->value, OrderStatus::cases());
        $enumList = "'" . implode("','", array_map('addslashes', $values)) . "'";

        DB::statement("ALTER TABLE orders MODIFY status ENUM({$enumList}) NULL");
        DB::statement("ALTER TABLE order_items MODIFY status ENUM({$enumList}) NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE orders MODIFY status VARCHAR(255) NULL');
        DB::statement('ALTER TABLE order_items MODIFY status VARCHAR(255) NULL');
    }
};
