<?php

namespace App\Traits;

use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;
use App\Traits\GetUserToken;
use App\Traits\HandleResponse;
use Illuminate\Http\Request;

trait AuthenticatedUser
{
    use GetUserToken, HandleResponse;
    public function AuthenticatedUser(Request $request)
    {
        $token = $this->getUserToken($request);

        // Check if getUserToken returned an error response
        if ($token instanceof \Illuminate\Http\JsonResponse) {
            return $token;
        }

        // Find the token in the database
        $personalAccessToken = PersonalAccessToken::findToken($token);

        if (!$personalAccessToken) {
            return $this->error('يجب تسجيل الدخول اولا', 401);
        }

        $user_id = $personalAccessToken->tokenable_id;

        if (!$user_id) {
            return $this->error('يجب تسجيل الدخول اولا', 401);
        }

        $user = User::find($user_id);

        if (!$user) {
            return $this->notFound('المستخدم غير موجود', 404);
        }

        return $user;
    }
}
