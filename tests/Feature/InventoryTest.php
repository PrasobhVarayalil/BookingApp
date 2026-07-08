<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Hotel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesRoomInventory;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    use CreatesRoomInventory;
    use RefreshDatabase;

    public function test_hotel_with_room_types_cannot_be_deleted(): void
    {
        $hotel = Hotel::factory()->create();
        $this->roomTypeWithUnits(1, [], $hotel);

        $this->actingAs(User::factory()->create())
            ->delete(route('hotels.destroy', $hotel))
            ->assertSessionHas('error');

        $this->assertNotSoftDeleted('hotels', ['id' => $hotel->id]);
    }

    public function test_room_type_with_bookings_cannot_be_deleted(): void
    {
        $roomType = $this->roomTypeWithUnits(1);
        Booking::factory()->forType($roomType, $roomType->units->first())->create();

        $this->actingAs(User::factory()->create())
            ->delete(route('rooms.destroy', $roomType))
            ->assertSessionHas('error');

        $this->assertNotSoftDeleted('room_types', ['id' => $roomType->id]);
    }

    public function test_empty_hotel_can_be_deleted(): void
    {
        $hotel = Hotel::factory()->create();

        $this->actingAs(User::factory()->create())
            ->delete(route('hotels.destroy', $hotel))
            ->assertSessionHas('success');

        $this->assertSoftDeleted('hotels', ['id' => $hotel->id]);
    }

    public function test_api_hotels_are_filtered_by_city(): void
    {
        Hotel::factory()->inCity('Dubai')->create();
        Hotel::factory()->inCity('London')->create();

        $this->getJson('/api/hotels?city=dubai')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.city', 'Dubai');
    }
}
