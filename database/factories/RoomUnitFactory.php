<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\RoomType;
use App\Models\RoomUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RoomUnit>
 */
class RoomUnitFactory extends Factory
{
    protected $model = RoomUnit::class;

    public function definition(): array
    {
        return [
            'room_type_id' => RoomType::factory(),
            'room_number' => (string) $this->faker->unique()->numberBetween(100, 999),
            'status' => RoomUnit::STATUS_AVAILABLE,
        ];
    }

    public function forType(RoomType $roomType): self
    {
        return $this->state(['room_type_id' => $roomType->id]);
    }
}
