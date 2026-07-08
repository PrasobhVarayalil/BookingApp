<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_units', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('room_type_id')->constrained()->cascadeOnDelete();
            $table->string('room_number');
            $table->string('status')->default('available');

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['room_type_id', 'room_number']);
            $table->index(['room_type_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_units');
    }
};
