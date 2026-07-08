<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ResourceInUseException;
use App\Models\Hotel;
use App\Repositories\Contracts\HotelRepositoryInterface;

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
        $types = $hotel->roomTypes()->count();

        if ($types > 0) {
            throw ResourceInUseException::hotelHasRoomTypes($types);
        }

        $this->hotels->delete($hotel);
    }

    /**
     * @param  array{country?: string|null, city?: string|null, rating?: int|null}  $filters
     */
    public function paginate(array $filters, int $perPage = 15)
    {
        return $this->hotels->paginate($filters, $perPage);
    }

    public function all()
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
