<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ItemResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'price' => (float) $this->price,
            'condition' => $this->condition,
            'status' => $this->status,
            'category' => $this->category_name ?? $this->category?->name,
            'category_id' => $this->category_id,
            'sub_category_id' => $this->sub_category_id,
            'child_sub_category_id' => $this->child_sub_category_id,
            'category_path' => $this->category_path,
            'location' => $this->location_display,
            'location_id' => $this->location_id,
            'contact_method' => $this->contact_method,
            'tags' => $this->tags ?? [],
            'is_promoted' => $this->isPromoted(),
            'is_favorited' => $this->when(auth()->check(), function() {
                return $this->favorites()->where('user_id', auth()->id())->exists();
            }, false),
            'images' => $this->when($this->relationLoaded('images'), function() {
                return $this->images->map(function($image) {
                    return [
                        'id' => $image->id,
                        'url' => $image->url,
                        'is_primary' => $image->is_primary,
                        'order' => $image->order,
                    ];
                });
            }),
            'primary_image' => $this->when($this->relationLoaded('primaryImage') && $this->primaryImage, [
                'url' => $this->primaryImage->url,
            ]),
            'user' => $this->when($this->relationLoaded('user'), [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'profile_image' => $this->user->profile_image,
            ]),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
