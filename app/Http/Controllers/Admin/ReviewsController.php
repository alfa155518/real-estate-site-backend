<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reviews;
use App\Traits\HandleResponse;
use DB;

class ReviewsController extends Controller
{
    use HandleResponse;


    /**
     * Get a paginated list of reviews
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
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
                'total_reviews' => $total,
                'total_likes_count' => Reviews::getTotalLikesCount(),
            ], 200);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return $this->error("حدث خطأ ما");
        }
    }

    /**
     * Delete a review
     *
     * @param int $id Review ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id)
    {
        try {
            $review = Reviews::find($id);
            DB::beginTransaction();
            if (!$review) {
                return $this->error("المراجعة غير موجودة");
            }
            $review->delete();
            Reviews::clearReviewsCache();
            DB::commit();
            return $this->success("تم حذف المراجعة");
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error("حدث خطأ ما");
        }
    }
}

