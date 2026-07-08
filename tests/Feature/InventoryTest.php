<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_hotel_with_rooms_cannot_be_deleted(): void
    {
        $hotel = Hotel::factory()->create();
        Room::factory()->forHotel($hotel)->create();

        $this->actingAs(User::factory()->create())
            ->delete(route('hotels.destroy', $hotel))
            ->assertSessionHas('error');

        $this->assertNotSoftDeleted('hotels', ['id' => $hotel->id]);
    }

    public function test_room_with_bookings_cannot_be_deleted(): void
    {
        $room = Room::factory()->forHotel(Hotel::factory()->create())->create();
        Booking::factory()->forRoom($room)->create();

        $this->actingAs(User::factory()->create())
            ->delete(route('rooms.destroy', $room))
            ->assertSessionHas('error');

        $this->assertNotSoftDeleted('rooms', ['id' => $room->id]);
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
