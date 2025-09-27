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

        $user_id = PersonalAccessToken::findToken($token)->tokenable_id;

        if (!$user_id) {
            return $this->error('aيجب تسجيل الدخول اولا', 401);
        }
        $user = User::find($user_id);
        if (!$user) {
            return $this->notFound('المستخدم غير موجود', 404);
        }
        return $user;
    }
}
