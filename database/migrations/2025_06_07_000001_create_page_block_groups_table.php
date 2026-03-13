<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('page_block_groups', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('remark')->nullable();
            $table->unsignedTinyInteger('sort')->default(0);
            $table->unsignedTinyInteger('status')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_block_groups');
    }
};
