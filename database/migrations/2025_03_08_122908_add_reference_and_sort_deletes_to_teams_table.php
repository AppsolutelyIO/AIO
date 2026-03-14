<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('teams')) {
            return;
        }

        Schema::table('teams', function (Blueprint $table) {
            if (! Schema::hasColumn('teams', 'reference')) {
                $table->string('reference')->nullable()->after('user_id');
            }

            if (! Schema::hasColumn('teams', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('teams')) {
            return;
        }

        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn('reference');
            $table->dropSoftDeletes();
        });
    }
};
