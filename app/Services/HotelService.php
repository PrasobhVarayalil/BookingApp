<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ResourceInUseException;
use App\Models\Hotel;
use App\Repositories\Contracts\HotelRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class HotelService
{
    public function __construct(
        private readonly HotelRepositoryInterface $hotels,
    ) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Hotel
    {
        return $this->hotels->create($attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Hotel $hotel, array $attributes): Hotel
    {
        return $this->hotels->update($hotel, $attributes);
    }

    /**
     * @throws ResourceInUseException
     */
    public function delete(Hotel $hotel): void
    {
        $rooms = $hotel->rooms()->count();

        if ($rooms > 0) {
            throw ResourceInUseException::hotelHasRooms($rooms);
        }

        $this->hotels->delete($hotel);
    }

    /**
     * @param  array{city?: string|null, rating?: int|null}  $filters
     * @return LengthAwarePaginator<int, Hotel>
     */
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->hotels->paginate($filters, $perPage);
    }

    /**
     * @return Collection<int, Hotel>
     */
    public function all(): Collection
    {
        return $this->hotels->all();
    }

    public function count(): int
    {
        return $this->hotels->count();
    }

    public function averageRating(): float
    {
        return $this->hotels->averageRating();
    }
}
