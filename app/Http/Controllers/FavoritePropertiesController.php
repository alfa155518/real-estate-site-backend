<?php

namespace App\Http\Controllers;

use App\Http\Resources\PropertyResource;
use App\Models\Properties;
use App\Traits\AuthenticatedUser;
use App\Traits\HandleResponse;
use App\Traits\RateLimitable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
class FavoritePropertiesController extends Controller
{
    use AuthenticatedUser, HandleResponse, RateLimitable;

    /**
     * Toggle favorite status for a property
     * @param Request $request
     * @param int $propertyId
     */
    public function toggleFavorite(Request $request, $propertyId)
    {
        // Rate limit by IP
        if ($response = $this->rateLimit('toggle_favorite:' . $request->ip(), 8)) {
            return $response;
        }
        try {
            DB::beginTransaction();
            $user = $this->AuthenticatedUser($request);
            $property = Properties::find($propertyId);

            if (!$property) {
                return $this->notFound("العقار غير موجود");
            }

            Cache::forget('user_favorites_' . $user->id);

            if ($user->favoriteProperties()->where('property_id', $property->id)->exists()) {
                $user->favoriteProperties()->detach($property->id);
                DB::commit();
                return $this->success("تم إزالة العقار من المفضلة");
            } else {
                $user->favoriteProperties()->attach($property->id);
                DB::commit();
                return $this->success("تم إضافة العقار للمفضلة", 201);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error("حدث خطأ ما");
        }
    }

    /**
     * Get user's favorite properties
     * @param Request $request
     */
    public function getFavorites(Request $request)
    {
        try {
            $user = $this->AuthenticatedUser($request);
            $favorites = Cache::rememberForever('user_favorites_' . $user->id, function () use ($user) {
                return $user->favoriteProperties()
                    ->with([
                        'images',
                        'location',
                        'owner',
                        'agency',
                        'videos'
                    ])
                    ->get();
            });
            return $this->successData([PropertyResource::collection($favorites), "user_id" => $user->id]);
        } catch (\Exception $e) {
            return $this->error("حدث خطأ ما");
        }
    }
}
