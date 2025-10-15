<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyResource extends JsonResource
{
    /**
     * Convert a number to Arabic digits.
     *
     * @param mixed $number
     * @return string
     */
    private function toArabicDigits($number): string
    {
        if (is_null($number)) {
            return '';
        }

        $arabic = [
            '0' => '٠',
            '1' => '١',
            '2' => '٢',
            '3' => '٣',
            '4' => '٤',
            '5' => '٥',
            '6' => '٦',
            '7' => '٧',
            '8' => '٨',
            '9' => '٩',
            '.' => '.',
            ',' => ','
        ];

        return strtr((string) $number, $arabic);
    }

    /**
     * Calculate discount percentage based on original price and discounted price
     *
     * @param float $originalPrice
     * @param float $discountedPrice
     * @return string
     */
    private function calculateDiscountPercentage($originalPrice, $discountedPrice): string
    {
        if (empty($originalPrice) || empty($discountedPrice) || $originalPrice <= 0 || $originalPrice <= $discountedPrice) {
            return '';
        }

        $discountPercentage = (($originalPrice - $discountedPrice) / $originalPrice) * 100;

        // Format to 0 decimal places and add percentage sign
        $formattedPercentage = number_format($discountPercentage, 0);

        return $this->toArabicDigits($formattedPercentage) . '%';
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
            'price' => $this->toArabicDigits($this->price),
            'currency' => $this->currency,
            'discount' => $this->discount,
            'discount_percentage' => $this->calculateDiscountPercentage($this->price, $this->discounted_price),
            'discounted_price' => $this->toArabicDigits($this->discounted_price),
            'type' => $this->type,
            'purpose' => $this->purpose,
            'property_type' => $this->property_type,
            'bedrooms' => $this->bedrooms,
            'bathrooms' => $this->bathrooms,
            'living_rooms' => $this->living_rooms,
            'kitchens' => $this->kitchens,
            'balconies' => $this->balconies,
            'area_total' => $this->toArabicDigits($this->area_total),
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
            'owner' => new OwnerResource($this->whenLoaded('owner')),
            'agency' => new AgencyResource($this->whenLoaded('agency')),
            'location' => new PropertyLocationResource($this->whenLoaded('location')),
            'images' => PropertyImageResource::collection($this->whenLoaded('images')),
            'videos' => PropertyVideoResource::collection($this->whenLoaded('videos')),
        ];
    }
}