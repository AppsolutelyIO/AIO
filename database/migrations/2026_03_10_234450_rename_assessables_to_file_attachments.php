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
        // Drop the old composite index before renaming columns
        Schema::table('assessables', function (Blueprint $table) {
            $table->dropIndex('assessables_polymorphic_type_index');
        });

        // Rename morph columns
        Schema::table('assessables', function (Blueprint $table) {
            $table->renameColumn('assessable_type', 'attachable_type');
            $table->renameColumn('assessable_id', 'attachable_id');
        });

        // Rename the table
        Schema::rename('assessables', 'file_attachments');

        // Re-add composite index with new column names
        Schema::table('file_attachments', function (Blueprint $table) {
            $table->index(['attachable_type', 'attachable_id', 'type'], 'file_attachments_polymorphic_type_index');
        });

        // Rename assessable_id on release_builds if column exists
        if (Schema::hasColumn('release_builds', 'assessable_id')) {
            Schema::table('release_builds', function (Blueprint $table) {
                $table->renameColumn('assessable_id', 'file_attachment_id');
            });
        }

        // Add unique index on files.hash for deduplication guarantee
        Schema::table('files', function (Blueprint $table) {
            $table->dropIndex(['hash']);
            $table->unique('hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove unique index and restore regular index on files.hash
        Schema::table('files', function (Blueprint $table) {
            $table->dropUnique(['hash']);
            $table->index('hash');
        });

        // Rename file_attachment_id back on release_builds
        if (Schema::hasColumn('release_builds', 'file_attachment_id')) {
            Schema::table('release_builds', function (Blueprint $table) {
                $table->renameColumn('file_attachment_id', 'assessable_id');
            });
        }

        // Drop the new composite index
        Schema::table('file_attachments', function (Blueprint $table) {
            $table->dropIndex('file_attachments_polymorphic_type_index');
        });

        // Rename the table back
        Schema::rename('file_attachments', 'assessables');

        // Rename morph columns back
        Schema::table('assessables', function (Blueprint $table) {
            $table->renameColumn('attachable_type', 'assessable_type');
            $table->renameColumn('attachable_id', 'assessable_id');
        });

        // Re-add original composite index
        Schema::table('assessables', function (Blueprint $table) {
            $table->index(['assessable_type', 'assessable_id', 'type'], 'assessables_polymorphic_type_index');
        });
    }
};
