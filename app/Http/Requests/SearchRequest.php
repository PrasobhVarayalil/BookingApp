<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchRequest extends FormRequest
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
            'city' => ['required', 'string', 'max:100'],
            'checkin_date' => ['required', 'date', 'after_or_equal:today'],
            'checkout_date' => ['required', 'date', 'after:checkin_date'],
            'guests' => ['required', 'integer', 'min:1'],
        ];
    }

    /**
     * @return array{city: string, checkin_date: string, checkout_date: string, guests: int}
     */
    public function params(): array
    {
        return [
            'city' => (string) $this->string('city'),
            'checkin_date' => (string) $this->date('checkin_date')?->toDateString(),
            'checkout_date' => (string) $this->date('checkout_date')?->toDateString(),
            'guests' => $this->integer('guests'),
        ];
    }
}
