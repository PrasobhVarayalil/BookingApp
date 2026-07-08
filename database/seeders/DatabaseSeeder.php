<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Admin', 'password' => Hash::make('password')],
        );

        foreach ($this->hotels() as [$name, $city, $country, $rating, $rooms]) {
            $hotel = Hotel::create(compact('name', 'city', 'country', 'rating'));

            foreach ($rooms as [$roomName, $price, $occupancy, $total]) {
                $room = $hotel->rooms()->create([
                    'name' => $roomName,
                    'price_per_night' => $price,
                    'max_occupancy' => $occupancy,
                    'total_rooms' => $total,
                ]);

                $this->seedBookings($room);
            }
        }
    }

    private function seedBookings(Room $room): void
    {
        // Book a couple of upcoming windows so search results differ by date.
        $windows = $room->total_rooms > 4 ? 3 : 1;

        for ($i = 0; $i < $windows; $i++) {
            $checkin = Carbon::today()->addDays(($i * 5) + 2);
            $checkout = $checkin->copy()->addDays(rand(2, 4));

            Booking::create([
                'room_id' => $room->id,
                'checkin_date' => $checkin->toDateString(),
                'checkout_date' => $checkout->toDateString(),
                'guests' => min(2, $room->max_occupancy),
                'status' => Booking::STATUS_CONFIRMED,
                'total_price' => round((float) $room->price_per_night * $checkin->diffInDays($checkout), 2),
            ]);
        }
    }

    /**
     * @return list<array{0: string, 1: string, 2: string, 3: int, 4: list<array{0: string, 1: float, 2: int, 3: int}>}>
     */
    private function hotels(): array
    {
        return [
            ['Burj Marina Resort', 'Dubai', 'United Arab Emirates', 5, [
                ['Deluxe King', 220.00, 2, 6],
                ['Executive Suite', 450.00, 3, 3],
                ['Family Room', 320.00, 4, 4],
            ]],
            ['Palm Stay Hotel', 'Dubai', 'United Arab Emirates', 4, [
                ['Standard Twin', 120.00, 2, 8],
                ['Deluxe King', 180.00, 2, 5],
            ]],
            ['Thames View Inn', 'London', 'United Kingdom', 4, [
                ['Standard Twin', 140.00, 2, 6],
                ['Executive Suite', 380.00, 3, 2],
            ]],
            ['Seine Boutique Hotel', 'Paris', 'France', 4, [
                ['Standard Twin', 130.00, 2, 7],
                ['Deluxe King', 200.00, 2, 4],
            ]],
            ['Shinjuku Sky Hotel', 'Tokyo', 'Japan', 4, [
                ['Standard Twin', 110.00, 2, 9],
                ['Family Room', 290.00, 4, 3],
            ]],
            ['Gateway Grand', 'Mumbai', 'India', 5, [
                ['Deluxe King', 160.00, 2, 5],
                ['Executive Suite', 300.00, 3, 2],
            ]],
        ];
    }
}
