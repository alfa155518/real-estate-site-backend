<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Properties extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'slug',
        'description',
        'price',
        'currency',
        'discount',
        'discounted_price',
        'type',
        'purpose',
        'property_type',
        'bedrooms',
        'bathrooms',
        'living_rooms',
        'kitchens',
        'balconies',
        'area_total',
        'features',
        'tags',
        'floor',
        'total_floors',
        'furnishing',
        'status',
        'owner_id',
        'agency_id',
        'views',
        'likes',
        'is_featured',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'features' => 'array',
        'tags' => 'array',
        'price' => 'decimal:2',
        'discount' => 'decimal:2',
        'discounted_price' => 'decimal:2',
        'area_total' => 'decimal:2',
        'is_featured' => 'boolean',
    ];

    /**
     * Set JSON attribute with proper encoding
     * 
     * @param string $key
     * @param mixed $value
     * @return void
     */
    protected function setJsonAttribute($key, $value): void
    {
        if (is_array($value)) {
            // Filter out any empty or null values and re-index the array
            $filtered = array_values(array_filter($value, fn($item) => !empty($item) && is_string($item)));
            // Use JSON_UNESCAPED_UNICODE to preserve Arabic characters
            // Save as '[]' for empty arrays instead of null
            $this->attributes[$key] = json_encode($filtered, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } else if (is_string($value) && json_decode($value) !== null) {
            // If it's a JSON string, decode and process it
            $this->setJsonAttribute($key, json_decode($value, true));
        } else if (is_null($value)) {
            // Convert null to empty array
            $this->attributes[$key] = json_encode([], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } else {
            $this->attributes[$key] = $value;
        }
    }

    /**
     * Set the features attribute with proper JSON encoding
     *
     * @param  array|string|null  $value
     * @return void
     */
    public function setFeaturesAttribute($value)
    {
        $this->setJsonAttribute('features', $value);
    }

    // /**
    //  * Set the tags attribute with proper JSON encoding
    //  *
    //  * @param  array|string|null  $value
    //  * @return void
    //  */
    public function setTagsAttribute($value)
    {
        $this->setJsonAttribute('tags', $value);
    }


    /**
     * Get the owner that owns the property.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(Owners::class);
    }

    /**
     * Get the agency that manages the property.
     */
    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agencies::class);
    }

    /**
     * Get the location for the property.
     */
    public function location(): HasOne
    {
        return $this->hasOne(PropertyLocations::class, 'property_id');
    }

    /**
     * Get the images for the property.
     */
    public function images(): HasMany
    {
        return $this->hasMany(PropertyImages::class, 'property_id');
    }

    /**
     * Get the videos for the property.
     */
    public function videos(): HasMany
    {
        return $this->hasMany(PropertyVideos::class, 'property_id');
    }

    /**
     * Get the users that favorited the property.
     */
    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favorite_properties', 'property_id', 'user_id')
            ->withTimestamps();
    }

    public function normalizeArabic($text)
    {
        if (empty($text)) {
            return '';
        }
        // Existing replacements...
        $text = str_replace(['أ', 'إ', 'آ'], 'ا', $text);
        $text = str_replace('ة', 'ه', $text);
        $text = str_replace('ى', 'ي', $text);
        // Remove diacritics
        $text = preg_replace('/[\x{064B}-\x{065F}\x{0670}]/u', '', $text); // Added \x{0670} for tatweel
        // Trim whitespace
        return trim($text);
    }

    /**
     * Scope to search for normalized location
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $searchTerm
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithNormalizedLocation($query, $searchTerm)
    {
        $normalizedSearch = $this->normalizeArabic($searchTerm);
        $locationTerm = '%' . $normalizedSearch . '%';

        return $query->where(function ($q) use ($locationTerm) {
            $q->whereHas('location', function ($locQuery) use ($locationTerm) {
                $locQuery->where(function ($loc) use ($locationTerm) {
                    $loc->whereRaw("REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
                    city,
                    'أ', 'ا'), 'إ', 'ا'), 'آ', 'ا'), 'ة', 'ه'), 'ى', 'ي'
                ) LIKE ?", [$locationTerm])
                        ->orWhereRaw("REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
                    district,
                    'أ', 'ا'), 'إ', 'ا'), 'آ', 'ا'), 'ة', 'ه'), 'ى', 'ي'
                ) LIKE ?", [$locationTerm]);
                });
            });
        });
    }
}