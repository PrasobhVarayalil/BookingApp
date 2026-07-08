<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'hotel_id' => ['required', 'uuid', 'exists:hotels,id'],
            'name' => ['required', 'string', 'max:255'],
            'price_per_night' => ['required', 'numeric', 'min:0'],
            'max_occupancy' => ['required', 'integer', 'min:1'],
            'total_rooms' => ['required', 'integer', 'min:1'],
        ];
    }
}
