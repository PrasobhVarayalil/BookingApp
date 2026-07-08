<?php

declare(strict_types=1);

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\BookingController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\HotelController;
use App\Http\Controllers\Web\RoomController;
use App\Http\Controllers\Web\SearchController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/hotels', [HotelController::class, 'index'])->name('hotels.index');
    Route::post('/hotels', [HotelController::class, 'store'])->name('hotels.store');
    Route::put('/hotels/{hotel}', [HotelController::class, 'update'])->name('hotels.update');
    Route::delete('/hotels/{hotel}', [HotelController::class, 'destroy'])->name('hotels.destroy');

    Route::get('/rooms', [RoomController::class, 'index'])->name('rooms.index');
    Route::post('/rooms', [RoomController::class, 'store'])->name('rooms.store');
    Route::put('/rooms/{room}', [RoomController::class, 'update'])->name('rooms.update');
    Route::delete('/rooms/{room}', [RoomController::class, 'destroy'])->name('rooms.destroy');

    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
    Route::delete('/bookings/{booking}', [BookingController::class, 'destroy'])->name('bookings.destroy');

    Route::get('/search', [SearchController::class, 'index'])->name('search.index');
    Route::post('/search', [SearchController::class, 'search'])->name('search.run');
});
