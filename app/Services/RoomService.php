<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ResourceInUseException;
use App\Models\Room;
use App\Repositories\Contracts\RoomRepositoryInterface;
use App\Services\Search\SearchResultCache;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class RoomService
{
    public function __construct(
        private readonly RoomRepositoryInterface $rooms,
        private readonly SearchResultCache $searchCache,
    ) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Room
    {
        $room = $this->rooms->create($attributes);

        // A new room type changes what search can return for its city.
        $this->searchCache->bump();

        return $room;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Room $room, array $attributes): Room
    {
        $room = $this->rooms->update($room, $attributes);

        $this->searchCache->bump();

        return $room;
    }

    /**
     * @throws ResourceInUseException
     */
    public function delete(Room $room): void
    {
        $bookings = $room->bookings()->count();

        if ($bookings > 0) {
            throw ResourceInUseException::roomHasBookings($bookings);
        }

        $this->rooms->delete($room);

        $this->searchCache->bump();
    }

    /**
     * @return LengthAwarePaginator<int, Room>
     */
    public function paginateWithHotel(int $perPage = 15, ?string $hotelId = null, ?string $search = null): LengthAwarePaginator
    {
        return $this->rooms->paginateWithHotel($perPage, $hotelId, $search);
    }

    /**
     * @return Collection<int, Room>
     */
    public function allWithHotel(): Collection
    {
        return $this->rooms->allWithHotel();
    }

    public function count(): int
    {
        return $this->rooms->count();
    }
}
