<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('room_id')->constrained()->cascadeOnDelete();
            $table->date('checkin_date');
            $table->date('checkout_date');
            $table->unsignedSmallInteger('guests');
            $table->string('status')->default('confirmed');
            $table->decimal('total_price', 10, 2);

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // The availability query filters by room and a date-range overlap.
            $table->index(['room_id', 'status', 'checkin_date', 'checkout_date'], 'bookings_availability_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
