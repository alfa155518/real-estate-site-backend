<?php

namespace App\Http\Middleware;

use App\Traits\HandleResponse;
use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class IsAuthorizedMiddleware
{
    use HandleResponse;
    /**
     * Handle an incoming request.
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $bearerToken = $request->header('Authorization');

        // Extract token from Bearer string
        if (!$bearerToken || !str_starts_with($bearerToken, 'Bearer ')) {
            return $this->error('يجب تسجيل الدخول اولا', 401);
        }

        $token = str_replace('Bearer ', '', $bearerToken);

        // Find token in database
        $tokenModel = PersonalAccessToken::findToken($token);

        //check if token in database
        if (!$tokenModel) {
            return $this->error("يجب تسجيل الدخول اولا", 401);
        }
        return $next($request);
    }
}
