<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Hotel;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SearchAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    private const CITY = 'Testville';

    private string $checkin;

    private string $checkout;

    protected function setUp(): void
    {
        parent::setUp();

        $this->checkin = Carbon::today()->addDays(30)->toDateString();
        $this->checkout = Carbon::today()->addDays(33)->toDateString(); // 3 nights
    }

    private function room(array $attributes = []): Room
    {
        return Room::factory()
            ->forHotel(Hotel::factory()->inCity(self::CITY)->create())
            ->create($attributes + ['max_occupancy' => 2]);
    }

    private function url(int $guests = 2, ?string $checkin = null, ?string $checkout = null): string
    {
        return '/api/search?'.http_build_query([
            'city' => self::CITY,
            'checkin_date' => $checkin ?? $this->checkin,
            'checkout_date' => $checkout ?? $this->checkout,
            'guests' => $guests,
        ]);
    }

    public function test_free_room_returns_full_inventory_and_priced_total(): void
    {
        $this->room(['price_per_night' => 120, 'total_rooms' => 4]);

        $this->getJson($this->url())
            ->assertOk()
            ->assertJsonPath('meta.nights', 3)
            ->assertJsonPath('data.0.rooms.0.available_units', 4)
            ->assertJsonPath('data.0.rooms.0.price_per_night', '120.00')
            ->assertJsonPath('data.0.rooms.0.total_price', '360.00');
    }

    public function test_fully_booked_room_disappears_from_results(): void
    {
        $room = $this->room(['total_rooms' => 1]);
        Booking::factory()->forRoom($room)->stay($this->checkin, $this->checkout)->create();

        $this->getJson($this->url())->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_partial_overlap_reduces_units(): void
    {
        $room = $this->room(['total_rooms' => 3]);
        $midpoint = Carbon::parse($this->checkin)->addDay()->toDateString();

        Booking::factory()->forRoom($room)->stay($this->checkin, $midpoint)->create();
        Booking::factory()->forRoom($room)->stay($midpoint, $this->checkout)->create();

        // Nothing overlaps on more than one unit per night here, so 3 - 1 = 2.
        $this->getJson($this->url())
            ->assertOk()
            ->assertJsonPath('data.0.rooms.0.available_units', 2);
    }

    public function test_cancelled_bookings_do_not_reduce_availability(): void
    {
        $room = $this->room(['total_rooms' => 1]);
        Booking::factory()->forRoom($room)->cancelled()->stay($this->checkin, $this->checkout)->create();

        $this->getJson($this->url())
            ->assertOk()
            ->assertJsonPath('data.0.rooms.0.available_units', 1);
    }

    public function test_rooms_smaller_than_party_are_excluded(): void
    {
        $this->room(['total_rooms' => 5, 'max_occupancy' => 2]);

        $this->getJson($this->url(guests: 3))->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_checkout_before_checkin_is_rejected(): void
    {
        $this->getJson($this->url(checkin: $this->checkout, checkout: $this->checkin))
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('checkout_date');
    }
}
