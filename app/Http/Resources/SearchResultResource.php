<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SearchResultResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'hotel' => $this->resource['hotel'],
            'nights' => $this->resource['nights'],
            'rooms' => collect($this->resource['rooms'])->map(fn (array $room): array => [
                'id' => $room['id'],
                'name' => $room['name'],
                'price_per_night' => number_format($room['price_per_night'], 2, '.', ''),
                'max_occupancy' => $room['max_occupancy'],
                'available_units' => $room['available_units'],
                'available_room_numbers' => $room['available_room_numbers'],
                'total_price' => number_format($room['total_price'], 2, '.', ''),
            ])->all(),
        ];
    }
}
