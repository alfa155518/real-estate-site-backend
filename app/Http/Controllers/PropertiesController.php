<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreatePropertiesFiltration;
use App\Http\Resources\PropertyResource;
use Illuminate\Http\Request;
use App\Http\Resources\PropertyCollection;
use App\Models\Properties;
use App\Traits\HandleResponse;
use Illuminate\Support\Facades\Cache;

class PropertiesController extends Controller
{
    use HandleResponse;
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $currentPage = $request->header('page', 1);
        $cacheKey = 'properties_page_' . $currentPage;
        try {
            $query = Properties::query();

            $properties = Cache::rememberForever($cacheKey, function () use ($query, $currentPage) {
                return $query->with(['location', 'images', 'videos', 'owner', 'agency'])->paginate(10, ['*'], 'page', $currentPage);
            });

            return response()->json([
                'success' => "success",
                'data' => new PropertyCollection($properties)
            ], 200);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return $this->error("حدث خطأ ما");
        }
    }

    /**
     * Filter properties based on various parameters
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function filterByParams(CreatePropertiesFiltration $request)
    {
        try {
            $query = Properties::query();
            $validated = $request->validated();

            if (isset($validated['search'])) {
                $searchTerm = trim($validated['search']);

                if (!empty($searchTerm)) {
                    // أولاً نحاول بالبحث باستخدام full-text search
                    $query->selectRaw("*, MATCH(title, description) AGAINST(? IN NATURAL LANGUAGE MODE) AS relevance", [$searchTerm])
                        ->whereRaw("MATCH(title, description) AGAINST(? IN NATURAL LANGUAGE MODE)", [$searchTerm])
                        ->orderByDesc('relevance');

                }
            }

            // Filter by is_featured
            if (!empty($validated['is_featured']) && $validated['is_featured'] !== 'all') {
                $isFeatured = filter_var($validated['is_featured'], FILTER_VALIDATE_BOOLEAN);
                $query->where('is_featured', $isFeatured);
            }

            // Filter by status
            if (!empty($validated['status']) && $validated['status'] !== 'all') {
                $query->where('status', $validated['status']);
            }

            // Filter by property type
            if (!empty($validated['type']) && $validated['type'] !== 'all') {
                $query->where('type', $validated['type']);
            }

            // Filter by location
            if (!empty($validated['location'])) {
                $query->withNormalizedLocation($validated['location']);
            }

            // Filter by price range
            if (isset($validated['minPrice'])) {
                $query->where('price', '>=', $validated['minPrice']);
            }
            if (isset($validated['maxPrice'])) {
                $query->where('price', '<=', $validated['maxPrice']);
            }

            // Filter by number of bedrooms
            if (isset($validated['bedrooms'])) {
                $query->where('bedrooms', $validated['bedrooms']);
            }

            // Filter by number of bathrooms
            if (isset($validated['bathrooms'])) {
                $query->where('bathrooms', $validated['bathrooms']);
            }

            $properties = $query->with(['location', 'images', 'videos', 'owner', 'agency'])
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            return response()->json([
                'success' => true,
                'data' => new PropertyCollection($properties)
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error($e->validator->errors()->first(), 422);
        } catch (\Exception $e) {
            return $this->error('حدث خطأ ما');
        }
    }

    /**
     * Get a single property by slug
     *
     * @param string $slug
     * @return \Illuminate\Http\JsonResponse
     */
    public function singleProperty($slug)
    {
        try {

            $property = Properties::where('slug', $slug)->first();

            if (!$property) {
                return $this->notFound('العقار غير موجود');
            }

            $property = Cache::rememberForever('property_' . $slug, function () use ($slug) {
                return Properties::where('slug', $slug)->with(['location', 'images', 'videos', 'owner', 'agency'])->first();
            });
            return response()->json([
                'status' => 'success',
                'data' => new PropertyResource($property)
            ], 200);
        } catch (\Exception $e) {
            return $this->error('حدث خطأ ما');
        }
    }
}
