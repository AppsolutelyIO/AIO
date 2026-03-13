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
        Schema::create('page_block_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('block_id')->constrained('page_blocks')->cascadeOnDelete();
            $table->string('view')->nullable();
            $table->json('query_options')->nullable();
            $table->json('display_options')->nullable();
            $table->json('scripts')->nullable();
            $table->json('styles')->nullable();
            $table->text('template')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_block_values');
    }
};
