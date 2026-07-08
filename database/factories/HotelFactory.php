<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Hotel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Hotel>
 */
class HotelFactory extends Factory
{
    protected $model = Hotel::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company().' Hotel',
            'city' => $this->faker->randomElement(['Dubai', 'London', 'Paris', 'Tokyo', 'Mumbai']),
            'country' => $this->faker->country(),
            'rating' => $this->faker->numberBetween(1, 5),
        ];
    }

    public function inCity(string $city): self
    {
        return $this->state(['city' => $city]);
    }
}
