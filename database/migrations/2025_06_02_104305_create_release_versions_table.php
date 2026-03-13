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
        Schema::create('release_versions', function (Blueprint $table) {
            $table->id();
            $table->string('version');
            $table->string('remark')->nullable();
            $table->string('release_channel')->nullable(); // stable, beta, dev
            $table->tinyInteger('status');
            $table->dateTimeTz('published_at')->useCurrent();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('release_versions');
    }
};
