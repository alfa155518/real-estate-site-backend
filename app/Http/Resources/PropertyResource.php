<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Utils\ConvertNumbers;

class PropertyResource extends JsonResource
{
  

    /**
     * Calculate discount percentage based on original price and discounted price
     *
     * @param float $originalPrice
     * @param float $discountedPrice
     * @return string
     */
    private function calculateDiscountPercentage($originalPrice, $discountedPrice): string
    {
        if (empty($originalPrice) || empty($discountedPrice) || $originalPrice <= 0 || $discountedPrice <= 0) {
            return '';
        }

        $discountPercentage = abs((($originalPrice - $discountedPrice) / $originalPrice) * 100);

        // If the discount percentage is 0, return empty string
        if ($discountPercentage === 0.0) {
            return '';
        }

        // Format to 0 decimal places and add percentage sign
        $formattedPercentage = number_format($discountPercentage, 0);

        return ConvertNumbers::toArabicDigits($formattedPercentage) . '%';
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => ConvertNumbers::toArabicDigits($this->price),
            'currency' => $this->currency,
            'discount' => $this->discount,
            'discount_percentage' => $this->calculateDiscountPercentage($this->price, $this->discounted_price),
            'discounted_price' => $this->discounted_price > 0 ? ConvertNumbers::toArabicDigits($this->discounted_price) : null,
            'type' => $this->type,
            'purpose' => $this->purpose,
            'property_type' => $this->property_type,
            'bedrooms' => $this->bedrooms,
            'bathrooms' => $this->bathrooms,
            'living_rooms' => $this->living_rooms,
            'kitchens' => $this->kitchens,
            'balconies' => $this->balconies,
            'area_total' => ConvertNumbers::toArabicDigits($this->area_total),
            'features' => $this->features,
            'tags' => $this->tags,
            'floor' => $this->floor,
            'total_floors' => $this->total_floors,
            'furnishing' => $this->furnishing,
            'status' => $this->status,
            'views' => $this->views,
            'likes' => $this->likes,
            'is_featured' => $this->is_featured,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'owner_id' => $this->owner_id,
            'owner' => new OwnerResource($this->whenLoaded('owner')),
            'agency' => new AgencyResource($this->whenLoaded('agency')),
            'location' => new PropertyLocationResource($this->whenLoaded('location')),
            'images' => PropertyImageResource::collection($this->whenLoaded('images')),
            'videos' => PropertyVideoResource::collection($this->whenLoaded('videos')),
        ];
    }
}