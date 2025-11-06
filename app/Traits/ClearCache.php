<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;


trait ClearCache
{
    /**
     * Apply rate limiting to an API request.
     *
     * @param string $key Unique key (usually user id or IP).
     */
    public function clearCache(string $key): void
    {
        Cache::forget($key);
    }

    /**
     * Clear multiple cached  pages
     *
     * @return void
     */
    public static function clearMultipleCachePages(string $key, int $maxPages): void
    {
        for ($page = 1; $page <= $maxPages; $page++) {
            Cache::forget("$key-{$page}");
        }
    }
}
