@extends('layouts.app')

@section('title', 'Search availability')
@section('crumb', 'Find rooms')

@section('content')
<div class="card hb-hero mb-4">
    <div class="card-body p-4 position-relative" style="z-index:1">
        <h2 class="h5 fw-bold mb-1"><i class="bi bi-compass me-2"></i>Where to next?</h2>
        <p class="opacity-75 mb-3">Search real-time availability by city and dates.</p>
        <form method="POST" action="{{ route('search.run') }}" class="row g-2">
            @csrf
            <div class="col-md-4">
                <div class="input-icon">
                    <i class="bi bi-geo-alt"></i>
                    <input type="text" name="city" value="{{ old('city', $filters['city'] ?? '') }}" class="form-control @error('city') is-invalid @enderror" placeholder="City" required>
                </div>
                @error('city')<div class="text-warning small mt-1">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3 col-6">
                <input type="date" name="checkin_date" value="{{ old('checkin_date', $filters['checkin_date'] ?? '') }}" class="form-control @error('checkin_date') is-invalid @enderror" required>
                @error('checkin_date')<div class="text-warning small mt-1">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3 col-6">
                <input type="date" name="checkout_date" value="{{ old('checkout_date', $filters['checkout_date'] ?? '') }}" class="form-control @error('checkout_date') is-invalid @enderror" required>
                @error('checkout_date')<div class="text-warning small mt-1">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-1 col-6">
                <input type="number" name="guests" min="1" value="{{ old('guests', $filters['guests'] ?? 1) }}" class="form-control @error('guests') is-invalid @enderror" title="Guests" required>
            </div>
            <div class="col-md-1 col-6 d-grid">
                <button class="btn btn-light fw-semibold"><i class="bi bi-search"></i></button>
            </div>
        </form>
    </div>
</div>

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
                            <th>Room</th>
                            <th>Price / night</th>
                            <th>Max guests</th>
                            <th>Availability</th>
                            <th class="text-end">Total · {{ $result['nights'] }} {{ Str::plural('night', $result['nights']) }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($result['rooms'] as $room)
                            <tr>
                                <td class="fw-semibold">{{ $room['name'] }}</td>
                                <td>${{ number_format($room['price_per_night'], 2) }}</td>
                                <td><i class="bi bi-people text-muted me-1"></i>{{ $room['max_occupancy'] }}</td>
                                <td><span class="hb-chip hb-chip-green">{{ $room['available_units'] }} left</span></td>
                                <td class="text-end fw-bold" style="color:var(--hb-primary)">${{ number_format($room['total_price'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @empty
        <div class="card"><div class="hb-empty"><i class="bi bi-emoji-frown"></i>No rooms available for these dates. Try different dates or a nearby city.</div></div>
    @endforelse
@else
    <div class="card"><div class="hb-empty"><i class="bi bi-search"></i>Enter a city and dates to see available rooms.</div></div>
@endif
@endsection
