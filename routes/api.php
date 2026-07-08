<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\HotelController;
use App\Http\Controllers\Api\RoomController;
use App\Http\Controllers\Api\SearchController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:60,1')->group(function (): void {
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

    Route::get('/hotels', [HotelController::class, 'index']);
    Route::get('/search', SearchController::class)->middleware('throttle:30,1');
    Route::get('/room-types/{roomType}/available-units', [BookingController::class, 'availableUnits']);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/hotels', [HotelController::class, 'store']);
        Route::post('/rooms', [RoomController::class, 'store']);
        Route::get('/bookings', [BookingController::class, 'index']);
        Route::post('/bookings', [BookingController::class, 'store']);
        Route::delete('/bookings/{booking}', [BookingController::class, 'destroy']);
    });
});
