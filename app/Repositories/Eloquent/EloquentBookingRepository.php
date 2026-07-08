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

    public function cancel(Booking $booking): void
    {
        $booking->update(['status' => Booking::STATUS_CANCELLED]);
    }

    public function count(): int
    {
        return Booking::where('status', Booking::STATUS_CONFIRMED)->count();
    }

    public function paginateWithRoomAndHotel(int $perPage): LengthAwarePaginator
    {
        return Booking::query()
            ->with(['roomType.hotel', 'roomUnit'])
            ->latest()
            ->paginate($perPage);
    }

    public function overlappingForRoomTypes(array $roomTypeIds, string $checkin, string $checkout): Collection
    {
        if ($roomTypeIds === []) {
            return collect();
        }

        return $this->overlapping($checkin, $checkout)
            ->whereIn('room_type_id', $roomTypeIds)
            ->get(['id', 'room_type_id', 'room_unit_id', 'checkin_date', 'checkout_date'])
            ->groupBy('room_type_id');
    }

    public function overlappingForUnit(string $roomUnitId, string $checkin, string $checkout, bool $lock = false): Collection
    {
        return $this->overlapping($checkin, $checkout)
            ->where('room_unit_id', $roomUnitId)
            ->when($lock, fn (Builder $q) => $q->lockForUpdate())
            ->get(['id', 'room_type_id', 'room_unit_id', 'checkin_date', 'checkout_date']);
    }

    public function overlappingUnitIdsForType(string $roomTypeId, string $checkin, string $checkout): array
    {
        return $this->overlapping($checkin, $checkout)
            ->where('room_type_id', $roomTypeId)
            ->whereNotNull('room_unit_id')
            ->pluck('room_unit_id')
            ->unique()
            ->values()
            ->all();
    }

    /**
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
