<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Exceptions\RoomNotAvailableException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Services\BookingService;
use App\Services\RoomService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BookingController extends Controller
{
    private const PER_PAGE = 10;

    public function __construct(
        private readonly BookingService $bookings,
        private readonly RoomService $rooms,
    ) {}

    public function index(): View
    {
        return view('bookings.index', [
            'bookings' => $this->bookings->paginate(self::PER_PAGE),
            'rooms' => $this->rooms->allWithHotel(),
        ]);
    }

    public function store(StoreBookingRequest $request): RedirectResponse
    {
        try {
            $this->bookings->create($request->bookingData());
        } catch (RoomNotAvailableException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('bookings.index')->with('success', 'Booking confirmed.');
    }

    public function destroy(Booking $booking): RedirectResponse
    {
        $this->bookings->delete($booking);

        return redirect()->route('bookings.index')->with('success', 'Booking cancelled.');
    }
}
