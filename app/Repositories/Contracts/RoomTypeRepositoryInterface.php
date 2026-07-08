<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\RoomType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface RoomTypeRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): RoomType;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(RoomType $roomType, array $attributes): RoomType;

    public function delete(RoomType $roomType): void;

    public function findForUpdate(string $id): ?RoomType;

    /**
     * @return LengthAwarePaginator<int, RoomType>
     */
    public function paginateWithHotel(int $perPage, ?string $hotelId = null, ?string $search = null): LengthAwarePaginator;

    /**
     * @return Collection<int, RoomType>
     */
    public function allWithHotel(): Collection;

    public function count(): int;
}
