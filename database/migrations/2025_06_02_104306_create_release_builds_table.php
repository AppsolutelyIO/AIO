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
        Schema::create('release_builds', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('version_id');
            $table->string('platform')->default('windows');
            $table->string('arch')->nullable();
            $table->tinyInteger('force_update')->default(0);
            $table->json('gray_strategy')->nullable();
            $table->text('release_notes')->nullable();
            $table->string('build_status')->nullable();
            $table->string('build_log')->nullable();
            $table->string('path')->nullable();
            $table->string('signature')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->dateTimeTz('published_at')->useCurrent();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('version_id')->references('id')->on('release_versions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('release_builds');
    }
};
