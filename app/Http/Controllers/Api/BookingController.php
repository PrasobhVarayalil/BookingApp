<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Resources\BookingResource;
use App\Http\Resources\RoomUnitResource;
use App\Models\Booking;
use App\Models\RoomType;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BookingController extends Controller
{
    public function __construct(
        private readonly BookingService $bookings,
    ) {}

    public function index(): JsonResponse
    {
        $bookings = $this->bookings->paginate(15);

        return BookingResource::collection($bookings)->response();
    }

    public function store(StoreBookingRequest $request): JsonResponse
    {
        $booking = $this->bookings->create($request->bookingData());

        return BookingResource::make($booking)->response()->setStatusCode(Response::HTTP_CREATED);
    }

    public function destroy(Booking $booking): JsonResponse
    {
        $this->bookings->cancel($booking);

        return $this->noContent();
    }

    public function availableUnits(Request $request, RoomType $roomType): JsonResponse
    {
        $request->validate([
            'checkin_date' => ['required', 'date', 'after_or_equal:today'],
            'checkout_date' => ['required', 'date', 'after:checkin_date'],
        ]);

        $units = $this->bookings->listAvailableUnits(
            $roomType->id,
            (string) $request->date('checkin_date')?->toDateString(),
            (string) $request->date('checkout_date')?->toDateString(),
        );

        return RoomUnitResource::collection($units)->response();
    }
}
