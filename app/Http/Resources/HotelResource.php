<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Hotel
 */
class HotelResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'city' => $this->city,
            'country' => $this->country,
            'rating' => $this->rating,
            'room_types' => RoomTypeResource::collection($this->whenLoaded('roomTypes')),
            'room_types_count' => $this->whenCounted('roomTypes'),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
