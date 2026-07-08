<?php

declare(strict_types=1);

namespace Tests\Concerns;

use App\Models\Hotel;
use App\Models\RoomType;
use App\Models\RoomUnit;

trait CreatesRoomInventory
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    protected function roomTypeWithUnits(int $units = 1, array $attributes = [], ?Hotel $hotel = null): RoomType
    {
        $hotel ??= Hotel::factory()->create();

        $roomType = RoomType::factory()
            ->forHotel($hotel)
            ->create(array_merge(['max_occupancy' => 2], $attributes));

        for ($i = 1; $i <= $units; $i++) {
            RoomUnit::factory()->forType($roomType)->create([
                'room_number' => (string) (100 + $i),
            ]);
        }

        return $roomType->load('units');
    }
}
