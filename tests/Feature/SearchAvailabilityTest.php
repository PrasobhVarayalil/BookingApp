<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Hotel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\Concerns\CreatesRoomInventory;
use Tests\TestCase;

class SearchAvailabilityTest extends TestCase
{
    use CreatesRoomInventory;
    use RefreshDatabase;

    private const CITY = 'Testville';

    private string $checkin;

    private string $checkout;

    protected function setUp(): void
    {
        parent::setUp();

        $this->checkin = Carbon::today()->addDays(30)->toDateString();
        $this->checkout = Carbon::today()->addDays(33)->toDateString();
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
        $this->roomTypeWithUnits(4, ['price_per_night' => 120], Hotel::factory()->inCity(self::CITY)->create());

        $this->getJson($this->url())
            ->assertOk()
            ->assertJsonPath('meta.nights', 3)
            ->assertJsonPath('data.0.rooms.0.available_units', 4)
            ->assertJsonPath('data.0.rooms.0.price_per_night', '120.00')
            ->assertJsonPath('data.0.rooms.0.total_price', '360.00')
            ->assertJsonPath('data.0.rooms.0.available_room_numbers.0', '101');
    }

    public function test_fully_booked_room_disappears_from_results(): void
    {
        $roomType = $this->roomTypeWithUnits(1, [], Hotel::factory()->inCity(self::CITY)->create());
        $unit = $roomType->units->first();

        Booking::factory()->forType($roomType, $unit)->stay($this->checkin, $this->checkout)->create();

        $this->getJson($this->url())->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_partial_overlap_on_other_units_still_shows_one_free_unit(): void
    {
        $roomType = $this->roomTypeWithUnits(3, [], Hotel::factory()->inCity(self::CITY)->create());
        $midpoint = Carbon::parse($this->checkin)->addDay()->toDateString();

        Booking::factory()->forType($roomType, $roomType->units[0])->stay($this->checkin, $midpoint)->create();
        Booking::factory()->forType($roomType, $roomType->units[1])->stay($midpoint, $this->checkout)->create();

        $this->getJson($this->url())
            ->assertOk()
            ->assertJsonPath('data.0.rooms.0.available_units', 1)
            ->assertJsonPath('data.0.rooms.0.available_room_numbers.0', '103');
    }

    public function test_cancelled_bookings_do_not_reduce_availability(): void
    {
        $roomType = $this->roomTypeWithUnits(1, [], Hotel::factory()->inCity(self::CITY)->create());
        $unit = $roomType->units->first();

        Booking::factory()->forType($roomType, $unit)->cancelled()->stay($this->checkin, $this->checkout)->create();

        $this->getJson($this->url())
            ->assertOk()
            ->assertJsonPath('data.0.rooms.0.available_units', 1);
    }

    public function test_rooms_smaller_than_party_are_excluded(): void
    {
        $this->roomTypeWithUnits(5, ['max_occupancy' => 2], Hotel::factory()->inCity(self::CITY)->create());

        $this->getJson($this->url(guests: 3))->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_checkout_before_checkin_is_rejected(): void
    {
        $this->getJson($this->url(checkin: $this->checkout, checkout: $this->checkin))
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('checkout_date');
    }
}
