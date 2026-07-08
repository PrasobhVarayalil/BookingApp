<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cities', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('country_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();

            $table->unique(['country_id', 'name']);
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cities');
    }
};
