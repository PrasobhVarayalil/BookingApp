<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Room
 */
class RoomResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'hotel_id' => $this->hotel_id,
            'name' => $this->name,
            'price_per_night' => $this->price_per_night,
            'max_occupancy' => $this->max_occupancy,
            'total_rooms' => $this->total_rooms,
            'hotel' => new HotelResource($this->whenLoaded('hotel')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
