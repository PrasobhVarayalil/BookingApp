<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHotelRequest;
use App\Http\Resources\HotelResource;
use App\Services\HotelService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class HotelController extends Controller
{
    public function __construct(
        private readonly HotelService $hotels,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = $request->integer('per_page', 15);

        return HotelResource::collection($this->hotels->paginate(
            ['city' => $request->query('city'), 'rating' => $request->query('rating')],
            min(max($perPage, 1), 50),
        ));
    }

    public function store(StoreHotelRequest $request): JsonResponse
    {
        $hotel = $this->hotels->create($request->validated());

        return HotelResource::make($hotel)->response()->setStatusCode(Response::HTTP_CREATED);
    }
}
