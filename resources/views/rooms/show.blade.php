@extends('layouts.app')

@section('title', $roomType->name)
@section('crumb', 'Room Types / Details')

@section('actions')
    <x-back-link :href="route('rooms.index')" label="Back to room types" />
@endsection

@section('content')
<div class="row g-3">
    <div class="col-lg-8">
        <x-detail-card title="Room type details" icon="door-open">
            <div class="hb-detail-head mb-3">
                <span class="hb-thumb hb-grad-teal"><i class="bi bi-door-open-fill"></i></span>
                <div>
                    <div class="h5 mb-1 fw-bold">{{ $roomType->name }}</div>
                    <a href="{{ route('hotels.show', $roomType->hotel) }}" class="text-muted small text-decoration-none">
                        <i class="bi bi-buildings me-1"></i>{{ $roomType->hotel->name }} · {{ $roomType->hotel->city }}
                    </a>
                </div>
            </div>
            <x-detail-row label="Price / night">${{ number_format((float) $roomType->price_per_night, 2) }}</x-detail-row>
            <x-detail-row label="Max guests">{{ $roomType->max_occupancy }}</x-detail-row>
            <x-detail-row label="Total units">{{ $roomType->units->count() }}</x-detail-row>
        </x-detail-card>

        <x-detail-card title="Units" icon="grid-3x3-gap">
            <div class="table-responsive">
                <table class="table hb-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Room number</th>
                            <th>Status</th>
                            <th>Bookings</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($roomType->units as $unit)
                            <tr>
                                <td class="fw-semibold">{{ $unit->room_number }}</td>
                                <td>
                                    @if ($unit->status === \App\Models\RoomUnit::STATUS_AVAILABLE)
                                        <span class="hb-chip hb-chip-green"><i class="bi bi-check-circle"></i>Available</span>
                                    @else
                                        <span class="hb-chip hb-chip-amber"><i class="bi bi-tools"></i>{{ ucfirst($unit->status) }}</span>
                                    @endif
                                </td>
                                <td><span class="badge text-bg-light">{{ $unit->bookings_count }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="3"><x-empty-state icon="grid-3x3-gap" message="No units defined." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-detail-card>

        <x-detail-card title="Recent bookings" icon="calendar-check">
            <div class="table-responsive">
                <table class="table hb-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Guest</th>
                            <th>Stay</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($roomType->bookings as $booking)
                            <tr>
                                <td class="fw-semibold">{{ $booking->booking_reference }}</td>
                                <td>{{ $booking->guest_name }}</td>
                                <td>{{ $booking->checkin_date->format('M d') }} → {{ $booking->checkout_date->format('M d, Y') }}</td>
                                <td>
                                    @if ($booking->status === \App\Models\Booking::STATUS_CONFIRMED)
                                        <span class="hb-chip hb-chip-green">Confirmed</span>
                                    @else
                                        <span class="hb-chip hb-chip-amber">Cancelled</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('bookings.show', $booking) }}" class="btn btn-sm btn-soft"><i class="bi bi-eye"></i></a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><x-empty-state icon="calendar-x" message="No bookings for this room type." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-detail-card>
    </div>

    <div class="col-lg-4">
        <x-audit-info :model="$roomType" />
    </div>
</div>
@endsection
