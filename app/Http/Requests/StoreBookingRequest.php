<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Room;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
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
            'room_id' => ['required', 'uuid', 'exists:rooms,id'],
            'checkin_date' => ['required', 'date', 'after_or_equal:today'],
            'checkout_date' => ['required', 'date', 'after:checkin_date'],
            'guests' => ['required', 'integer', 'min:1'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $roomId = $this->input('room_id');
            $guests = $this->integer('guests');

            if (! is_string($roomId) || $guests < 1) {
                return;
            }

            $room = Room::find($roomId);

            if ($room && $guests > $room->max_occupancy) {
                $validator->errors()->add('guests', "This room sleeps at most {$room->max_occupancy} guests.");
            }
        });
    }

    /**
     * @return array{room_id: string, checkin_date: string, checkout_date: string, guests: int}
     */
    public function bookingData(): array
    {
        return [
            'room_id' => (string) $this->string('room_id'),
            'checkin_date' => (string) $this->date('checkin_date')?->toDateString(),
            'checkout_date' => (string) $this->date('checkout_date')?->toDateString(),
            'guests' => $this->integer('guests'),
        ];
    }
}
