<?php

declare(strict_types=1);

namespace App\Services\Search;

use Closure;
use Illuminate\Support\Facades\Cache;

/**
 * Short-lived cache for availability search results.
 *
 * Rather than cache tags (unsupported on the file/array drivers), invalidation
 * uses a version counter folded into the key. Booking writes call bump(), which
 * increments the version and leaves every earlier entry unreachable — they age
 * out on their own TTL.
 */
class SearchResultCache
{
    private const VERSION_KEY = 'search:version';

    private const TTL_SECONDS = 60;

    /**
     * @param  Closure(): array<string, mixed>  $resolver
     * @return array<string, mixed>
     */
    public function remember(string $fingerprint, Closure $resolver): array
    {
        return Cache::remember(
            sprintf('search:%d:%s', $this->version(), $fingerprint),
            self::TTL_SECONDS,
            $resolver,
        );
    }

    public function bump(): void
    {
        Cache::add(self::VERSION_KEY, 1);
        Cache::increment(self::VERSION_KEY);
    }

    private function version(): int
    {
        return (int) Cache::get(self::VERSION_KEY, 1);
    }
}
