<?php

namespace App\Traits;

use App\Traits\HandleResponse;
use Illuminate\Http\Request;

trait GetUserToken
{
    use HandleResponse;
    public function getUserToken(Request $request)
    {
        $bearerToken = $request->header('Authorization');

        if (!$bearerToken || !str_starts_with($bearerToken, 'Bearer ')) {
            return $this->error('يجب تسجيل الدخول اولا', 401);
        }

        $token = str_replace('Bearer ', '', $bearerToken);

        return $token;
    }
}
