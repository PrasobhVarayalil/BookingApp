<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoomType extends AppModel
{
    protected $table = 'room_types';

    protected $fillable = [
        'hotel_id',
        'name',
        'price_per_night',
        'max_occupancy',
    ];

    protected function casts(): array
    {
        return [
            'price_per_night' => 'decimal:2',
            'max_occupancy' => 'integer',
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
     * @return HasMany<RoomUnit, $this>
     */
    public function units(): HasMany
    {
        return $this->hasMany(RoomUnit::class);
    }

    /**
     * @return HasMany<Booking, $this>
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
