<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewsResource extends JsonResource
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
            'property_id' => $this->property_id,
            'property_title' => $this->property->title,
            'user_name' => $this->user ? $this->user->name : 'مستخدم',
            'rating' => $this->rating,
            'comment' => $this->comment,
            'likes_count' => count($this->likes ?? []),
            'created_at' => $this->created_at,
        ];
    }
}
