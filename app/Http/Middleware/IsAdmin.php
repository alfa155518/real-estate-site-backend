<?php

namespace App\Http\Middleware;

use App\Traits\HandleResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Laravel\Sanctum\PersonalAccessToken;


class IsAdmin
{
    use HandleResponse;
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $bearerToken = $request->header('Authorization');

        // Extract token from Bearer string
        if (!$bearerToken || !str_starts_with($bearerToken, 'Bearer ')) {
            return $this->error('يجب تسجيل الدخول اولا', 401);
        }

        $token = str_replace('Bearer ', '', $bearerToken);
        $tokenModel = PersonalAccessToken::findToken($token);


        $user = $tokenModel->tokenable;

        // Check if user exists and has admin role
        if (!$user || $user->role !== 'admin') {
            return $this->error('يجب ان تكون ادمن', 403);
        }

        return $next($request);
    }
}
