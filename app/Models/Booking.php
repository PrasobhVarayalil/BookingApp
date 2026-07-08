<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends AppModel
{
    public const string STATUS_CONFIRMED = 'confirmed';

    public const string STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'room_id',
        'checkin_date',
        'checkout_date',
        'guests',
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
     * @return BelongsTo<Room, $this>
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}
