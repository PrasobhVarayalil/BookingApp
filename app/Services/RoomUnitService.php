<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\Contracts\RoomUnitRepositoryInterface;

class RoomUnitService
{
    public function __construct(
        private readonly RoomUnitRepositoryInterface $roomUnits,
    ) {}

    public function count(): int
    {
        return $this->roomUnits->count();
    }
}
