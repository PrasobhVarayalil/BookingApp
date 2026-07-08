<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Booking
 */
class BookingResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'room_id' => $this->room_id,
            'checkin_date' => $this->checkin_date?->toDateString(),
            'checkout_date' => $this->checkout_date?->toDateString(),
            'guests' => $this->guests,
            'status' => $this->status,
            'total_price' => $this->total_price,
            'room' => new RoomResource($this->whenLoaded('room')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
