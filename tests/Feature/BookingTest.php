<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Hotel;
use App\Models\RoomType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\CreatesRoomInventory;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use CreatesRoomInventory;
    use RefreshDatabase;

    private string $checkin;

    private string $checkout;

    protected function setUp(): void
    {
        parent::setUp();

        $this->checkin = Carbon::today()->addDays(60)->toDateString();
        $this->checkout = Carbon::today()->addDays(63)->toDateString();
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(RoomType $roomType, array $overrides = []): array
    {
        return array_merge([
            'room_type_id' => $roomType->id,
            'checkin_date' => $this->checkin,
            'checkout_date' => $this->checkout,
            'guests' => 2,
            'guest_name' => 'Jane Guest',
            'guest_email' => 'jane@example.com',
        ], $overrides);
    }

    public function test_guests_cannot_book_without_a_token(): void
    {
        $roomType = $this->roomTypeWithUnits(1, [], Hotel::factory()->inCity('Bookville')->create());

        $this->postJson('/api/bookings', $this->payload($roomType))->assertStatus(401);
    }

    public function test_booking_snapshots_the_total_price_and_assigns_a_unit(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $roomType = $this->roomTypeWithUnits(1, ['price_per_night' => 100], Hotel::factory()->inCity('Bookville')->create());

        $this->postJson('/api/bookings', $this->payload($roomType))
            ->assertCreated()
            ->assertJsonPath('data.status', Booking::STATUS_CONFIRMED)
            ->assertJsonPath('data.total_price', '300.00')
            ->assertJsonPath('data.guest_name', 'Jane Guest');

        $this->assertDatabaseHas('bookings', [
            'room_type_id' => $roomType->id,
            'room_unit_id' => $roomType->units->first()?->id,
            'guests' => 2,
        ]);
    }

    public function test_booking_a_full_room_type_returns_422(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $roomType = $this->roomTypeWithUnits(1, [], Hotel::factory()->inCity('Bookville')->create());
        $unit = $roomType->units->first();

        Booking::factory()->forType($roomType, $unit)->stay($this->checkin, $this->checkout)->create();

        $this->postJson('/api/bookings', $this->payload($roomType))->assertStatus(422);
    }

    public function test_manual_room_selection_assigns_the_requested_unit(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $roomType = $this->roomTypeWithUnits(2, [], Hotel::factory()->inCity('Bookville')->create());
        $second = $roomType->units[1];

        $this->postJson('/api/bookings', $this->payload($roomType, ['room_unit_id' => $second->id]))
            ->assertCreated()
            ->assertJsonPath('data.room_unit.room_number', '102');
    }

    public function test_too_many_guests_is_a_validation_error(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $roomType = $this->roomTypeWithUnits(1, ['max_occupancy' => 2], Hotel::factory()->inCity('Bookville')->create());

        $this->postJson('/api/bookings', $this->payload($roomType, ['guests' => 5]))
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('guests');
    }

    public function test_a_booking_reduces_the_next_search(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $roomType = $this->roomTypeWithUnits(2, [], Hotel::factory()->inCity('Bookville')->create());

        $url = '/api/search?'.http_build_query([
            'city' => 'Bookville',
            'checkin_date' => $this->checkin,
            'checkout_date' => $this->checkout,
            'guests' => 2,
        ]);

        $this->getJson($url)->assertJsonPath('data.0.rooms.0.available_units', 2);

        $this->postJson('/api/bookings', $this->payload($roomType))->assertCreated();

        $this->getJson($url)->assertJsonPath('data.0.rooms.0.available_units', 1);
    }

    public function test_cancelled_bookings_free_the_unit_again(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $roomType = $this->roomTypeWithUnits(1, [], Hotel::factory()->inCity('Bookville')->create());

        $response = $this->postJson('/api/bookings', $this->payload($roomType))->assertCreated();
        $bookingId = $response->json('data.id');

        $this->deleteJson("/api/bookings/{$bookingId}")->assertNoContent();

        $this->assertDatabaseHas('bookings', [
            'id' => $bookingId,
            'status' => Booking::STATUS_CANCELLED,
        ]);
    }
}
