<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateUsersSupport;
use App\Models\UserSupport;
use App\Traits\AuthenticatedUser;
use App\Traits\HandleResponse;
use App\Traits\HandleUploads;
use App\Traits\RateLimitable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserSupportController extends Controller
{
    use HandleResponse, AuthenticatedUser, HandleUploads, RateLimitable;
    public function store(CreateUsersSupport $request)
    {
        // Rate limit by IP
        if ($response = $this->rateLimit('support:' . $request->ip(), 5, 120)) {
            return $response;
        }
        try {
            DB::beginTransaction();
            $user = $this->AuthenticatedUser($request);

            $validated = $request->validated();
            $userSupport = UserSupport::create([
                'user_id' => $user->id,
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'priority' => $validated['priority'] ?? 'low',
                'subject' => $validated['subject'],
                'message' => $validated['message'],
                'images' => null,
                'status' => $validated['status'] ?? 'pending',
            ]);

            if ($request->hasFile('images')) {
                $imagePaths = $this->uploadImages($request->file('images'), 'users_support');
                $userSupport->images = $imagePaths;
                $userSupport->save();
            }


            DB::commit();
            return $this->success('سنتواصل معك قريباً', 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return $this->error($e->validator->errors()->first(), 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('حدث خطأ ما');
        }
    }
}

