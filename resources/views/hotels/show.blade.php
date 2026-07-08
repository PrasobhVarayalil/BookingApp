@extends('layouts.app')

@section('title', $hotel->name)
@section('crumb', 'Hotels / Details')

@section('actions')
    <x-back-link :href="route('hotels.index')" label="Back to hotels" />
@endsection

@section('content')
<div class="row g-3">
    <div class="col-lg-8">
        <x-detail-card title="Hotel details" icon="buildings">
            <div class="hb-detail-head mb-3">
                <span class="hb-thumb hb-grad-indigo">{{ strtoupper(mb_substr($hotel->name, 0, 1)) }}</span>
                <div>
                    <div class="h5 mb-1 fw-bold">{{ $hotel->name }}</div>
                    <div class="hb-star">{{ str_repeat('★', $hotel->rating) }}<span class="text-muted">{{ str_repeat('☆', 5 - $hotel->rating) }}</span></div>
                </div>
            </div>
            <x-detail-row label="City">{{ $hotel->city }}</x-detail-row>
            <x-detail-row label="Country">{{ $hotel->country }}</x-detail-row>
            <x-detail-row label="Rating">{{ $hotel->rating }} / 5</x-detail-row>
        </x-detail-card>

        <x-detail-card title="Room types" icon="door-open">
            <x-slot:tools>
                <a href="{{ route('rooms.index', ['hotel' => $hotel->id]) }}" class="btn btn-sm btn-soft">Manage</a>
            </x-slot:tools>
            <div class="table-responsive">
                <table class="table hb-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Room type</th>
                            <th>Price / night</th>
                            <th>Max guests</th>
                            <th>Units</th>
                            <th>Bookings</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($hotel->roomTypes as $roomType)
                            <tr>
                                <td class="fw-semibold">{{ $roomType->name }}</td>
                                <td>${{ number_format((float) $roomType->price_per_night, 2) }}</td>
                                <td>{{ $roomType->max_occupancy }}</td>
                                <td><span class="badge text-bg-light">{{ $roomType->units_count }}</span></td>
                                <td><span class="badge text-bg-light">{{ $roomType->bookings_count }}</span></td>
                                <td class="text-end">
                                    <a href="{{ route('rooms.show', $roomType) }}" class="btn btn-sm btn-soft"><i class="bi bi-eye"></i></a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6"><x-empty-state icon="door-open" message="No room types for this hotel yet." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-detail-card>
    </div>

    <div class="col-lg-4">
        <div class="row g-2 mb-1">
            <div class="col-6"><x-stat-tile icon="door-open" :value="$roomsCount" label="Room types" tone="teal" /></div>
            <div class="col-6"><x-stat-tile icon="grid-3x3-gap" :value="$unitsCount" label="Units" tone="sky" /></div>
            <div class="col-12"><x-stat-tile icon="calendar-check" :value="$bookingsCount" label="Bookings" tone="amber" /></div>
        </div>

        <x-audit-info :model="$hotel" />
    </div>
</div>
@endsection
