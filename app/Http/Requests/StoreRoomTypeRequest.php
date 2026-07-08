<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoomTypeRequest extends FormRequest
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
            'room_numbers' => [$this->isMethod('post') ? 'required' : 'nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function typeAttributes(): array
    {
        return $this->only(['hotel_id', 'name', 'price_per_night', 'max_occupancy']);
    }

    /**
     * @return list<string>
     */
    public function unitNumbers(): array
    {
        $numbers = preg_split('/[\s,]+/', (string) $this->input('room_numbers'), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return array_values(array_unique(array_map('trim', $numbers)));
    }
}
