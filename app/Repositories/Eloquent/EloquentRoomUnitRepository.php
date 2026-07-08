<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Booking;
use App\Models\RoomUnit;
use App\Repositories\Contracts\RoomUnitRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentRoomUnitRepository implements RoomUnitRepositoryInterface
{
    public function create(array $attributes): RoomUnit
    {
        return RoomUnit::create($attributes);
    }

    public function findForUpdate(string $id): ?RoomUnit
    {
        return RoomUnit::whereKey($id)->lockForUpdate()->first();
    }

    public function createManyForType(string $roomTypeId, array $roomNumbers): Collection
    {
        $units = collect();

        foreach ($roomNumbers as $number) {
            $units->push($this->create([
                'room_type_id' => $roomTypeId,
                'room_number' => $number,
                'status' => RoomUnit::STATUS_AVAILABLE,
            ]));
        }

        return $units;
    }

    public function availableForStay(string $roomTypeId, string $checkin, string $checkout): Collection
    {
        $bookedIds = Booking::query()
            ->where('room_type_id', $roomTypeId)
            ->where('status', Booking::STATUS_CONFIRMED)
            ->whereNotNull('room_unit_id')
            ->whereDate('checkin_date', '<', $checkout)
            ->whereDate('checkout_date', '>', $checkin)
            ->pluck('room_unit_id');

        return RoomUnit::query()
            ->where('room_type_id', $roomTypeId)
            ->where('status', RoomUnit::STATUS_AVAILABLE)
            ->whereNotIn('id', $bookedIds)
            ->orderBy('room_number')
            ->get();
    }

    public function count(): int
    {
        return RoomUnit::count();
    }
}
