<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}
