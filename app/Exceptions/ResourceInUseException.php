<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Exceptions\Contracts\HasHttpStatus;
use Illuminate\Http\Response;
use RuntimeException;

class ResourceInUseException extends RuntimeException implements HasHttpStatus
{
    public static function hotelHasRoomTypes(int $count): self
    {
        return new self(self::phrase('hotel', 'room type', $count, 'deleting the hotel'));
    }

    public static function roomTypeHasBookings(int $count): self
    {
        return new self(self::phrase('room type', 'booking', $count, 'deleting the room type'));
    }

    public function httpStatus(): int
    {
        return Response::HTTP_CONFLICT;
    }

    private static function phrase(string $owner, string $child, int $count, string $action): string
    {
        $plural = $count === 1 ? $child : "{$child}s";

        return "This {$owner} still has {$count} {$plural}. Remove them before {$action}.";
    }
}
