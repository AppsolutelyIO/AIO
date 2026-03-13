<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('page_block_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained('pages')->cascadeOnDelete();
            $table->foreignId('block_id')->constrained('page_blocks')->cascadeOnDelete();
            $table->foreignId('block_value_id')->constrained('page_block_values')->cascadeOnDelete();
            $table->string('reference')->nullable();
            $table->string('type')->nullable();
            $table->string('remark')->nullable();
            $table->unsignedTinyInteger('sort')->default(0);
            $table->unsignedTinyInteger('status')->default(0);
            $table->dateTimeTz('published_at')->useCurrent();
            $table->dateTimeTz('expired_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_block_settings');
    }
};
