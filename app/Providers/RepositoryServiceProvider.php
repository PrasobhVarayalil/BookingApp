<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\Contracts\BookingRepositoryInterface;
use App\Repositories\Contracts\HotelRepositoryInterface;
use App\Repositories\Contracts\RoomTypeRepositoryInterface;
use App\Repositories\Contracts\RoomUnitRepositoryInterface;
use App\Repositories\Eloquent\EloquentBookingRepository;
use App\Repositories\Eloquent\EloquentHotelRepository;
use App\Repositories\Eloquent\EloquentRoomTypeRepository;
use App\Repositories\Eloquent\EloquentRoomUnitRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, class-string>
     */
    private array $repositories = [
        HotelRepositoryInterface::class => EloquentHotelRepository::class,
        RoomTypeRepositoryInterface::class => EloquentRoomTypeRepository::class,
        RoomUnitRepositoryInterface::class => EloquentRoomUnitRepository::class,
        BookingRepositoryInterface::class => EloquentBookingRepository::class,
    ];

    public function register(): void
    {
        foreach ($this->repositories as $contract => $implementation) {
            $this->app->bind($contract, $implementation);
        }
    }
}
