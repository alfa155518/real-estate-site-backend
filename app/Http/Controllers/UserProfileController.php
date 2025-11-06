<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Profile;
use Illuminate\Http\Request;
use App\Traits\HandleResponse;
use App\Traits\AuthenticatedUser;
use App\Traits\ClearCache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\UserProfileResource;

class UserProfileController extends Controller
{
    use HandleResponse, AuthenticatedUser, ClearCache;
    public function profileInfo(Request $request)
    {
        try {
            $user = new UserProfileResource($this->AuthenticatedUser($request));
            return response()->json([
                'status' => "success",
                'data' => $user
            ]);
        } catch (\Exception $e) {
            return $this->error("حدث خطأ في السيرفر");
        }
    }

    public function updateProfileInfo(Request $request)
    {
        // Get authenticated user first to fail fast if not authenticated
        $user = $this->AuthenticatedUser($request);

        // Validate before starting transaction
        try {
            $validatedData = (new Profile)->updateProfileInfoValidation($request, $user);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error($e->validator->errors()->first());
        }

        DB::beginTransaction();
        try {
            // Update only changed fields
            $updates = [];
            $fields = ['name', 'email', 'phone', 'address'];

            foreach ($fields as $field) {
                if ($user->$field !== $validatedData[$field]) {
                    $updates[$field] = $validatedData[$field];
                }
            }

            if (!empty($updates)) {
                $user->update($updates);
            }
            $this->clearMultipleCachePages("admin_management_users", 200);
            DB::commit();
            return $this->success("تم التحديث بنجاح");
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error("حدث خطأ في السيرفر");
        }
    }



    public function updatePassword(Request $request)
    {
        try {
            DB::beginTransaction();
            // Get authenticated user
            $user = $this->AuthenticatedUser($request);

            $validatedData = (new Profile)->updatePasswordValidation($request);

            if (!Hash::check($validatedData['current_password'], $user->password)) {
                return $this->error("كلمة المرور الحالية غير صحيحة", 422);
            }

            $user->update([
                'password' => Hash::make($validatedData['password']),
                'confirm_password' => Hash::make($validatedData['confirm_password'])
            ]);


            DB::commit();
            return $this->success("تم التحديث بنجاح");
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return $this->error($e->validator->errors()->first());
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error("حدث خطأ في السيرفر");
        }
    }

    public function logout(Request $request)
    {
        try {
            DB::beginTransaction();
            // Get authenticated user
            $user = $this->AuthenticatedUser($request);

            DB::afterCommit(function () use ($user) {
                if ($user->tokens->count() > 0) {
                    $user->tokens()->delete();
                }
                User::destroy($user->id);
            });
            $this->clearMultipleCachePages("admin_management_users", 200);
            DB::commit();
            return $this->success("تم تسجيل الخروج بنجاح");
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            DB::rollBack();
            return $this->error("حدث خطأ في السيرفر");
        }
    }
}
