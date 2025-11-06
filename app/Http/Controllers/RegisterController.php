<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\ClearCache;
use App\Traits\HandleResponse;
use App\Traits\RateLimitable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;


class RegisterController extends Controller
{
    use HandleResponse, RateLimitable, ClearCache;

    /**
     ** Register a new user.
     * @param Request $request
     * Rate limit by IP
     */
    public function signup(Request $request)
    {

        // Rate limit by IP
        if ($response = $this->rateLimit('signup:' . $request->ip(), 5, 60)) {
            return $response;
        }

        DB::beginTransaction();

        $validator = Validator::make($request->all(), User::signupRules(), User::signupMessages());

        if ($validator->fails()) {
            return $this->error($validator->errors()->first(), 422);
        }

        $validatedData = $validator->validated();

        try {
            $user = User::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
                'confirm_password' => Hash::make($validatedData['confirm_password']),
                'phone' => $validatedData['phone'],
            ]);
            // Generate token for the newly created user
            $token = $user->createToken('auth_token')->plainTextToken;
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'تم التسجيل بنجاح',
                'access_token' => $token,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error("حدث خطأ في السيرفر", 500);
        }
    }

    /**
     ** Login a user.
     * @param Request $request
     * Rate limit by IP
     */
    public function login(Request $request)
    {
        // Rate limit by IP
        if ($response = $this->rateLimit('login:' . $request->ip(), 5, 60)) {
            return $response;
        }
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return $this->notFound('المستخدم غير موجود', 404);
        }

        if (!Hash::check($request->password, $user->password)) {
            return $this->error('كلمة المرور غير صحيحة', 401);
        }
        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), User::loginRules(), User::loginMessages());

            if ($validator->fails()) {
                return $this->error($validator->errors()->first(), 422);
            }

            if ($user->tokens->count() > 0) {
                $user->tokens()->delete();
            }

            // Create a new token
            $token = $user->createToken('auth_token')->plainTextToken;
            $this->clearMultipleCachePages("admin_management_users", 200);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'تم تسجيل الدخول بنجاح',
                'access_token' => $token,
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error("حدث خطأ في السيرفر", 500);
        }
    }
}