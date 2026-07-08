<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Hotel;
use App\Models\RoomType;
use App\Models\RoomUnit;
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
            ['email' => 'admin@terrastay.com'],
            ['name' => 'Admin', 'password' => Hash::make('password')],
        );

        $this->call(LocationSeeder::class);

        foreach ($this->hotels() as [$name, $city, $country, $rating, $roomTypes]) {
            $hotel = Hotel::create(compact('name', 'city', 'country', 'rating'));

            foreach ($roomTypes as [$typeName, $price, $occupancy, $numbers]) {
                $roomType = $hotel->roomTypes()->create([
                    'name' => $typeName,
                    'price_per_night' => $price,
                    'max_occupancy' => $occupancy,
                ]);

                foreach ($numbers as $number) {
                    $roomType->units()->create([
                        'room_number' => $number,
                        'status' => RoomUnit::STATUS_AVAILABLE,
                    ]);
                }

                $this->seedBookings($roomType);
            }
        }
    }

    private function seedBookings(RoomType $roomType): void
    {
        $units = $roomType->units()->get();
        $windows = min(3, $units->count());

        for ($i = 0; $i < $windows; $i++) {
            $unit = $units[$i] ?? null;

            if (! $unit instanceof RoomUnit) {
                break;
            }

            $checkin = Carbon::today()->addDays(($i * 5) + 2);
            $checkout = $checkin->copy()->addDays(rand(2, 4));

            Booking::create([
                'booking_reference' => 'BK-'.now()->format('Ymd').'-'.strtoupper(substr(md5((string) $i.$unit->id), 0, 4)),
                'room_type_id' => $roomType->id,
                'room_unit_id' => $unit->id,
                'checkin_date' => $checkin->toDateString(),
                'checkout_date' => $checkout->toDateString(),
                'guests' => min(2, $roomType->max_occupancy),
                'guest_name' => 'Guest '.($i + 1),
                'guest_email' => "guest{$i}@example.com",
                'status' => Booking::STATUS_CONFIRMED,
                'total_price' => round((float) $roomType->price_per_night * $checkin->diffInDays($checkout), 2),
            ]);
        }
    }

    /**
     * @return list<array{0: string, 1: string, 2: string, 3: int, 4: list<array{0: string, 1: float, 2: int, 3: list<string>}>}>
     */
    private function hotels(): array
    {
        return [
            ['Burj Marina Resort', 'Dubai', 'United Arab Emirates', 5, [
                ['Deluxe King', 220.00, 2, ['101', '102', '103', '104', '105', '106']],
                ['Executive Suite', 450.00, 3, ['201', '202', '203']],
                ['Family Room', 320.00, 4, ['301', '302', '303', '304']],
            ]],
            ['Palm Stay Hotel', 'Dubai', 'United Arab Emirates', 4, [
                ['Standard Twin', 120.00, 2, ['110', '111', '112', '113', '114', '115', '116', '117']],
                ['Deluxe King', 180.00, 2, ['210', '211', '212', '213', '214']],
            ]],
            ['Thames View Inn', 'London', 'United Kingdom', 4, [
                ['Standard Twin', 140.00, 2, ['11', '12', '13', '14', '15', '16']],
                ['Executive Suite', 380.00, 3, ['21', '22']],
            ]],
            ['Seine Boutique Hotel', 'Paris', 'France', 4, [
                ['Standard Twin', 130.00, 2, ['31', '32', '33', '34', '35', '36', '37']],
                ['Deluxe King', 200.00, 2, ['41', '42', '43', '44']],
            ]],
            ['Shinjuku Sky Hotel', 'Tokyo', 'Japan', 4, [
                ['Standard Twin', 110.00, 2, ['501', '502', '503', '504', '505', '506', '507', '508', '509']],
                ['Family Room', 290.00, 4, ['601', '602', '603']],
            ]],
            ['Gateway Grand', 'Mumbai', 'India', 5, [
                ['Deluxe King', 160.00, 2, ['701', '702', '703', '704', '705']],
                ['Executive Suite', 300.00, 3, ['801', '802']],
            ]],
        ];
    }
}
