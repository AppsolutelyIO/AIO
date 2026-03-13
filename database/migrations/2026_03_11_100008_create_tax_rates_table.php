<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('tax_rates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('country')->nullable();
            $table->string('region')->nullable();
            $table->string('type');
            $table->unsignedInteger('rate');
            $table->unsignedInteger('priority')->default(0);
            $table->boolean('is_compound')->default(false);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['country', 'region', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_rates');
    }
};
