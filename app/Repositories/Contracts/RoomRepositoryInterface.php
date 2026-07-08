<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Room;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface RoomRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Room;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Room $room, array $attributes): Room;

    public function delete(Room $room): void;

    /**
     * Load a room under a pessimistic lock, used inside the booking transaction
     * so two concurrent requests cannot overbook the same room.
     */
    public function findForUpdate(string $id): ?Room;

    /**
     * @return LengthAwarePaginator<int, Room>
     */
    public function paginateWithHotel(int $perPage, ?string $hotelId = null, ?string $search = null): LengthAwarePaginator;

    /**
     * @return Collection<int, Room>
     */
    public function allWithHotel(): Collection;

    public function count(): int;
}
