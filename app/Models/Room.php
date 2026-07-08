<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends AppModel
{
    protected $fillable = [
        'hotel_id',
        'name',
        'price_per_night',
        'max_occupancy',
        'total_rooms',
    ];

    protected function casts(): array
    {
        return [
            'price_per_night' => 'decimal:2',
            'max_occupancy' => 'integer',
            'total_rooms' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Hotel, $this>
     */
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    /**
     * @return HasMany<Booking, $this>
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
