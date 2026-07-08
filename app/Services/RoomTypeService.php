<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ResourceInUseException;
use App\Models\RoomType;
use App\Repositories\Contracts\RoomTypeRepositoryInterface;
use App\Repositories\Contracts\RoomUnitRepositoryInterface;
use App\Services\Search\SearchResultCache;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class RoomTypeService
{
    public function __construct(
        private readonly RoomTypeRepositoryInterface $roomTypes,
        private readonly RoomUnitRepositoryInterface $roomUnits,
        private readonly SearchResultCache $searchCache,
    ) {}

    /**
     * @param  array<string, mixed>  $attributes
     * @param  list<string>  $roomNumbers
     */
    public function create(array $attributes, array $roomNumbers): RoomType
    {
        $roomType = $this->roomTypes->create($attributes);
        $this->roomUnits->createManyForType($roomType->id, $roomNumbers);

        $this->searchCache->bump();

        return $roomType->load('units');
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(RoomType $roomType, array $attributes): RoomType
    {
        $roomType = $this->roomTypes->update($roomType, $attributes);

        $this->searchCache->bump();

        return $roomType;
    }

    /**
     * @throws ResourceInUseException
     */
    public function delete(RoomType $roomType): void
    {
        $bookings = $roomType->bookings()->count();

        if ($bookings > 0) {
            throw ResourceInUseException::roomTypeHasBookings($bookings);
        }

        $this->roomTypes->delete($roomType);

        $this->searchCache->bump();
    }

    /**
     * @return LengthAwarePaginator<int, RoomType>
     */
    public function paginateWithHotel(int $perPage = 15, ?string $hotelId = null, ?string $search = null): LengthAwarePaginator
    {
        return $this->roomTypes->paginateWithHotel($perPage, $hotelId, $search);
    }

    /**
     * @return Collection<int, RoomType>
     */
    public function allWithHotel(): Collection
    {
        return $this->roomTypes->allWithHotel();
    }

    public function count(): int
    {
        return $this->roomTypes->count();
    }
}
