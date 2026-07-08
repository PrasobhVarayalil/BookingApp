<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Exceptions\RoomNotAvailableException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\RoomType;
use App\Services\BookingService;
use App\Services\RoomTypeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class BookingController extends Controller
{
    private const PER_PAGE = 10;

    public function __construct(
        private readonly BookingService $bookings,
        private readonly RoomTypeService $roomTypes,
    ) {}

    public function index(Request $request): View
    {
        return view('bookings.index', [
            'bookings' => $this->bookings->paginate(self::PER_PAGE),
            'roomTypes' => $this->roomTypes->allWithHotel(),
            'prefill' => [
                'room_type_id' => $request->query('room_type_id'),
                'checkin_date' => $request->query('checkin_date'),
                'checkout_date' => $request->query('checkout_date'),
                'guests' => $request->query('guests', 1),
            ],
        ]);
    }

    public function availableUnits(Request $request, RoomType $roomType): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'checkin_date' => ['required', 'date', 'after_or_equal:today'],
            'checkout_date' => ['required', 'date', 'after:checkin_date'],
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first(), 'data' => []], 422);
        }

        $units = $this->bookings->listAvailableUnits(
            $roomType->id,
            (string) $request->date('checkin_date')?->toDateString(),
            (string) $request->date('checkout_date')?->toDateString(),
        );

        return response()->json([
            'data' => $units->map(fn ($unit) => [
                'id' => $unit->id,
                'room_number' => $unit->room_number,
            ])->values(),
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
        $this->bookings->cancel($booking);

        return redirect()->route('bookings.index')->with('success', 'Booking cancelled.');
    }
}
