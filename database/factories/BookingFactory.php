<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

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
            'room_id' => Room::factory(),
            'checkin_date' => $checkin->toDateString(),
            'checkout_date' => $checkout->toDateString(),
            'guests' => $this->faker->numberBetween(1, 4),
            'status' => Booking::STATUS_CONFIRMED,
            'total_price' => $this->faker->randomFloat(2, 60, 1500),
        ];
    }

    public function forRoom(Room $room): self
    {
        return $this->state(['room_id' => $room->id]);
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
