<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_types', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('hotel_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->decimal('price_per_night', 10, 2);
            $table->unsignedSmallInteger('max_occupancy');

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_types');
    }
};
