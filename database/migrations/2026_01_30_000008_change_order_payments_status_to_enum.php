<?php

declare(strict_types=1);

use Appsolutely\AIO\Enums\OrderPaymentStatus;
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
        $allowed = [
            OrderPaymentStatus::Pending->value,
            OrderPaymentStatus::Paid->value,
            OrderPaymentStatus::Failed->value,
            OrderPaymentStatus::Refunded->value,
        ];
        DB::table('order_payments')
            ->whereNotIn('status', $allowed)
            ->orWhereNull('status')
            ->orWhere('status', '')
            ->update(['status' => OrderPaymentStatus::Pending->value]);

        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        $values   = array_map(fn (\BackedEnum $case) => $case->value, OrderPaymentStatus::cases());
        $enumList = "'" . implode("','", array_map('addslashes', $values)) . "'";

        DB::statement("ALTER TABLE order_payments MODIFY status ENUM({$enumList}) NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE order_payments MODIFY status VARCHAR(255) NOT NULL');
    }
};
