<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->countries() as $name => [$code, $cities]) {
            $country = Country::updateOrCreate(['name' => $name], ['code' => $code]);

            foreach ($cities as $city) {
                $country->cities()->updateOrCreate(['name' => $city]);
            }
        }
    }

    /**
     * @return array<string, array{0: string, 1: list<string>}>
     */
    private function countries(): array
    {
        return [
            'United Arab Emirates' => ['AE', ['Dubai', 'Abu Dhabi', 'Sharjah']],
            'United Kingdom' => ['GB', ['London', 'Manchester', 'Edinburgh', 'Birmingham']],
            'France' => ['FR', ['Paris', 'Nice', 'Lyon', 'Marseille']],
            'Japan' => ['JP', ['Tokyo', 'Osaka', 'Kyoto', 'Sapporo']],
            'India' => ['IN', ['Mumbai', 'Delhi', 'Bengaluru', 'Chennai', 'Goa']],
            'United States' => ['US', ['New York', 'Los Angeles', 'Chicago', 'Miami', 'San Francisco']],
            'Singapore' => ['SG', ['Singapore']],
            'Italy' => ['IT', ['Rome', 'Milan', 'Venice', 'Florence']],
            'Spain' => ['ES', ['Madrid', 'Barcelona', 'Seville', 'Valencia']],
            'Australia' => ['AU', ['Sydney', 'Melbourne', 'Brisbane', 'Perth']],
        ];
    }
}
