<?php

declare(strict_types=1);

use Appsolutely\AIO\Enums\TranslationType;
use Appsolutely\AIO\Enums\TranslatorType;
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

        $typeValues   = array_map(fn (\BackedEnum $case) => $case->value, TranslationType::cases());
        $typeEnumList = "'" . implode("','", array_map('addslashes', $typeValues)) . "'";

        $translatorValues   = array_map(fn (\BackedEnum $case) => $case->value, TranslatorType::cases());
        $translatorEnumList = "'" . implode("','", array_map('addslashes', $translatorValues)) . "'";

        DB::statement("ALTER TABLE translations MODIFY type ENUM({$typeEnumList}) NOT NULL");
        DB::statement("ALTER TABLE translations MODIFY translator ENUM({$translatorEnumList}) NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE translations MODIFY type VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE translations MODIFY translator VARCHAR(255) NULL');
    }
};
