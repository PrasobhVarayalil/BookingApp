<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\RoomNotAvailableException;
use App\Models\Booking;
use App\Models\Room;
use App\Repositories\Contracts\BookingRepositoryInterface;
use App\Repositories\Contracts\RoomRepositoryInterface;
use App\Services\Search\SearchResultCache;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BookingService
{
    public function __construct(
        private readonly BookingRepositoryInterface $bookings,
        private readonly RoomRepositoryInterface $rooms,
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
     * How many units of a room are free across the whole requested range.
     *
     * Availability is capped by the busiest single night: for each night we
     * count the overlapping confirmed bookings and subtract the worst case
     * from the room's physical inventory.
     *
     * @param  Collection<int, Booking>  $overlapping
     */
    public function availableUnits(int $totalRooms, Collection $overlapping, Carbon $checkin, Carbon $checkout): int
    {
        $busiestNight = 0;

        for ($night = $checkin->copy(); $night->lt($checkout); $night->addDay()) {
            $booked = $overlapping->filter(fn (Booking $b) => $night->gte($b->checkin_date) && $night->lt($b->checkout_date))->count();
            $busiestNight = max($busiestNight, $booked);
        }

        return max(0, $totalRooms - $busiestNight);
    }

    /**
     * @param  array{room_id: string, checkin_date: string, checkout_date: string, guests: int}  $data
     *
     * @throws RoomNotAvailableException
     */
    public function create(array $data): Booking
    {
        $checkin = Carbon::parse($data['checkin_date'])->startOfDay();
        $checkout = Carbon::parse($data['checkout_date'])->startOfDay();

        $booking = DB::transaction(function () use ($data, $checkin, $checkout): Booking {
            $room = $this->rooms->findForUpdate($data['room_id']);

            if (! $room instanceof Room) {
                throw RoomNotAvailableException::forDates($checkin->toDateString(), $checkout->toDateString());
            }

            $overlapping = $this->bookings->overlappingForRoom(
                $room->id,
                $checkin->toDateString(),
                $checkout->toDateString(),
                lock: true,
            );

            if ($this->availableUnits($room->total_rooms, $overlapping, $checkin, $checkout) < 1) {
                throw RoomNotAvailableException::forDates($checkin->toDateString(), $checkout->toDateString());
            }

            return $this->bookings->create([
                'room_id' => $room->id,
                'checkin_date' => $checkin->toDateString(),
                'checkout_date' => $checkout->toDateString(),
                'guests' => (int) $data['guests'],
                'status' => Booking::STATUS_CONFIRMED,
                'total_price' => round((float) $room->price_per_night * $checkin->diffInDays($checkout), 2),
            ]);
        });

        $this->searchCache->bump();

        return $booking;
    }

    public function delete(Booking $booking): void
    {
        $this->bookings->delete($booking);

        $this->searchCache->bump();
    }
}
