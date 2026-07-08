<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\Concerns\CreatesRoomInventory;
use Tests\TestCase;

class AvailabilityTest extends TestCase
{
    use CreatesRoomInventory;
    use RefreshDatabase;

    private function service(): BookingService
    {
        return app(BookingService::class);
    }

    public function test_no_bookings_leaves_full_inventory(): void
    {
        $roomType = $this->roomTypeWithUnits(5);

        $units = $this->service()->availableUnitsForStay(
            $roomType,
            Carbon::parse('2026-07-01'),
            Carbon::parse('2026-07-04'),
        );

        $this->assertCount(5, $units);
    }

    public function test_fully_booked_range_leaves_nothing(): void
    {
        $roomType = $this->roomTypeWithUnits(1);
        $unit = $roomType->units->first();

        Booking::factory()->forType($roomType, $unit)->stay('2026-07-01', '2026-07-04')->create();

        $units = $this->service()->availableUnitsForStay(
            $roomType,
            Carbon::parse('2026-07-01'),
            Carbon::parse('2026-07-04'),
        );

        $this->assertCount(0, $units);
    }

    public function test_partial_bookings_on_other_units_still_leave_a_free_unit(): void
    {
        $roomType = $this->roomTypeWithUnits(3);

        Booking::factory()->forType($roomType, $roomType->units[0])->stay('2026-07-01', '2026-07-02')->create();
        Booking::factory()->forType($roomType, $roomType->units[1])->stay('2026-07-02', '2026-07-04')->create();

        $units = $this->service()->availableUnitsForStay(
            $roomType,
            Carbon::parse('2026-07-01'),
            Carbon::parse('2026-07-04'),
        );

        $this->assertCount(1, $units);
        $this->assertSame('103', $units->first()?->room_number);
    }

    public function test_checkout_day_is_not_counted_as_occupied(): void
    {
        $roomType = $this->roomTypeWithUnits(1);
        $unit = $roomType->units->first();

        Booking::factory()->forType($roomType, $unit)->stay('2026-06-29', '2026-07-01')->create();

        $units = $this->service()->availableUnitsForStay(
            $roomType,
            Carbon::parse('2026-07-01'),
            Carbon::parse('2026-07-03'),
        );

        $this->assertCount(1, $units);
    }
}
