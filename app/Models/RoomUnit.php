<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoomUnit extends AppModel
{
    public const string STATUS_AVAILABLE = 'available';

    public const string STATUS_MAINTENANCE = 'maintenance';

    public const string STATUS_BLOCKED = 'blocked';

    protected $fillable = [
        'room_type_id',
        'room_number',
        'status',
    ];

    /**
     * @return BelongsTo<RoomType, $this>
     */
    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    /**
     * @return HasMany<Booking, $this>
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
