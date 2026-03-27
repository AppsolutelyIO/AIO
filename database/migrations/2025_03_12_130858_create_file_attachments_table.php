<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('file_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_id')->constrained('files');
            $table->nullableUuidMorphs('attachable');
            $table->string('type')->nullable();
            $table->string('file_path');
            $table->string('optimized_path')->nullable();
            $table->string('optimized_format', 20)->nullable();
            $table->unsignedBigInteger('optimized_size')->nullable();
            $table->unsignedInteger('optimized_width')->nullable();
            $table->unsignedInteger('optimized_height')->nullable();
            $table->string('title')->nullable();
            $table->string('keyword')->nullable();
            $table->text('description')->nullable();
            $table->text('content')->nullable();
            $table->json('config')->nullable();
            $table->unsignedTinyInteger('status')->default(0);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->dateTimeTz('published_at')->useCurrent();
            $table->dateTimeTz('expired_at')->nullable();
            $table->timestamps();

            $table->index(['attachable_type', 'attachable_id', 'type'], 'file_attachments_polymorphic_type_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('file_attachments');
    }
};
