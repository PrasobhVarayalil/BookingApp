<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Hotel;
use App\Models\RoomType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RoomType>
 */
class RoomTypeFactory extends Factory
{
    protected $model = RoomType::class;

    public function definition(): array
    {
        return [
            'hotel_id' => Hotel::factory(),
            'name' => $this->faker->randomElement(['Standard Twin', 'Deluxe King', 'Executive Suite', 'Family Room']),
            'price_per_night' => $this->faker->randomFloat(2, 60, 500),
            'max_occupancy' => $this->faker->numberBetween(1, 4),
        ];
    }

    public function forHotel(Hotel $hotel): self
    {
        return $this->state(['hotel_id' => $hotel->id]);
    }
}
