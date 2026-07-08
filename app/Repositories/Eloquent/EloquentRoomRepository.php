<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Room;
use App\Repositories\Contracts\RoomRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class EloquentRoomRepository implements RoomRepositoryInterface
{
    public function create(array $attributes): Room
    {
        return Room::create($attributes);
    }

    public function update(Room $room, array $attributes): Room
    {
        $room->update($attributes);

        return $room;
    }

    public function delete(Room $room): void
    {
        $room->delete();
    }

    public function findForUpdate(string $id): ?Room
    {
        return Room::whereKey($id)->lockForUpdate()->first();
    }

    public function paginateWithHotel(int $perPage, ?string $hotelId = null, ?string $search = null): LengthAwarePaginator
    {
        return Room::query()
            ->with('hotel')
            ->when(filled($hotelId), fn (Builder $q) => $q->where('hotel_id', $hotelId))
            ->when(filled($search), function (Builder $q) use ($search): void {
                $like = '%'.$search.'%';
                $q->where(fn (Builder $inner) => $inner
                    ->where('name', 'like', $like)
                    ->orWhereHas('hotel', fn (Builder $hotel) => $hotel->where('name', 'like', $like)));
            })
            ->latest()
            ->paginate($perPage);
    }

    public function allWithHotel(): Collection
    {
        return Room::with('hotel')->orderBy('name')->get();
    }

    public function count(): int
    {
        return Room::count();
    }
}
