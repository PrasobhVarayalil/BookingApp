<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\RoomUnit;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin RoomUnit
 */
class RoomUnitResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'room_type_id' => $this->room_type_id,
            'room_number' => $this->room_number,
            'status' => $this->status,
        ];
    }
}
