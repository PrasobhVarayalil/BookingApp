<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Hotel;
use App\Models\RoomType;
use App\Repositories\Contracts\HotelRepositoryInterface;
use App\Services\Search\SearchResultCache;
use Illuminate\Support\Carbon;

class SearchService
{
    public function __construct(
        private readonly HotelRepositoryInterface $hotels,
        private readonly BookingService $bookingService,
        private readonly SearchResultCache $cache,
    ) {}

    /**
     * @param  array{city: string, checkin_date: string, checkout_date: string, guests: int}  $params
     * @return array{results: list<array<string, mixed>>, meta: array<string, mixed>}
     */
    public function search(array $params): array
    {
        $city = trim($params['city']);
        $guests = (int) $params['guests'];
        $checkin = Carbon::parse($params['checkin_date'])->startOfDay();
        $checkout = Carbon::parse($params['checkout_date'])->startOfDay();

        $fingerprint = md5(implode('|', [mb_strtolower($city), $checkin->toDateString(), $checkout->toDateString(), $guests]));

        return $this->cache->remember(
            $fingerprint,
            fn () => $this->build($city, $guests, $checkin, $checkout),
        );
    }

    /**
     * @return array{results: list<array<string, mixed>>, meta: array<string, mixed>}
     */
    private function build(string $city, int $guests, Carbon $checkin, Carbon $checkout): array
    {
        $nights = (int) $checkin->diffInDays($checkout);
        $hotels = $this->hotels->availableInCity($city, $guests);

        $results = $hotels
            ->map(function (Hotel $hotel) use ($checkin, $checkout, $nights): ?array {
                $rooms = $hotel->roomTypes
                    ->map(function (RoomType $roomType) use ($checkin, $checkout, $nights): ?array {
                        $freeUnits = $this->bookingService->availableUnitsForStay($roomType, $checkin, $checkout);

                        if ($freeUnits->isEmpty()) {
                            return null;
                        }

                        return [
                            'id' => $roomType->id,
                            'name' => $roomType->name,
                            'price_per_night' => (float) $roomType->price_per_night,
                            'max_occupancy' => $roomType->max_occupancy,
                            'available_units' => $freeUnits->count(),
                            'available_room_numbers' => $freeUnits->pluck('room_number')->values()->all(),
                            'total_price' => round((float) $roomType->price_per_night * $nights, 2),
                        ];
                    })
                    ->filter()
                    ->values();

                if ($rooms->isEmpty()) {
                    return null;
                }

                return [
                    'hotel' => $hotel->only('id', 'name', 'city', 'country', 'rating'),
                    'nights' => $nights,
                    'rooms' => $rooms->all(),
                ];
            })
            ->filter()
            ->values()
            ->all();

        return [
            'results' => $results,
            'meta' => [
                'city' => $city,
                'checkin_date' => $checkin->toDateString(),
                'checkout_date' => $checkout->toDateString(),
                'guests' => $guests,
                'nights' => $nights,
            ],
        ];
    }
}
