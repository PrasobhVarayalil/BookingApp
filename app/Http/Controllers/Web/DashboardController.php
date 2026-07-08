<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\BookingService;
use App\Services\DashboardService;
use App\Services\HotelService;
use App\Services\RoomTypeService;
use App\Services\RoomUnitService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly HotelService $hotels,
        private readonly RoomTypeService $roomTypes,
        private readonly RoomUnitService $roomUnits,
        private readonly BookingService $bookings,
        private readonly DashboardService $dashboard,
    ) {}

    public function index(): View
    {
        $units = $this->roomUnits->count();

        return view('dashboard.index', [
            'stats' => [
                'hotels' => $this->hotels->count(),
                'room_types' => $this->roomTypes->count(),
                'room_units' => $units,
                'bookings' => $this->bookings->count(),
                'average_rating' => $this->hotels->averageRating(),
                'occupancy' => $this->dashboard->occupancyRate($units),
            ],
            'charts' => $this->dashboard->charts(),
        ]);
    }
}
