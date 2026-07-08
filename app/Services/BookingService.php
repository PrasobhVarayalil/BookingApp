<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\RoomNotAvailableException;
use App\Models\Booking;
use App\Models\RoomType;
use App\Models\RoomUnit;
use App\Repositories\Contracts\BookingRepositoryInterface;
use App\Repositories\Contracts\RoomTypeRepositoryInterface;
use App\Repositories\Contracts\RoomUnitRepositoryInterface;
use App\Services\Search\SearchResultCache;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookingService
{
    public function __construct(
        private readonly BookingRepositoryInterface $bookings,
        private readonly RoomTypeRepositoryInterface $roomTypes,
        private readonly RoomUnitRepositoryInterface $roomUnits,
        private readonly SearchResultCache $searchCache,
    ) {}

    /**
     * @return LengthAwarePaginator<int, Booking>
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->bookings->paginateWithRoomAndHotel($perPage);
    }

    public function count(): int
    {
        return $this->bookings->count();
    }

    /**
     * Units of a type that are free for the entire requested stay.
     *
     * @return Collection<int, RoomUnit>
     */
    public function availableUnitsForStay(RoomType $roomType, Carbon $checkin, Carbon $checkout): Collection
    {
        return $this->roomUnits->availableForStay(
            $roomType->id,
            $checkin->toDateString(),
            $checkout->toDateString(),
        );
    }

    public function availableUnitCount(RoomType $roomType, Carbon $checkin, Carbon $checkout): int
    {
        return $this->availableUnitsForStay($roomType, $checkin, $checkout)->count();
    }

    /**
     * @return Collection<int, RoomUnit>
     */
    public function listAvailableUnits(string $roomTypeId, string $checkin, string $checkout): Collection
    {
        return $this->roomUnits->availableForStay($roomTypeId, $checkin, $checkout);
    }

    /**
     * @param  array{
     *     room_type_id: string,
     *     room_unit_id?: string|null,
     *     checkin_date: string,
     *     checkout_date: string,
     *     guests: int,
     *     guest_name: string,
     *     guest_email: string,
     *     guest_phone?: string|null
     * }  $data
     *
     * @throws RoomNotAvailableException
     */
    public function create(array $data): Booking
    {
        $checkin = Carbon::parse($data['checkin_date'])->startOfDay();
        $checkout = Carbon::parse($data['checkout_date'])->startOfDay();

        $booking = DB::transaction(function () use ($data, $checkin, $checkout): Booking {
            $roomType = $this->roomTypes->findForUpdate($data['room_type_id']);

            if (! $roomType instanceof RoomType) {
                throw RoomNotAvailableException::forDates($checkin->toDateString(), $checkout->toDateString());
            }

            if ((int) $data['guests'] > $roomType->max_occupancy) {
                throw RoomNotAvailableException::forDates($checkin->toDateString(), $checkout->toDateString());
            }

            $unit = $this->resolveUnit($roomType, $checkin, $checkout, $data['room_unit_id'] ?? null);

            return $this->bookings->create([
                'booking_reference' => $this->reference(),
                'room_type_id' => $roomType->id,
                'room_unit_id' => $unit->id,
                'checkin_date' => $checkin->toDateString(),
                'checkout_date' => $checkout->toDateString(),
                'guests' => (int) $data['guests'],
                'guest_name' => $data['guest_name'],
                'guest_email' => $data['guest_email'],
                'guest_phone' => $data['guest_phone'] ?? null,
                'status' => Booking::STATUS_CONFIRMED,
                'total_price' => round((float) $roomType->price_per_night * $checkin->diffInDays($checkout), 2),
            ]);
        });

        $this->searchCache->bump();

        return $booking->load(['roomType.hotel', 'roomUnit']);
    }

    public function cancel(Booking $booking): void
    {
        if ($booking->status === Booking::STATUS_CANCELLED) {
            return;
        }

        $this->bookings->cancel($booking);

        $this->searchCache->bump();
    }

    private function resolveUnit(RoomType $roomType, Carbon $checkin, Carbon $checkout, ?string $requestedUnitId): RoomUnit
    {
        $available = $this->availableUnitsForStay($roomType, $checkin, $checkout);

        if ($available->isEmpty()) {
            throw RoomNotAvailableException::forDates($checkin->toDateString(), $checkout->toDateString());
        }

        if ($requestedUnitId !== null && $requestedUnitId !== '') {
            $chosen = $available->firstWhere('id', $requestedUnitId);

            if (! $chosen instanceof RoomUnit) {
                throw RoomNotAvailableException::forDates($checkin->toDateString(), $checkout->toDateString());
            }

            $this->roomUnits->findForUpdate($chosen->id);

            return $chosen;
        }

        $unit = $available->first();

        if (! $unit instanceof RoomUnit) {
            throw RoomNotAvailableException::forDates($checkin->toDateString(), $checkout->toDateString());
        }

        $this->roomUnits->findForUpdate($unit->id);

        return $unit;
    }

    private function reference(): string
    {
        return 'BK-'.now()->format('Ymd').'-'.strtoupper(Str::random(4));
    }
}
