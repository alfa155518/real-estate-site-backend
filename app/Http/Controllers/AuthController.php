<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Traits\HandleResponse;
use App\Traits\RateLimitable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    use HandleResponse, RateLimitable;

    /**
     * Login a user with Google.
     * @param Request $request
     * Rate limit by IP
     */
    public function googleRedirect(Request $request)
    {
        // Rate limit by IP
        if ($response = $this->rateLimit('signup:' . $request->ip(), 5, 120)) {
            return $response;
        }
        try {
            $url = Socialite::driver('google')->stateless()->redirect()->getTargetUrl();
            return response()->json(['url' => $url]);
        } catch (\Exception $e) {
            return $this->error("حدث خطأ في السيرفر", 500);
        }
    }

    /**
     * Handle the callback from Google.
     * @param Request $request
     * Rate limit by IP
     */
    public function googleCallback(Request $request)
    {
        // Rate limit by IP
        if ($response = $this->rateLimit('signup:' . $request->ip(), 5, 120)) {
            return $response;
        }
        // Check if the user denied the authorization request
        if ($request->has('error') || $request->has('error_description')) {
            return redirect()->to(env('FRONTEND_URL') . '/signup?error=google_auth_cancelled');
        }

        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            $user = User::where('email', $googleUser->getEmail())->first();

            if (!$user) {
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'email_verified_at' => now(),
                ]);
            } else {
                $user->update([
                    'google_id' => $googleUser->getId(),
                ]);
            }

            if ($user->tokens->count() > 0) {
                $user->tokens()->delete();
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            $userResponse = $user->makeHidden(['address', 'tokens']);

            // Store user data and token in cookies
            $userData = json_encode($userResponse);

            // Create redirect response with cookies
            return redirect()->to(env('FRONTEND_URL'))
                ->cookie('userToken', $token, 525600, null, null, false, false) // 1 year (525600 minutes), HTTP only false to allow JS access
                ->cookie('user', $userData, 525600, null, null, false, false);
        } catch (\Exception $e) {
            // Redirect to signup with error message
            return redirect()->to(env('FRONTEND_URL') . '/signup?error=google_auth_failed');
        }
    }


    public function forgotPassword(Request $request)
    {
        // Rate limit by IP
        if ($response = $this->rateLimit('forgot-password:' . $request->ip(), 5, 120)) {
            return $response;
        }
        try {
            DB::beginTransaction();
            $validator = Validator::make($request->all(), User::forgotPasswordRules(), User::forgotPasswordMessages());

            if ($validator->fails()) {
                return $this->error($validator->errors()->first(), 422);
            }

            $validatedData = $validator->validated();

            $user = User::where('email', $validatedData['email'])->first();

            if (!$user) {
                return $this->error('المستخدم غير موجود', 404);
            }

            // create reset token
            $token = Str::random(60);

            // save reset token
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $validatedData['email']],
                [
                    'email' => $validatedData['email'],
                    'token' => Hash::make($token),
                    'created_at' => now(),
                ]
            );

            // create reset link
            $resetLink = env('FRONTEND_URL') . "/reset-password?token=$token&email=$request->email";

            // send email
            Mail::send('emails.resetPassword', ['resetLink' => $resetLink], function ($message) use ($validatedData) {
                $message->to($validatedData['email'])
                    ->subject('إعادة تعيين كلمة المرور');
            });

            DB::commit();
            return $this->success('تم إرسال رابط إعادة تعيين كلمة المرور إلى بريدك الإلكتروني.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error("حدث خطأ في السيرفر", 500);
        }
    }

    public function resetPassword(Request $request)
    {
        // Rate limit by IP
        if ($response = $this->rateLimit('reset-password:' . $request->ip(), 5, 120)) {
            return $response;
        }
        try {
            // First check if required fields exist and are not 'undefined'
            if (
                !$request->has('token') || !$request->has('email') ||
                $request->input('token') === 'undefined' || $request->input('email') === 'undefined'
            ) {
                return $this->error('يجب ادخال البريدالالكروني لاعادة تعيين كلمة المرور', 400);
            }

            // Get the raw input data
            $input = $request->only(['token', 'email', 'password', 'confirm_password']);

            // Validate the input
            $validator = Validator::make(
                $input,
                User::resetPasswordRules(),
                User::resetPasswordMessages()
            );

            if ($validator->fails()) {
                return $this->error($validator->errors()->first(), 422);
            }

            // If validation passes, get the validated data
            $validatedData = $validator->validated();

            // Check the reset token
            $resetRecord = DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->first();

            if (!$resetRecord) {
                return $this->error('رمز إعادة تعيين كلمة المرور غير صالح', 400);
            }

            if (now()->diffInHours($resetRecord->created_at) > 1) {
                return $this->error('انتهت صلاحية رمز إعادة التعيين. يرجى طلب رمز جديد', 400);
            }

            // update password
            $user = User::where('email', $request->email)->first();
            if (!$user) {
                return response()->json(['message' => 'المستخدم غير موجود.'], 404);
            }
            $user->password = Hash::make($validatedData['password']);
            $user->confirm_password = Hash::make($validatedData['confirm_password']);
            $user->save();
            // delete reset token
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            DB::commit();

            return $this->success('تم إعادة تعيين كلمة المرور بنجاح.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error("حدث خطأ في السيرفر", 500);
        }
    }
}