<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\RoomUnit;
use Illuminate\Support\Collection;

interface RoomUnitRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): RoomUnit;

    public function findForUpdate(string $id): ?RoomUnit;

    /**
     * @param  list<string>  $roomNumbers
     * @return Collection<int, RoomUnit>
     */
    public function createManyForType(string $roomTypeId, array $roomNumbers): Collection;

    /**
     * Units of a type that are assignable for the full stay window.
     *
     * @return Collection<int, RoomUnit>
     */
    public function availableForStay(string $roomTypeId, string $checkin, string $checkout): Collection;

    public function count(): int;
}
