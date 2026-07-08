<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Hotel extends AppModel
{
    protected $fillable = [
        'name',
        'city',
        'country',
        'rating',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
        ];
    }

    /**
     * @return HasMany<Room, $this>
     */
    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }
}
