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
            'booking_reference' => $this->booking_reference,
            'room_type_id' => $this->room_type_id,
            'room_unit_id' => $this->room_unit_id,
            'checkin_date' => $this->checkin_date?->toDateString(),
            'checkout_date' => $this->checkout_date?->toDateString(),
            'guests' => $this->guests,
            'guest_name' => $this->guest_name,
            'guest_email' => $this->guest_email,
            'guest_phone' => $this->guest_phone,
            'status' => $this->status,
            'total_price' => $this->total_price,
            'room_type' => new RoomTypeResource($this->whenLoaded('roomType')),
            'room_unit' => new RoomUnitResource($this->whenLoaded('roomUnit')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
