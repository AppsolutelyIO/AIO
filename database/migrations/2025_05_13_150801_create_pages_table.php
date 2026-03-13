<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->ulid('reference');
            $table->string('title')->nullable();
            $table->string('name')->nullable();
            $table->string('slug')->nullable();
            $table->string('keywords')->nullable();
            $table->text('description')->nullable();
            $table->mediumText('content')->nullable();
            $table->json('setting')->nullable();
            $table->string('h1_text')->nullable();
            $table->string('canonical_url')->nullable();
            $table->string('meta_robots')->nullable();
            $table->string('og_title')->nullable();
            $table->text('og_description')->nullable();
            $table->string('og_image')->nullable();
            $table->json('structured_data')->nullable();
            $table->string('hreflang')->nullable();
            $table->string('language')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->dateTimeTz('published_at')->useCurrent();
            $table->dateTimeTz('expired_at')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
