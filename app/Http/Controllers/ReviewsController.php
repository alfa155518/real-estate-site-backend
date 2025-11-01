<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateReviewRequest;
use App\Http\Resources\ReviewsResource;
use App\Models\Reviews;
use App\Traits\AuthenticatedUser;
use App\Traits\ClearCache;
use App\Traits\HandleResponse;
use App\Traits\RateLimitable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * ReviewsController
 * 
 * Handles all review-related operations including:
 * - Fetching paginated reviews
 * - Creating new reviews
 * - Toggling likes on reviews
 * 
 * @package App\Http\Controllers
 */
class ReviewsController extends Controller
{
    use HandleResponse, AuthenticatedUser, RateLimitable, ClearCache;


    /**
     * Get all reviews with pagination
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function allReviews()
    {
        try {

            $page = request()->get('page', 1);
            $reviews = Reviews::getCachedReviews($page);
            $total = Reviews::count();
            $hasMore = Reviews::hasMorePages($page, $total);

            return response()->json([
                'reviews' => $reviews,
                'has_more' => $hasMore,
                'current_page' => (int) $page,
                'total_reviews' => $total
            ], 200);
        } catch (\Exception $e) {
            return $this->error("حدث خطأ في السيرفر");
        }
    }

    /**
     * Get reviews for a specific property
     *
     * @param int $propertyId Property ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function reviewsByProperty($propertyId)
    {
        try {
            $reviews = Reviews::with('user:id,name')
                ->where('property_id', $propertyId)
                ->get()
                ->map(fn($review) => new ReviewsResource($review));

            return response()->json([
                'status' => "success",
                'reviews' => $reviews,
                'total_reviews' => $reviews->count()
            ], 200);
        } catch (\Exception $e) {
            return $this->error("حدث خطأ في السيرفر");
        }
    }

    /**
     * Create a new review
     *
     * @param CreateReviewRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createReview(CreateReviewRequest $request)
    {

        // Rate limit by IP
        if ($response = $this->rateLimit('post_review:' . $request->ip(), 3)) {
            return $response;
        }

        $user = $this->AuthenticatedUser($request);

        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $validatedData = $request->validated();

        if (Reviews::hasUserReviewed($user->id, $validatedData['property_id'] ?? null)) {
            return $this->error("لقد قمت بإضافة تقييم لهذا العقار من قبل", 409);
        }

        return $this->storeReview($user->id, $validatedData);
    }

    /**
     * Toggle like/unlike on a review
     *
     * @param Request $request
     * @param int $id Review ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleLike(Request $request, $id)
    {
        // Rate limit by IP
        if ($response = $this->rateLimit('review_like:' . $request->ip())) {
            return $response;
        }

        $user = $this->AuthenticatedUser($request);

        if ($user instanceof \Illuminate\Http\JsonResponse) {
            return $user;
        }

        $review = Reviews::find($id);

        if (!$review) {
            return $this->error("التقييم غير موجود", 404);
        }
        return $this->processToggleLike($review, $user->id);
    }
    /**
     * Store a new review in the database
     *
     * @param int $userId User ID
     * @param array $validatedData Validated review data
     * @return \Illuminate\Http\JsonResponse
     */
    private function storeReview(int $userId, array $validatedData)
    {
        try {
            DB::beginTransaction();

            Reviews::create([
                'user_id' => $userId,
                'property_id' => $validatedData['property_id'],
                'rating' => $validatedData['rating'],
                'comment' => $validatedData['comment'],
                'likes' => [],
            ]);

            Reviews::clearReviewsCache();
            DB::commit();

            return $this->success("سعداء بتقديم تقييمك", 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error("حدث خطأ في السيرفر");
        }
    }

    /**
     * Process toggle like/unlike action on a review
     *
     * @param Reviews $review Review model instance
     * @param int $userId User ID
     * @return \Illuminate\Http\JsonResponse
     */
    private function processToggleLike(Reviews $review, int $userId)
    {
        try {
            DB::beginTransaction();

            $result = $review->toggleLike($userId);
            Reviews::clearReviewsCache();

            DB::commit();

            $message = $result['is_liked']
                ? 'تم الإعجاب بالتقييم'
                : 'تم إلغاء الإعجاب';

            return response()->json([
                'status' => "success",
                'message' => $message,
                'review' => [
                    'likes' => $review->likes,
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error("حدث خطأ في السيرفر");
        }
    }
}
