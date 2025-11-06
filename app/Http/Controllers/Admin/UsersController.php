<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\UsersResource;
use App\Models\User;
use App\Traits\ClearCache;
use App\Traits\HandleResponse;
use DB;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class UsersController extends Controller
{

    use HandleResponse, ClearCache;

    private const MAX_CACHE_PAGES = 200;


    /**
     * Get all users
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $currentPage = request()->get('page', 1);

            $query = User::query();

            $users = Cache::rememberForever("admin_management_users-{$currentPage}", function () use ($query, $currentPage) {
                return $query->paginate(10, ['*'], 'page', $currentPage);

            });

            $adminsTotal = User::where('role', 'admin')->count();
            return $this->successData(new UsersResource($users, $adminsTotal));

        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return $this->error("حدث خطأ ما");
        }
    }

    /**
     * Update a user
     *
     * @param int $id
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id, Request $request)
    {
        $currentPage = request()->get('current_page');

        try {

            $validator = Validator::make(
                $request->all(),
                User::adminUpdateUserRules(),
                User::adminUpdateUserMessages()
            );

            if ($validator->fails()) {
                return $this->error($validator->errors()->first());
            }

            $user = User::findOrFail($id);
            $validatedData = $validator->validated();

            $user->fill($validatedData)->save();

            $this->clearCache("admin_management_users-{$currentPage}");

            return $this->success("تم التحديث بنجاح");

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFound("المستخدم غير موجود");
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return $this->error("حدث خطأ في السيرفر");
        }
    }


    /**
     * Delete a user
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id)
    {
        $currentPage = request()->get('current_page');
        try {
            DB::beginTransaction();

            $user = User::with(['tokens'])->findOrFail($id);

            // Delete user's personal access tokens
            $user->tokens()->delete();

            // Finally, delete the user
            $user->delete();

            $this->clearCache("admin_management_users-{$currentPage}");

            DB::commit();
            return $this->success("تم حذف المستخدم بنجاح");

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFound("المستخدم غير موجود");
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error("حدث خطأ أثناء حذف المستخدم");
        }
    }

}
