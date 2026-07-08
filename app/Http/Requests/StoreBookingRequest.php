<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\RoomType;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'room_type_id' => ['required', 'uuid', 'exists:room_types,id'],
            'room_unit_id' => ['nullable', 'uuid', 'exists:room_units,id'],
            'checkin_date' => ['required', 'date', 'after_or_equal:today'],
            'checkout_date' => ['required', 'date', 'after:checkin_date'],
            'guests' => ['required', 'integer', 'min:1'],
            'guest_name' => ['required', 'string', 'max:255'],
            'guest_email' => ['required', 'email', 'max:255'],
            'guest_phone' => ['nullable', 'string', 'max:30'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $roomTypeId = $this->input('room_type_id');
            $guests = $this->integer('guests');
            $unitId = $this->input('room_unit_id');

            if (! is_string($roomTypeId) || $guests < 1) {
                return;
            }

            $roomType = RoomType::find($roomTypeId);

            if ($roomType && $guests > $roomType->max_occupancy) {
                $validator->errors()->add('guests', "This room type sleeps at most {$roomType->max_occupancy} guests.");
            }

            if (is_string($unitId) && $unitId !== '' && $roomType) {
                $belongs = $roomType->units()->whereKey($unitId)->exists();

                if (! $belongs) {
                    $validator->errors()->add('room_unit_id', 'That room number does not belong to the selected room type.');
                }
            }
        });
    }

    /**
     * @return array{
     *     room_type_id: string,
     *     room_unit_id?: string|null,
     *     checkin_date: string,
     *     checkout_date: string,
     *     guests: int,
     *     guest_name: string,
     *     guest_email: string,
     *     guest_phone?: string|null
     * }
     */
    public function bookingData(): array
    {
        return [
            'room_type_id' => (string) $this->string('room_type_id'),
            'room_unit_id' => $this->filled('room_unit_id') ? (string) $this->string('room_unit_id') : null,
            'checkin_date' => (string) $this->date('checkin_date')?->toDateString(),
            'checkout_date' => (string) $this->date('checkout_date')?->toDateString(),
            'guests' => $this->integer('guests'),
            'guest_name' => (string) $this->string('guest_name'),
            'guest_email' => (string) $this->string('guest_email'),
            'guest_phone' => $this->filled('guest_phone') ? (string) $this->string('guest_phone') : null,
        ];
    }
}
