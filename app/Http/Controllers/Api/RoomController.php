<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoomRequest;
use App\Http\Resources\RoomResource;
use App\Services\RoomService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class RoomController extends Controller
{
    public function __construct(
        private readonly RoomService $rooms,
    ) {}

    public function store(StoreRoomRequest $request): JsonResponse
    {
        $room = $this->rooms->create($request->validated());

        return RoomResource::make($room->load('hotel'))->response()->setStatusCode(Response::HTTP_CREATED);
    }
}
