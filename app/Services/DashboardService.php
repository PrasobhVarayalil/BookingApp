<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Booking;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class DashboardService
{
    private const TREND_MONTHS = 6;

    private const TOP_LIMIT = 5;

    /**
     * @return array<string, mixed>
     */
    public function charts(): array
    {
        $bookings = Booking::query()
            ->with('roomType.hotel')
            ->get(['id', 'room_type_id', 'status', 'total_price', 'checkin_date', 'created_at']);

        return [
            'trend' => $this->monthlyTrend($bookings),
            'status' => $this->statusBreakdown($bookings),
            'topHotels' => $this->topHotels($bookings),
            'byCity' => $this->bookingsByCity($bookings),
        ];
    }

    /**
     * @param  Collection<int, Booking>  $bookings
     * @return array{labels: list<string>, bookings: list<int>, revenue: list<float>}
     */
    private function monthlyTrend(Collection $bookings): array
    {
        $months = collect(range(self::TREND_MONTHS - 1, 0))
            ->map(fn (int $i) => Carbon::now()->startOfMonth()->subMonths($i));

        $labels = [];
        $counts = [];
        $revenue = [];

        foreach ($months as $month) {
            $key = $month->format('Y-m');
            $inMonth = $bookings->filter(fn (Booking $b) => $b->created_at?->format('Y-m') === $key);

            $labels[] = $month->format('M Y');
            $counts[] = $inMonth->count();
            $revenue[] = round((float) $inMonth
                ->where('status', Booking::STATUS_CONFIRMED)
                ->sum(fn (Booking $b) => (float) $b->total_price), 2);
        }

        return ['labels' => $labels, 'bookings' => $counts, 'revenue' => $revenue];
    }

    /**
     * @param  Collection<int, Booking>  $bookings
     * @return array{confirmed: int, cancelled: int}
     */
    private function statusBreakdown(Collection $bookings): array
    {
        return [
            'confirmed' => $bookings->where('status', Booking::STATUS_CONFIRMED)->count(),
            'cancelled' => $bookings->where('status', Booking::STATUS_CANCELLED)->count(),
        ];
    }

    /**
     * @param  Collection<int, Booking>  $bookings
     * @return array{labels: list<string>, data: list<int>}
     */
    private function topHotels(Collection $bookings): array
    {
        $ranked = $bookings
            ->where('status', Booking::STATUS_CONFIRMED)
            ->groupBy(fn (Booking $b) => $b->roomType?->hotel?->name ?? 'Unknown')
            ->map(fn (Collection $group) => $group->count())
            ->sortDesc()
            ->take(self::TOP_LIMIT);

        return [
            'labels' => $ranked->keys()->all(),
            'data' => $ranked->values()->all(),
        ];
    }

    /**
     * @param  Collection<int, Booking>  $bookings
     * @return array{labels: list<string>, data: list<int>}
     */
    private function bookingsByCity(Collection $bookings): array
    {
        $byCity = $bookings
            ->where('status', Booking::STATUS_CONFIRMED)
            ->groupBy(fn (Booking $b) => $b->roomType?->hotel?->city ?? 'Unknown')
            ->map(fn (Collection $group) => $group->count())
            ->sortDesc()
            ->take(self::TOP_LIMIT);

        return [
            'labels' => $byCity->keys()->all(),
            'data' => $byCity->values()->all(),
        ];
    }

    public function occupancyRate(int $totalUnits): float
    {
        if ($totalUnits === 0) {
            return 0.0;
        }

        $today = Carbon::today()->toDateString();

        $occupied = Booking::query()
            ->where('status', Booking::STATUS_CONFIRMED)
            ->whereDate('checkin_date', '<=', $today)
            ->whereDate('checkout_date', '>', $today)
            ->distinct('room_unit_id')
            ->count('room_unit_id');

        return round(($occupied / $totalUnits) * 100, 1);
    }
}
