<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyResource extends JsonResource
{
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
            'price' => $this->price,
            'currency' => $this->currency,
            'discount' => $this->discount,
            'discounted_price' => $this->discounted_price,
            'type' => $this->type,
            'purpose' => $this->purpose,
            'property_type' => $this->property_type,
            'bedrooms' => $this->bedrooms,
            'bathrooms' => $this->bathrooms,
            'living_rooms' => $this->living_rooms,
            'kitchens' => $this->kitchens,
            'balconies' => $this->balconies,
            'area_total' => $this->area_total,
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