<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Exceptions\Contracts\HasHttpStatus;
use Illuminate\Http\Response;
use RuntimeException;

/**
 * Raised when a record still has dependents and therefore cannot be deleted.
 * The message is safe to show to the user.
 */
class ResourceInUseException extends RuntimeException implements HasHttpStatus
{
    public static function hotelHasRooms(int $count): self
    {
        return new self(self::phrase('hotel', 'room', $count, 'deleting the hotel'));
    }

    public static function roomHasBookings(int $count): self
    {
        return new self(self::phrase('room', 'booking', $count, 'deleting the room'));
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
