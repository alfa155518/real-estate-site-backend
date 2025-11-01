<?php

namespace App\Models;

use App\Http\Resources\ReviewsResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

/**
 * Reviews Model
 * 
 * Represents a review for a property with rating, comment, and likes.
 * 
 * @property int $id
 * @property int $user_id
 * @property int|null $property_id
 * @property int $rating
 * @property string $comment
 * @property array $likes Array of user IDs who liked this review
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * 
 * @package App\Models
 */
class Reviews extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'property_id',
        'rating',
        'comment',
        'likes'
    ];

    protected $casts = [
        'rating' => 'integer',
        'likes' => 'array',
    ];

    /** @var int Number of reviews per page */
    private const PER_PAGE = 8;

    /** @var int Maximum number of cache pages to clear */
    private const MAX_CACHE_PAGES = 100;

    /**
     * Get the user who created the review
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the property that this review belongs to
     *
     * @return BelongsTo
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Properties::class);
    }




    /**
     * Toggle like/unlike for a review
     * If user already liked, remove the like. Otherwise, add the like.
     *
     * @param int $userId User ID
     * @return array Returns is_liked status and likes_count
     */
    public function toggleLike(int $userId): array
    {
        $likes = $this->likes ?? [];

        if ($this->isLikedBy($userId)) {
            $likes = array_values(array_diff($likes, [$userId]));
        } else {
            $likes[] = $userId;
        }

        $this->likes = $likes;
        $this->save();

        return [
            'is_liked' => $this->isLikedBy($userId),
            'likes_count' => count($this->likes)
        ];
    }

    /**
     * Check if a specific user has liked this review
     *
     * @param int $userId User ID
     * @return bool
     */
    public function isLikedBy(int $userId): bool
    {
        return in_array($userId, $this->likes ?? []);
    }

    /**
     * Get the total count of likes for this review
     *
     * @return int
     */
    public function getLikesCountAttribute(): int
    {
        return count($this->likes ?? []);
    }

    /**
     * Check if a user has already reviewed a specific property
     *
     * @param int $userId User ID
     * @param int|null $propertyId Property ID
     * @return bool
     */
    public static function hasUserReviewed(int $userId, ?int $propertyId): bool
    {
        return self::where('user_id', $userId)
            ->where('property_id', $propertyId)
            ->exists();
    }

    /**
     * Get cached reviews for a specific page
     *
     * @param int $page Page number
     * @return \Illuminate\Support\Collection
     */
    public static function getCachedReviews(int $page)
    {
        return Cache::rememberForever(
            "reviews-{$page}",
            fn() => Reviews::with('user:id,name')
                ->orderBy('created_at', 'desc')
                ->skip(($page - 1) * self::PER_PAGE)
                ->take(self::PER_PAGE)
                ->get()
                ->map(fn($review) => new ReviewsResource($review))
        );
    }


    /**
     * Check if there are more pages available
     *
     * @param int $page Current page number
     * @param int $total Total number of reviews
     * @return bool
     */
    public static function hasMorePages(int $page, int $total): bool
    {
        return ($page * self::PER_PAGE) < $total;
    }



    /**
     * Get the total number of likes across all reviews
     *
     * @return int
     */
    public static function getTotalLikesCount(): int
    {
        return self::all()
            ->sum(function ($review) {
                return count($review->likes ?? []);
            });
    }

    /**
     * Clear all cached review pages
     *
     * @return void
     */
    public static function clearReviewsCache(): void
    {
        for ($page = 1; $page <= self::MAX_CACHE_PAGES; $page++) {
            Cache::forget("reviews-{$page}");
        }
    }
}
