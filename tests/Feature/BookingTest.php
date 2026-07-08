<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    private string $checkin;

    private string $checkout;

    protected function setUp(): void
    {
        parent::setUp();

        $this->checkin = Carbon::today()->addDays(60)->toDateString();
        $this->checkout = Carbon::today()->addDays(63)->toDateString(); // 3 nights
    }

    private function room(int $totalRooms = 1, float $price = 100): Room
    {
        return Room::factory()
            ->forHotel(Hotel::factory()->inCity('Bookville')->create())
            ->create(['price_per_night' => $price, 'max_occupancy' => 2, 'total_rooms' => $totalRooms]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(Room $room, array $overrides = []): array
    {
        return array_merge([
            'room_id' => $room->id,
            'checkin_date' => $this->checkin,
            'checkout_date' => $this->checkout,
            'guests' => 2,
        ], $overrides);
    }

    public function test_guests_cannot_book_without_a_token(): void
    {
        $this->postJson('/api/bookings', $this->payload($this->room()))->assertStatus(401);
    }

    public function test_booking_snapshots_the_total_price(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $room = $this->room(price: 100);

        $this->postJson('/api/bookings', $this->payload($room))
            ->assertCreated()
            ->assertJsonPath('data.status', Booking::STATUS_CONFIRMED)
            ->assertJsonPath('data.total_price', '300.00');

        $this->assertDatabaseHas('bookings', ['room_id' => $room->id, 'guests' => 2]);
    }

    public function test_booking_a_full_room_returns_422(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $room = $this->room(totalRooms: 1);
        Booking::factory()->forRoom($room)->stay($this->checkin, $this->checkout)->create();

        $this->postJson('/api/bookings', $this->payload($room))->assertStatus(422);
    }

    public function test_too_many_guests_is_a_validation_error(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $room = $this->room();

        $this->postJson('/api/bookings', $this->payload($room, ['guests' => 5]))
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('guests');
    }

    public function test_a_booking_reduces_the_next_search(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $room = $this->room(totalRooms: 2);

        $url = '/api/search?'.http_build_query([
            'city' => 'Bookville',
            'checkin_date' => $this->checkin,
            'checkout_date' => $this->checkout,
            'guests' => 2,
        ]);

        $this->getJson($url)->assertJsonPath('data.0.rooms.0.available_units', 2);

        $this->postJson('/api/bookings', $this->payload($room))->assertCreated();

        $this->getJson($url)->assertJsonPath('data.0.rooms.0.available_units', 1);
    }
}
