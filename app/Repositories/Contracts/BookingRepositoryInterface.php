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

    public function delete(Booking $booking): void;

    public function count(): int;

    /**
     * @return LengthAwarePaginator<int, Booking>
     */
    public function paginateWithRoomAndHotel(int $perPage): LengthAwarePaginator;

    /**
     * Confirmed bookings overlapping [checkin, checkout) for many rooms,
     * grouped by room_id.
     *
     * @param  list<string>  $roomIds
     * @return Collection<string, Collection<int, Booking>>
     */
    public function overlappingForRooms(array $roomIds, string $checkin, string $checkout): Collection;

    /**
     * Confirmed bookings overlapping [checkin, checkout) for a single room.
     * With $lock the rows are read for update inside a transaction.
     *
     * @return Collection<int, Booking>
     */
    public function overlappingForRoom(string $roomId, string $checkin, string $checkout, bool $lock = false): Collection;
}
