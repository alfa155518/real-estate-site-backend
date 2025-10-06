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