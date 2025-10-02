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
}
