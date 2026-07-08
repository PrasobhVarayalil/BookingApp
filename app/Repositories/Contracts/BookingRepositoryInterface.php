<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Booking;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface BookingRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Booking;

    public function cancel(Booking $booking): void;

    public function count(): int;

    /**
     * @return LengthAwarePaginator<int, Booking>
     */
    public function paginateWithRoomAndHotel(int $perPage): LengthAwarePaginator;

    /**
     * Confirmed bookings overlapping [checkin, checkout) for many room types,
     * grouped by room_type_id.
     *
     * @param  list<string>  $roomTypeIds
     * @return Collection<string, Collection<int, Booking>>
     */
    public function overlappingForRoomTypes(array $roomTypeIds, string $checkin, string $checkout): Collection;

    /**
     * Confirmed bookings overlapping [checkin, checkout) for a single unit.
     *
     * @return Collection<int, Booking>
     */
    public function overlappingForUnit(string $roomUnitId, string $checkin, string $checkout, bool $lock = false): Collection;

    /**
     * Unit ids that already have a confirmed overlap in the window.
     *
     * @return list<string>
     */
    public function overlappingUnitIdsForType(string $roomTypeId, string $checkin, string $checkout): array;
}
