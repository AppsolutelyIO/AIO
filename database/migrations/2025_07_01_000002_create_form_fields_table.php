<?php

declare(strict_types=1);

use Appsolutely\AIO\Enums\FormFieldType;
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
        Schema::create('form_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained('forms')->onDelete('cascade');
            $table->string('label');
            $table->string('name');
            $table->enum('type', array_column(FormFieldType::cases(), 'value'));
            $table->string('placeholder')->nullable();
            $table->boolean('required')->default(false);
            $table->json('options')->nullable();
            $table->integer('sort')->default(0);
            $table->json('setting')->nullable();
            $table->timestamps();

            $table->index(['form_id', 'sort']);
            $table->index(['form_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_fields');
    }
};
