<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;
use Illuminate\Http\JsonResponse;

trait RateLimitable
{
    /**
     * Apply rate limiting to an API request.
     *
     * @param string $key Unique key (usually user id or IP).
     * @param int $maxAttempts Number of allowed attempts.
     * @param int $decaySeconds Time in seconds before reset.
     * @return JsonResponse|null
     */
    public function rateLimit(string $key, int $maxAttempts = 5, int $decaySeconds = 60): ?JsonResponse
    {
        $cacheKey = 'rate_limit:' . $key;
        $attempts = Cache::get($cacheKey, 0);

        if ($attempts >= $maxAttempts) {
            return response()->json([
                'status' => 'error',
                'message' => "حاول مرة أخرى بعد {$decaySeconds} ثانية."
            ], 429);
        }

        Cache::put($cacheKey, $attempts + 1, $decaySeconds);

        return null;
    }
}
