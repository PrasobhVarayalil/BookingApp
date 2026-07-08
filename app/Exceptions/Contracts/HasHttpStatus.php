<?php

declare(strict_types=1);

namespace App\Exceptions\Contracts;

/**
 * A domain exception that knows which HTTP status it should map to, so the
 * status lives with the exception instead of being re-decided at each catch.
 */
interface HasHttpStatus
{
    public function httpStatus(): int;
}
