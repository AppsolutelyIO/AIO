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
        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedBigInteger('left')->default(0);
            $table->unsignedBigInteger('right')->default(0);
            $table->string('title')->default('');
            $table->string('keywords')->nullable();
            $table->string('description')->nullable();
            $table->string('slug')->nullable();
            $table->string('cover')->nullable();
            $table->json('setting')->nullable();
            $table->unsignedTinyInteger('status')->default(0);

            $table->dateTimeTz('published_at')->useCurrent();
            $table->dateTimeTz('expired_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_categories');
    }
};
