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
            $table->string('booking_reference')->unique();
            $table->foreignUuid('room_type_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('room_unit_id')->nullable()->constrained()->nullOnDelete();
            $table->date('checkin_date');
            $table->date('checkout_date');
            $table->unsignedSmallInteger('guests');
            $table->string('guest_name');
            $table->string('guest_email');
            $table->string('guest_phone')->nullable();
            $table->string('status')->default('confirmed');
            $table->decimal('total_price', 10, 2);

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['room_type_id', 'status', 'checkin_date', 'checkout_date'], 'bookings_availability_index');
            $table->index(['room_unit_id', 'status', 'checkin_date', 'checkout_date'], 'bookings_unit_availability_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
