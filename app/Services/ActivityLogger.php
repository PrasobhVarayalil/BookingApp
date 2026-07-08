<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Contracts\Auth\Authenticatable;

class ActivityLogger
{
    /**
     * @param  array<string, mixed>  $properties
     */
    public function log(string $description, string $logName = 'auth', ?Authenticatable $causer = null, array $properties = []): void
    {
        $activity = activity($logName)->withProperties($properties);

        if ($causer !== null) {
            $activity->causedBy($causer);
        }

        $activity->log($description);
    }
}
