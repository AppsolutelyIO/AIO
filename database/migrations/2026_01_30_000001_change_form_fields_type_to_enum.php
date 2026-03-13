<?php

declare(strict_types=1);

use Appsolutely\AIO\Enums\FormFieldType;
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

        $values   = array_map(fn (\BackedEnum $case) => $case->value, FormFieldType::cases());
        $enumList = "'" . implode("','", array_map('addslashes', $values)) . "'";

        DB::statement("ALTER TABLE form_fields MODIFY type ENUM({$enumList}) NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE form_fields MODIFY type VARCHAR(50) NOT NULL');
    }
};
