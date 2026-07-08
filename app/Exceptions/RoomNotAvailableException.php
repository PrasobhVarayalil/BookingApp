<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Exceptions\Contracts\HasHttpStatus;
use Illuminate\Http\Response;
use RuntimeException;

class RoomNotAvailableException extends RuntimeException implements HasHttpStatus
{
    public static function forDates(string $checkin, string $checkout): self
    {
        return new self("No rooms are available for {$checkin} to {$checkout}.");
    }

    public function httpStatus(): int
    {
        return Response::HTTP_UNPROCESSABLE_ENTITY;
    }
}
