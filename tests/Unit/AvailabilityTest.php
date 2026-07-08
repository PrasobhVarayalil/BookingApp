<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Tests\TestCase;

class AvailabilityTest extends TestCase
{
    private function service(): BookingService
    {
        return app(BookingService::class);
    }

    /**
     * @param  list<array{0: string, 1: string}>  $ranges
     * @return Collection<int, Booking>
     */
    private function bookings(array $ranges): Collection
    {
        return collect($ranges)->map(fn (array $r) => new Booking([
            'checkin_date' => $r[0],
            'checkout_date' => $r[1],
        ]));
    }

    public function test_no_bookings_leaves_full_inventory(): void
    {
        $units = $this->service()->availableUnits(
            5,
            collect(),
            Carbon::parse('2026-07-01'),
            Carbon::parse('2026-07-04'),
        );

        $this->assertSame(5, $units);
    }

    public function test_fully_booked_range_leaves_nothing(): void
    {
        $units = $this->service()->availableUnits(
            2,
            $this->bookings([['2026-07-01', '2026-07-04'], ['2026-07-01', '2026-07-04']]),
            Carbon::parse('2026-07-01'),
            Carbon::parse('2026-07-04'),
        );

        $this->assertSame(0, $units);
    }

    public function test_units_are_capped_by_the_busiest_night(): void
    {
        // Only one booking covers a single night of the stay.
        $units = $this->service()->availableUnits(
            3,
            $this->bookings([['2026-07-01', '2026-07-02']]),
            Carbon::parse('2026-07-01'),
            Carbon::parse('2026-07-04'),
        );

        $this->assertSame(2, $units);
    }

    public function test_checkout_day_is_not_counted_as_occupied(): void
    {
        // Booking ends the morning our stay begins -> no overlap.
        $units = $this->service()->availableUnits(
            1,
            $this->bookings([['2026-06-29', '2026-07-01']]),
            Carbon::parse('2026-07-01'),
            Carbon::parse('2026-07-03'),
        );

        $this->assertSame(1, $units);
    }

    public function test_availability_never_goes_negative(): void
    {
        $units = $this->service()->availableUnits(
            1,
            $this->bookings([['2026-07-01', '2026-07-04'], ['2026-07-01', '2026-07-04']]),
            Carbon::parse('2026-07-01'),
            Carbon::parse('2026-07-04'),
        );

        $this->assertSame(0, $units);
    }
}
