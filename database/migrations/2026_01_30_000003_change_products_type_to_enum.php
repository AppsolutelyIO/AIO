<?php

declare(strict_types=1);

use Appsolutely\AIO\Enums\ProductType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        $values   = array_map(fn (\BackedEnum $case) => $case->value, ProductType::cases());
        $enumList = "'" . implode("','", array_map('addslashes', $values)) . "'";

        DB::statement("ALTER TABLE products MODIFY type ENUM({$enumList}) NOT NULL DEFAULT 'PHYSICAL'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE products MODIFY type VARCHAR(255) NOT NULL DEFAULT 'PHYSICAL'");
    }
};
