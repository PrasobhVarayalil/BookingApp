<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Booking;
use App\Models\RoomType;
use App\Models\RoomUnit;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        $checkin = Carbon::today()->addDays($this->faker->numberBetween(1, 20));
        $checkout = $checkin->copy()->addDays($this->faker->numberBetween(1, 5));

        return [
            'booking_reference' => 'BK-'.now()->format('Ymd').'-'.strtoupper(Str::random(4)),
            'room_type_id' => RoomType::factory(),
            'room_unit_id' => RoomUnit::factory(),
            'checkin_date' => $checkin->toDateString(),
            'checkout_date' => $checkout->toDateString(),
            'guests' => $this->faker->numberBetween(1, 4),
            'guest_name' => $this->faker->name(),
            'guest_email' => $this->faker->safeEmail(),
            'guest_phone' => $this->faker->optional()->phoneNumber(),
            'status' => Booking::STATUS_CONFIRMED,
            'total_price' => $this->faker->randomFloat(2, 60, 1500),
        ];
    }

    public function forType(RoomType $roomType, ?RoomUnit $unit = null): self
    {
        return $this->state([
            'room_type_id' => $roomType->id,
            'room_unit_id' => $unit?->id ?? $roomType->units()->value('id'),
        ]);
    }

    public function stay(string $checkin, string $checkout): self
    {
        return $this->state([
            'checkin_date' => $checkin,
            'checkout_date' => $checkout,
        ]);
    }

    public function cancelled(): self
    {
        return $this->state(['status' => Booking::STATUS_CANCELLED]);
    }
}
