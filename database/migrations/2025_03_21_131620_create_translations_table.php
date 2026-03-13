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
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->string('locale');
            $table->string('type'); // php, blade, variable
            $table->text('original_text');
            $table->text('translated_text')->nullable();
            $table->string('translator')->nullable(); // Options: Google, DeepSeek, OpenAI, Manual
            $table->text('call_stack')->nullable();
            $table->unsignedBigInteger('used_count')->default(0);
            $table->dateTimeTz('last_used')->useCurrent();
            $table->timestamps();

            // Add indexes
            $table->index('locale');
            $table->index('type');
            $table->index(['locale', 'type']);
            $table->index('translator');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('translations');
    }
};
