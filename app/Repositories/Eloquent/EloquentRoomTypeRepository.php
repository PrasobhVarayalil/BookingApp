<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\RoomType;
use App\Repositories\Contracts\RoomTypeRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class EloquentRoomTypeRepository implements RoomTypeRepositoryInterface
{
    public function create(array $attributes): RoomType
    {
        return RoomType::create($attributes);
    }

    public function update(RoomType $roomType, array $attributes): RoomType
    {
        $roomType->update($attributes);

        return $roomType;
    }

    public function delete(RoomType $roomType): void
    {
        $roomType->delete();
    }

    public function findForUpdate(string $id): ?RoomType
    {
        return RoomType::whereKey($id)->lockForUpdate()->first();
    }

    public function paginateWithHotel(int $perPage, ?string $hotelId = null, ?string $search = null): LengthAwarePaginator
    {
        return RoomType::query()
            ->with(['hotel', 'units'])
            ->withCount('units')
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
        return RoomType::with(['hotel', 'units'])->orderBy('name')->get();
    }

    public function count(): int
    {
        return RoomType::count();
    }
}
