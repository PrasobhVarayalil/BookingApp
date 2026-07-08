<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotels', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('city');
            $table->string('country');
            $table->unsignedTinyInteger('rating');

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('city');
            $table->index(['city', 'rating']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotels');
    }
};
