<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Resources\BookingResource;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class BookingController extends Controller
{
    public function __construct(
        private readonly BookingService $bookings,
    ) {}

    public function store(StoreBookingRequest $request): JsonResponse
    {
        // RoomNotAvailableException maps to 422 via ApiExceptionRenderer.
        $booking = $this->bookings->create($request->bookingData());

        return BookingResource::make($booking)->response()->setStatusCode(Response::HTTP_CREATED);
    }
}
