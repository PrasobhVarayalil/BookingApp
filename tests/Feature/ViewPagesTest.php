<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesRoomInventory;
use Tests\TestCase;

class ViewPagesTest extends TestCase
{
    use CreatesRoomInventory;
    use RefreshDatabase;

    public function test_hotel_detail_page_renders(): void
    {
        $roomType = $this->roomTypeWithUnits(2);

        $this->actingAs(User::factory()->create())
            ->get(route('hotels.show', $roomType->hotel))
            ->assertOk()
            ->assertSee($roomType->hotel->name)
            ->assertSee($roomType->name);
    }

    public function test_room_type_detail_page_renders(): void
    {
        $roomType = $this->roomTypeWithUnits(2);

        $this->actingAs(User::factory()->create())
            ->get(route('rooms.show', $roomType))
            ->assertOk()
            ->assertSee($roomType->name)
            ->assertSee($roomType->units->first()->room_number);
    }

    public function test_booking_detail_page_renders(): void
    {
        $roomType = $this->roomTypeWithUnits(1);
        $booking = Booking::factory()->forType($roomType, $roomType->units->first())->create();

        $this->actingAs(User::factory()->create())
            ->get(route('bookings.show', $booking))
            ->assertOk()
            ->assertSee($booking->booking_reference)
            ->assertSee($booking->guest_name);
    }

    public function test_activity_log_page_renders(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('activity.index'))
            ->assertOk()
            ->assertSee('Activity log');
    }

    public function test_dashboard_renders_with_chart_data(): void
    {
        $roomType = $this->roomTypeWithUnits(1);
        Booking::factory()->forType($roomType, $roomType->units->first())->create();

        $this->actingAs(User::factory()->create())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Bookings &amp; revenue', false)
            ->assertSee('Booking status');
    }

    public function test_view_pages_require_auth(): void
    {
        $roomType = $this->roomTypeWithUnits(1);

        $this->get(route('hotels.show', $roomType->hotel))->assertRedirect(route('login'));
        $this->get(route('activity.index'))->assertRedirect(route('login'));
    }
}
