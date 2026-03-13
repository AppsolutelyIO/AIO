<?php

declare(strict_types=1);

use Appsolutely\AIO\Enums\BuildStatus;
use Appsolutely\AIO\Enums\Platform;
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
        $platformValues = array_map(fn (\BackedEnum $case) => $case->value, Platform::cases());
        DB::table('release_builds')
            ->whereNotIn('platform', $platformValues)
            ->orWhereNull('platform')
            ->orWhere('platform', '')
            ->update(['platform' => Platform::Windows->value]);

        $buildStatusValues = array_map(fn (\BackedEnum $case) => $case->value, BuildStatus::cases());
        DB::table('release_builds')
            ->whereNotNull('build_status')
            ->where('build_status', '!=', '')
            ->whereNotIn('build_status', $buildStatusValues)
            ->update(['build_status' => null]);

        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        $platformEnumList = "'" . implode("','", array_map('addslashes', $platformValues)) . "'";
        DB::statement("ALTER TABLE release_builds MODIFY platform ENUM({$platformEnumList}) NOT NULL DEFAULT 'windows'");

        $buildStatusEnumList = "'" . implode("','", array_map('addslashes', $buildStatusValues)) . "'";
        DB::statement("ALTER TABLE release_builds MODIFY build_status ENUM({$buildStatusEnumList}) NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE release_builds MODIFY platform VARCHAR(255) NOT NULL DEFAULT 'windows'");
        DB::statement('ALTER TABLE release_builds MODIFY build_status VARCHAR(255) NULL');
    }
};
