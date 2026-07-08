@extends('layouts.app')

@section('title', $booking->booking_reference)
@section('crumb', 'Bookings / Details')

@section('actions')
    <div class="d-flex gap-2">
        <x-back-link :href="route('bookings.index')" label="Back to bookings" />
        @if ($booking->status === \App\Models\Booking::STATUS_CONFIRMED)
            <x-delete-form :action="route('bookings.destroy', $booking)" confirm="Cancel this booking?" label="Cancel booking" icon="x-lg" />
        @endif
    </div>
@endsection

@section('content')
<div class="row g-3">
    <div class="col-lg-8">
        <x-detail-card title="Reservation" icon="calendar-check">
            <div class="hb-detail-head mb-3">
                <span class="hb-thumb hb-grad-sky"><i class="bi bi-receipt"></i></span>
                <div>
                    <div class="h5 mb-1 fw-bold">{{ $booking->booking_reference }}</div>
                    @if ($booking->status === \App\Models\Booking::STATUS_CONFIRMED)
                        <span class="hb-chip hb-chip-green"><i class="bi bi-check-circle"></i>Confirmed</span>
                    @else
                        <span class="hb-chip hb-chip-amber"><i class="bi bi-x-circle"></i>Cancelled</span>
                    @endif
                </div>
            </div>
            <x-detail-row label="Hotel">
                <a href="{{ route('hotels.show', $booking->roomType->hotel) }}" class="text-decoration-none">{{ $booking->roomType->hotel->name }}</a>
                <span class="text-muted small">{{ $booking->roomType->hotel->city }}, {{ $booking->roomType->hotel->country }}</span>
            </x-detail-row>
            <x-detail-row label="Room type">
                <a href="{{ route('rooms.show', $booking->roomType) }}" class="text-decoration-none">{{ $booking->roomType->name }}</a>
            </x-detail-row>
            <x-detail-row label="Room number">{{ $booking->roomUnit?->room_number ?? '—' }}</x-detail-row>
            <x-detail-row label="Check-in">{{ $booking->checkin_date->format('l, M d, Y') }}</x-detail-row>
            <x-detail-row label="Check-out">{{ $booking->checkout_date->format('l, M d, Y') }}</x-detail-row>
            <x-detail-row label="Nights">{{ $nights }} {{ Str::plural('night', $nights) }}</x-detail-row>
            <x-detail-row label="Guests">{{ $booking->guests }} {{ Str::plural('guest', $booking->guests) }}</x-detail-row>
            <x-detail-row label="Total price">
                <span class="fw-bold" style="color:var(--hb-primary)">${{ number_format((float) $booking->total_price, 2) }}</span>
            </x-detail-row>
        </x-detail-card>

        <x-detail-card title="Guest details" icon="person-badge">
            <x-detail-row label="Name">{{ $booking->guest_name }}</x-detail-row>
            <x-detail-row label="Email"><a href="mailto:{{ $booking->guest_email }}">{{ $booking->guest_email }}</a></x-detail-row>
            <x-detail-row label="Phone">{{ $booking->guest_phone ?? '—' }}</x-detail-row>
        </x-detail-card>
    </div>

    <div class="col-lg-4">
        <x-audit-info :model="$booking" />
    </div>
</div>
@endsection
