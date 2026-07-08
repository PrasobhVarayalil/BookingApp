<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends AppModel
{
    public const string STATUS_CONFIRMED = 'confirmed';

    public const string STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'booking_reference',
        'room_type_id',
        'room_unit_id',
        'checkin_date',
        'checkout_date',
        'guests',
        'guest_name',
        'guest_email',
        'guest_phone',
        'status',
        'total_price',
    ];

    protected function casts(): array
    {
        return [
            'checkin_date' => 'date',
            'checkout_date' => 'date',
            'guests' => 'integer',
            'total_price' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<RoomType, $this>
     */
    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    /**
     * @return BelongsTo<RoomUnit, $this>
     */
    public function roomUnit(): BelongsTo
    {
        return $this->belongsTo(RoomUnit::class);
    }
}
