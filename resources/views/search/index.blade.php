@extends('layouts.app')

@section('title', 'Search availability')
@section('crumb', 'Find rooms')

@section('content')
<x-hero-banner
    title="Where to next?"
    subtitle="Search real-time availability by city and dates."
/>

<x-filter-toolbar method="POST" :action="route('search.run')" class="mb-2">
    @include('search.partials.filters')
</x-filter-toolbar>

@include('partials.form-errors')

@if (! $errors->any())
    <div class="mb-3"></div>
@endif

@if ($payload)
    <div class="d-flex align-items-center gap-2 mb-3 text-muted">
        <span class="hb-chip hb-chip-green"><i class="bi bi-geo-alt"></i>{{ $payload['meta']['city'] }}</span>
        <span class="hb-chip hb-chip-amber"><i class="bi bi-moon-stars"></i>{{ $payload['meta']['nights'] }} {{ Str::plural('night', $payload['meta']['nights']) }}</span>
        <span class="hb-chip" style="background:#e2eef0;color:#0f766e"><i class="bi bi-people"></i>{{ $payload['meta']['guests'] }} {{ Str::plural('guest', $payload['meta']['guests']) }}</span>
    </div>

    @forelse ($payload['results'] as $result)
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <span class="hb-thumb hb-grad-indigo">{{ strtoupper(mb_substr($result['hotel']['name'], 0, 1)) }}</span>
                    <div>
                        <div class="fw-semibold">{{ $result['hotel']['name'] }}</div>
                        <div class="text-muted small">{{ $result['hotel']['city'] }}, {{ $result['hotel']['country'] }}</div>
                    </div>
                </div>
                <span class="hb-star">{{ str_repeat('★', $result['hotel']['rating']) }}</span>
            </div>
            <div class="table-responsive">
                <table class="table hb-table mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Room type</th>
                            <th>Price / night</th>
                            <th>Max guests</th>
                            <th>Available rooms</th>
                            <th class="text-end">Total · {{ $result['nights'] }} {{ Str::plural('night', $result['nights']) }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($result['rooms'] as $room)
                            <tr>
                                <td class="fw-semibold">{{ $room['name'] }}</td>
                                <td>${{ number_format($room['price_per_night'], 2) }}</td>
                                <td><i class="bi bi-people text-muted me-1"></i>{{ $room['max_occupancy'] }}</td>
                                <td>
                                    <span class="hb-chip hb-chip-green">{{ $room['available_units'] }} left</span>
                                    <div class="text-muted small mt-1">{{ implode(', ', $room['available_room_numbers']) }}</div>
                                </td>
                                <td class="text-end fw-bold" style="color:var(--hb-primary)">${{ number_format($room['total_price'], 2) }}</td>
                                <td class="text-end">
                                    <a href="{{ route('bookings.index', ['room_type_id' => $room['id'], 'checkin_date' => $payload['meta']['checkin_date'], 'checkout_date' => $payload['meta']['checkout_date'], 'guests' => $payload['meta']['guests']]) }}" class="btn btn-sm btn-primary">Book</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @empty
        <div class="card"><x-empty-state icon="emoji-frown" message="No rooms available for these dates. Try different dates or a nearby city." /></div>
    @endforelse
@else
    <div class="card"><x-empty-state icon="search" message="Enter a city and dates to see available rooms." /></div>
@endif
@endsection
