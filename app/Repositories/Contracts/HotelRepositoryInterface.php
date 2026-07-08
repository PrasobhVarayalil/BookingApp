<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Hotel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface HotelRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Hotel;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Hotel $hotel, array $attributes): Hotel;

    public function delete(Hotel $hotel): void;

    /**
     * @param  array{city?: string|null, rating?: int|null}  $filters
     * @return LengthAwarePaginator<int, Hotel>
     */
    public function paginate(array $filters, int $perPage): LengthAwarePaginator;

    /**
     * @return Collection<int, Hotel>
     */
    public function all(): Collection;

    /**
     * Hotels in a city with their rooms (filtered to the requested occupancy)
     * eager-loaded, for the availability search.
     *
     * @return Collection<int, Hotel>
     */
    public function availableInCity(string $city, int $guests): Collection;

    public function count(): int;

    public function averageRating(): float;
}
