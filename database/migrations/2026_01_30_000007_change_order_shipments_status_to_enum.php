<?php

declare(strict_types=1);

use Appsolutely\AIO\Enums\OrderShipmentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     * Normalize unknown status to 'pending', then alter to enum.
     */
    public function up(): void
    {
        $allowed = [OrderShipmentStatus::Pending->value, OrderShipmentStatus::Shipped->value, OrderShipmentStatus::Delivered->value];
        DB::table('order_shipments')
            ->whereNotIn('status', $allowed)
            ->orWhereNull('status')
            ->orWhere('status', '')
            ->update(['status' => OrderShipmentStatus::Pending->value]);

        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        $values   = array_map(fn (\BackedEnum $case) => $case->value, OrderShipmentStatus::cases());
        $enumList = "'" . implode("','", array_map('addslashes', $values)) . "'";

        DB::statement("ALTER TABLE order_shipments MODIFY status ENUM({$enumList}) NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE order_shipments MODIFY status VARCHAR(255) NOT NULL');
    }
};
