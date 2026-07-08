<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Booking;
use App\Repositories\Contracts\BookingRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class EloquentBookingRepository implements BookingRepositoryInterface
{
    public function create(array $attributes): Booking
    {
        return Booking::create($attributes);
    }

    public function delete(Booking $booking): void
    {
        $booking->delete();
    }

    public function count(): int
    {
        return Booking::count();
    }

    public function paginateWithRoomAndHotel(int $perPage): LengthAwarePaginator
    {
        return Booking::query()
            ->with('room.hotel')
            ->latest()
            ->paginate($perPage);
    }

    public function overlappingForRooms(array $roomIds, string $checkin, string $checkout): Collection
    {
        if ($roomIds === []) {
            return collect();
        }

        return $this->overlapping($checkin, $checkout)
            ->whereIn('room_id', $roomIds)
            ->get(['id', 'room_id', 'checkin_date', 'checkout_date'])
            ->groupBy('room_id');
    }

    public function overlappingForRoom(string $roomId, string $checkin, string $checkout, bool $lock = false): Collection
    {
        return $this->overlapping($checkin, $checkout)
            ->where('room_id', $roomId)
            ->when($lock, fn (Builder $q) => $q->lockForUpdate())
            ->get(['id', 'room_id', 'checkin_date', 'checkout_date']);
    }

    /**
     * Half-open overlap: a confirmed booking clashes with [checkin, checkout)
     * when it starts before our checkout and ends after our checkin. The night
     * of checkout is therefore free.
     *
     * @return Builder<Booking>
     */
    private function overlapping(string $checkin, string $checkout): Builder
    {
        return Booking::query()
            ->where('status', Booking::STATUS_CONFIRMED)
            ->whereDate('checkin_date', '<', $checkout)
            ->whereDate('checkout_date', '>', $checkin);
    }
}
