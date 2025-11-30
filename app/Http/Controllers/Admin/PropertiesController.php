<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreatePropertyRequest;
use App\Http\Requests\Admin\UpdatePropertyRequest;
use App\Http\Resources\PropertyCollection;
use App\Models\Properties;
use App\Traits\ClearCache;
use App\Traits\HandleResponse;
use App\Traits\HandleUploads;
use App\Utils\ConvertNumbers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;



class PropertiesController extends Controller
{
    use HandleResponse, ClearCache, HandleUploads;

    /**
    * @desc Get all properties
    * @access Admin
    * @param Request $request
    * @return json
    */
    public function index(Request $request)
    {
        $currentPage = $request->header('page', 1);
        $cacheKey = 'properties_page-' . $currentPage;
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
            return $this->error("حدث خطأ ما");
        }
    }

    /**
    * @desc Create new property
    * @access Admin
    * @param CreatePropertyRequest $request
    * @return json
    */
    public function store(CreatePropertyRequest $request)
    {
        $currentPage = $request->header('page', 1);

        try {
            // ----------------------------------------
            // 1) Handle PHP upload errors before validation
            // ----------------------------------------
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $file) {
                    if (!$file->isValid() || !$file->getSize() || $file->getSize() > 2 * 1024 * 1024) {
                        return $this->error(
                            "حجم الصورة يجب أن لا يتجاوز 2 ميجابايت"
                        );
                    }
                }
            }
            if ($request->hasFile('videos')) {
                foreach ($request->file('videos') as $index => $file) {
                    if (!$file->isValid() || !$file->getSize() || $file->getSize() > 5 * 1024 * 1024) {
                        return $this->error(
                            "حجم الفيديو يجب أن لا يتجاوز 5 ميجابايت"
                        );
                    }
                }
            }

            // ----------------------------------------
            // 2) Laravel Validation
            // ----------------------------------------
            $validated = $request->validated();
            DB::beginTransaction();

            // ----------------------------------------
            // 3) Normalize validated data
            // ----------------------------------------
            $numeric = [
                'price',
                'area_total',
                'discount',
                'discounted_price',
                'bedrooms',
                'bathrooms',
                'living_rooms',
                'kitchens',
                'balconies',
                'floor',
                'total_floors'
            ];

            foreach ($numeric as $field) {
                if (!empty($validated[$field])) {
                    $validated[$field] = (float) ConvertNumbers::convertToEnglishDigits($validated[$field]);
                }
            }

            // ----------------------------------------
            // 4) Generate unique slug
            // ----------------------------------------
            $slug = \Illuminate\Support\Str::slug($validated['title']);
            $originalSlug = $slug;
            $counter = 1;

            while (Properties::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }

            $validated['slug'] = $slug;

            // ----------------------------------------
            // 5) Create property
            // ----------------------------------------
            $property = Properties::create($validated);

            // ----------------------------------------
            // 6) Create location
            // ----------------------------------------
            $property->location()->create([
                'city' => $validated['city'],
                'district' => $validated['district'],
                'street' => $validated['street'],
                'landmark' => $validated['landmark'] ?? null,
                'latitude' => $validated['latitude'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
            ]);

            // ----------------------------------------
            // 7) Handle features & tags
            // ----------------------------------------
            if (array_key_exists('features', $validated)) {
                $property->features = $validated['features'] ?? [];
            }
            if (array_key_exists('tags', $validated)) {
                $property->tags = $validated['tags'] ?? [];
            }
            $property->save();

            // ----------------------------------------
            // 8) Handle Image Upload
            // ----------------------------------------
            if ($request->hasFile('images')) {
                // Upload new images
                $uploadedPaths = $this->uploadImages($request->file('images'), 'properties');

                // Bulk insert images
                $imageData = [];
                foreach ($uploadedPaths as $index => $path) {
                    $imageData[] = [
                        'property_id' => $property->id,
                        'image_url' => $path,
                        'is_primary' => $index === 0, // First image is primary
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                
                if (!empty($imageData)) {
                    DB::table('property_images')->insert($imageData);
                }
            }

            // ----------------------------------------
            // 9) Handle Video Upload
            // ----------------------------------------
            if ($request->hasFile('videos')) {
                // Upload new videos as streams
                $uploadedPaths = $this->uploadVideos($request->file('videos'), 'properties/videos');

                // Bulk insert videos
                $videoData = [];
                foreach ($uploadedPaths as $path) {
                    $videoData[] = [
                        'property_id' => $property->id,
                        'video_url' => $path,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                
                if (!empty($videoData)) {
                    DB::table('property_videos')->insert($videoData);
                }
            }

            DB::commit();

            // ----------------------------------------
            // 10) Clear cache - only clear page 1 for new properties
            // ----------------------------------------
            $this->clearCache("properties_page-$currentPage");

            return $this->success("تم إنشاء العقار بنجاح");

        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error($e->validator->errors()->first(), 422);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Error creating property: " . $e->getMessage());
            return $this->error("حدث خطأ أثناء إنشاء العقار");
        }
    }

    /**
    * @desc Update property
    * @access Admin
    * @param UpdatePropertyRequest $request
    * @param int $id
    * @return json
    */
    public function update(UpdatePropertyRequest $request, $id)
    {
        $currentPage = $request->header('page', 1);

        try {
            // Only load relations that are needed for the update operation
            $property = Properties::with(['location'])
                ->findOrFail($id);

            // Store slug before potential update
            $oldSlug = $property->slug;

            // ----------------------------------------
            // 1) Handle PHP upload errors before validation
            // ----------------------------------------
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $file) {
                    if (!$file->isValid() || !$file->getSize() || $file->getSize() > 2 * 1024 * 1024) {
                        return $this->error(
                            "حجم الصورة يجب أن لا يتجاوز 2 ميجابايت"
                        );
                    }
                }
            }
            if ($request->hasFile('videos')) {
                foreach ($request->file('videos') as $index => $file) {
                    if (!$file->isValid() || !$file->getSize() || $file->getSize() > 5 * 1024 * 1024) {
                        return $this->error(
                            "حجم الفيديو يجب أن لا يتجاوز 5 ميجابايت"
                        );
                    }
                }
            }

            // ----------------------------------------
            // 2) Laravel Validation
            // ----------------------------------------
            $validated = $request->validated();
            DB::beginTransaction();

            // ----------------------------------------
            // 3) Normalize validated data
            // ----------------------------------------

            $numeric = [
                'price',
                'area_total',
                'discount',
                'discounted_price',
                'bedrooms',
                'bathrooms',
                'living_rooms',
                'kitchens',
                'balconies',
                'floor',
                'total_floors'
            ];

            foreach ($numeric as $field) {
                if (!empty($validated[$field])) {
                    $validated[$field] = (float) ConvertNumbers::convertToEnglishDigits($validated[$field]);
                }
            }

            $property->update($validated);

            // ----------------------------------------
            // 4) Update location
            // ----------------------------------------
            if ($property->location) {
                // Use the already loaded relation instead of querying again
                $property->location->update([
                    'city' => $validated['city'],
                    'district' => $validated['district'],
                    'street' => $validated['street'],
                    'landmark' => $validated['landmark'] ?? null,
                    'latitude' => $validated['latitude'] ?? null,
                    'longitude' => $validated['longitude'] ?? null,
                ]);
            }

            // ----------------------------------------
            // 5) Update features & tags
            // ----------------------------------------
            // Use array_key_exists to allow empty arrays (for deletion)
            if (array_key_exists('features', $validated)) {
                $property->features = $validated['features'] ?? [];
            }
            if (array_key_exists('tags', $validated)) {
                $property->tags = $validated['tags'] ?? [];
            }
            $property->save();

            // ----------------------------------------
            // 6) Handle Image Upload
            // ----------------------------------------
            if ($request->hasFile('images')) {
                // Get image URLs before deletion
                $oldImageUrls = $property->images()->pluck('image_url')->toArray();
                
                // Delete old images from database
                $property->images()->delete();

                // Upload new images
                $uploadedPaths = $this->uploadImages($request->file('images'), 'properties');

                // Bulk insert new images
                $imageData = [];
                foreach ($uploadedPaths as $index => $path) {
                    $imageData[] = [
                        'property_id' => $property->id,
                        'image_url' => $path,
                        'is_primary' => $index === 0, // First image is primary
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                
                if (!empty($imageData)) {
                    DB::table('property_images')->insert($imageData);
                }

                // Delete old image files after successful upload
                $this->deleteImages($oldImageUrls);
            }

            if ($request->hasFile('videos')) {
                // Get video URLs before deletion
                $oldVideoUrls = $property->videos()->pluck('video_url')->toArray();
                
                // Delete old videos from database
                $property->videos()->delete();

                // Upload new videos as streams
                $uploadedPaths = $this->uploadVideos($request->file('videos'), 'properties/videos');

                // Bulk insert new videos
                $videoData = [];
                foreach ($uploadedPaths as $path) {
                    $videoData[] = [
                        'property_id' => $property->id,
                        'video_url' => $path,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                
                if (!empty($videoData)) {
                    DB::table('property_videos')->insert($videoData);
                }

                // Delete old video files after successful upload
                $this->deleteVideos($oldVideoUrls);
            }
            
            DB::commit();
            
            // ----------------------------------------
            // 7) Clear cache - batch operation
            // ----------------------------------------
            $cachesToClear = [
                "properties_page-$currentPage",
                "property-$oldSlug"
            ];
            
            // If slug changed, clear both old and new
            if ($oldSlug !== $property->slug) {
                $cachesToClear[] = "property-{$property->slug}";
            }
            
            foreach ($cachesToClear as $cacheKey) {
                $this->clearCache($cacheKey);
            }

            return $this->success("تم تحديث العقار بنجاح");

        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error($e->validator->errors()->first(), 422);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Error updating property: " . $e->getMessage());
            return $this->error("حدث خطأ أثناء تحديث العقار");
        }
    }

    /**
    * @desc Delete property
    * @access Admin
    * @param int $id
    * @param Request $request
    * @return json
    */
    public function destroy($id, Request $request)
    {
        $currentPage = $request->header('page', 1);

        try {
            // ----------------------------------------
            // 1) Find property - only load necessary relations
            // ----------------------------------------
            $property = Properties::select('id', 'slug')
                ->findOrFail($id);
            
            // Store slug for cache clearing
            $slug = $property->slug;

            DB::beginTransaction();

            // ----------------------------------------
            // 2) Get file paths before deletion
            // ----------------------------------------
            $imagePaths = DB::table('property_images')
                ->where('property_id', $id)
                ->pluck('image_url')
                ->toArray();
                
            $videoPaths = DB::table('property_videos')
                ->where('property_id', $id)
                ->pluck('video_url')
                ->toArray();

            // ----------------------------------------
            // 3) Delete database records (cascading)
            // ----------------------------------------
            // Delete all related records in one go
            DB::table('property_images')->where('property_id', $id)->delete();
            DB::table('property_videos')->where('property_id', $id)->delete();
            DB::table('property_locations')->where('property_id', $id)->delete();
            
            // Delete property record
            $property->delete();

            DB::commit();

            // ----------------------------------------
            // 4) Delete files from storage after successful DB deletion
            // ----------------------------------------
            if (!empty($imagePaths)) {
                $this->deleteImages($imagePaths);
            }
            
            if (!empty($videoPaths)) {
                $this->deleteVideos($videoPaths);
            }

            // ----------------------------------------
            // 5) Clear cache
            // ----------------------------------------
            $cachesToClear = [
                "properties_page-$currentPage",
                "property-$slug"
            ];
            
            foreach ($cachesToClear as $cacheKey) {
                $this->clearCache($cacheKey);
            }

            return $this->success("تم حذف العقار بنجاح");

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return $this->error("العقار غير موجود", 404);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Error deleting property: " . $e->getMessage());
            return $this->error("حدث خطأ أثناء حذف العقار");
        }
    }
}
