<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoomTypeRequest;
use App\Http\Resources\RoomTypeResource;
use App\Services\RoomTypeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class RoomController extends Controller
{
    public function __construct(
        private readonly RoomTypeService $roomTypes,
    ) {}

    public function store(StoreRoomTypeRequest $request): JsonResponse
    {
        $numbers = $request->unitNumbers();

        if ($numbers === []) {
            return $this->error('At least one room number is required.', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $roomType = $this->roomTypes->create($request->typeAttributes(), $numbers);

        return RoomTypeResource::make($roomType->load('hotel', 'units'))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
}
