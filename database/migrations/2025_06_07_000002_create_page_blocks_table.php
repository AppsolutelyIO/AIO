<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('page_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('block_group_id')->constrained('page_block_groups')->cascadeOnDelete();
            $table->string('title');
            $table->string('reference')->nullable();
            $table->string('class');
            $table->string('remark')->nullable();
            $table->text('description')->nullable();
            $table->text('template')->nullable();
            $table->text('instruction')->nullable();
            $table->enum('scope', ['page', 'global'])->default('page');
            $table->json('schema')->nullable();
            $table->json('schema_values')->nullable();
            $table->unsignedTinyInteger('droppable')->nullable()->default(0);
            $table->json('setting')->nullable();
            $table->unsignedTinyInteger('sort')->default(0);
            $table->unsignedTinyInteger('status')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_blocks');
    }
};
