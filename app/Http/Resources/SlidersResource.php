<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SlidersResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $images = array_map(function ($image) {
            return asset($image);
        }, $this->images);
        return [
            'id' => $this->id,
            'name' => $this->name,
            'images' => $images,
        ];
    }
}
