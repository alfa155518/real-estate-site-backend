<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\SettingsResource;
use App\Models\Admin\Settings;
use App\Traits\ClearCache;
use App\Traits\HandleResponse;
use App\Http\Requests\Admin\CreateSettingsRequest;
use App\Traits\HandleUploads;
use DB;
use Illuminate\Support\Facades\Cache;

class SettingsController extends Controller
{

    use HandleResponse, ClearCache, HandleUploads;

    /**
     * Display a listing of the resource.
     *
     */
    public function index()
    {
        try {

            $settings = Cache::rememberForever('settings', function () {
                return SettingsResource::make(Settings::first());
            });
            return $this->successData($settings);
        } catch (\Exception $e) {
            return $this->error("حدث خطأ في السيرفر");
        }
    }

    /**
     * Update the specified resource in storage.
     *
     */
    public function update(CreateSettingsRequest $request)
    {
        try {
            DB::beginTransaction();
            $settings = Settings::first();

            $validatedData = $request->validated();


            if ($request->hasFile('logo')) {
                // Delete old logo if exists
                if ($settings->logo) {
                    $this->deleteImage($settings->logo);
                }
                // Upload and save new logo
                $settings->logo = $this->uploadImage($validatedData['logo'], 'settings');
                $settings->save();
            }

            // Update settings
            $settings->update([
                'location' => $validatedData['location'],
                'phone' => $validatedData['phone'],
                'email' => $validatedData['email'],
                'opening_hours' => $validatedData['opening_hours'],
                'facebook' => $validatedData['facebook'],
                'twitter' => $validatedData['twitter'],
                'instagram' => $validatedData['instagram'],
                'linkedin' => $validatedData['linkedin'],
            ]);
            $this->clearCache('settings');
            DB::commit();
            return $this->success("تم تحديث الإعدادات");
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error($e->validator->errors()->first(), 422);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error($e);
            return $this->error("حدث خطأ في السيرفر");
        }
    }

}
