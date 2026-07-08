<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Hotel;
use App\Repositories\Contracts\HotelRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class EloquentHotelRepository implements HotelRepositoryInterface
{
    public function create(array $attributes): Hotel
    {
        return Hotel::create($attributes);
    }

    public function update(Hotel $hotel, array $attributes): Hotel
    {
        $hotel->update($attributes);

        return $hotel;
    }

    public function delete(Hotel $hotel): void
    {
        $hotel->delete();
    }

    public function paginate(array $filters, int $perPage): LengthAwarePaginator
    {
        return Hotel::query()
            ->withCount('roomTypes')
            ->when(filled($filters['country'] ?? null), fn (Builder $q) => $q->whereRaw('LOWER(country) = ?', [mb_strtolower((string) $filters['country'])]))
            ->when(filled($filters['city'] ?? null), fn (Builder $q) => $q->whereRaw('LOWER(city) = ?', [mb_strtolower((string) $filters['city'])]))
            ->when(filled($filters['rating'] ?? null), fn (Builder $q) => $q->where('rating', '>=', (int) $filters['rating']))
            ->latest()
            ->paginate($perPage);
    }

    public function all(): Collection
    {
        return Hotel::orderBy('name')->get();
    }

    public function availableInCity(string $city, int $guests): Collection
    {
        $forGuests = fn ($q) => $q->where('max_occupancy', '>=', $guests);

        return Hotel::query()
            ->whereRaw('LOWER(city) = ?', [mb_strtolower($city)])
            ->whereHas('roomTypes', $forGuests)
            ->with(['roomTypes' => $forGuests])
            ->orderByDesc('rating')
            ->get();
    }

    public function count(): int
    {
        return Hotel::count();
    }

    public function averageRating(): float
    {
        return round((float) Hotel::avg('rating'), 1);
    }
}
