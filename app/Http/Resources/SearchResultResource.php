<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Shapes one search result (a hotel plus its available rooms). The underlying
 * resource is the plain array produced by SearchService, so this only formats
 * the money fields for the JSON payload.
 */
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
            'rooms' => array_map(fn (array $room): array => [
                'id' => $room['id'],
                'name' => $room['name'],
                'price_per_night' => number_format($room['price_per_night'], 2, '.', ''),
                'max_occupancy' => $room['max_occupancy'],
                'available_units' => $room['available_units'],
                'total_price' => number_format($room['total_price'], 2, '.', ''),
            ], $this->resource['rooms']),
        ];
    }
}
