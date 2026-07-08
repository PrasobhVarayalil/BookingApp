<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Hotel;
use App\Models\Room;
use App\Repositories\Contracts\BookingRepositoryInterface;
use App\Repositories\Contracts\HotelRepositoryInterface;
use App\Services\Search\SearchResultCache;
use Illuminate\Support\Carbon;

class SearchService
{
    public function __construct(
        private readonly HotelRepositoryInterface $hotels,
        private readonly BookingRepositoryInterface $bookings,
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

        $roomIds = $hotels->flatMap(fn (Hotel $hotel) => $hotel->rooms->modelKeys())->all();
        $bookingsByRoom = $this->bookings->overlappingForRooms($roomIds, $checkin->toDateString(), $checkout->toDateString());

        $results = $hotels
            ->map(function (Hotel $hotel) use ($bookingsByRoom, $checkin, $checkout, $nights): ?array {
                $rooms = $hotel->rooms
                    ->map(function (Room $room) use ($bookingsByRoom, $checkin, $checkout, $nights): ?array {
                        $units = $this->bookingService->availableUnits(
                            $room->total_rooms,
                            $bookingsByRoom->get($room->id, collect()),
                            $checkin,
                            $checkout,
                        );

                        if ($units < 1) {
                            return null;
                        }

                        return [
                            'id' => $room->id,
                            'name' => $room->name,
                            'price_per_night' => (float) $room->price_per_night,
                            'max_occupancy' => $room->max_occupancy,
                            'available_units' => $units,
                            'total_price' => round((float) $room->price_per_night * $nights, 2),
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
