@extends('layouts.app')

@section('title', 'Dashboard')
@section('crumb', 'Overview')

@section('content')
<div class="card hb-hero mb-4">
    <div class="card-body d-flex flex-wrap justify-content-between align-items-center p-4 position-relative" style="z-index:1">
        <div>
            <h2 class="h4 fw-bold mb-1">Welcome back, {{ auth()->user()?->name }} 👋</h2>
            <p class="mb-0 opacity-75">Here's a snapshot of your inventory today.</p>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('search.index') }}" class="btn btn-light fw-semibold"><i class="bi bi-search me-1"></i>Search availability</a>
            <a href="{{ route('bookings.index') }}" class="btn btn-outline-light text-white"><i class="bi bi-plus-circle me-1"></i>New booking</a>
        </div>
    </div>
</div>

<div class="row g-3">
    @foreach ([
        ['Total Hotels', $stats['hotels'], 'bi-buildings-fill', 'hb-grad-indigo', 'hotels.index'],
        ['Total Rooms', $stats['rooms'], 'bi-door-open-fill', 'hb-grad-teal', 'rooms.index'],
        ['Total Bookings', $stats['bookings'], 'bi-calendar-check-fill', 'hb-grad-sky', 'bookings.index'],
        ['Avg. Rating', number_format($stats['average_rating'], 1).' ★', 'bi-star-fill', 'hb-grad-amber', 'hotels.index'],
    ] as [$label, $value, $icon, $grad, $route])
        <div class="col-sm-6 col-xl-3">
            <a href="{{ route($route) }}" class="text-decoration-none text-reset">
                <div class="card h-100">
                    <div class="card-body hb-stat">
                        <span class="hb-stat-icon {{ $grad }}"><i class="bi {{ $icon }}"></i></span>
                        <div>
                            <div class="hb-stat-value">{{ $value }}</div>
                            <div class="hb-stat-label">{{ $label }}</div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    @endforeach
</div>

<div class="row g-3 mt-1">
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header">Quick start</div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach ([
                        ['bi-building-add', 'Add a hotel', 'Register a new property.', 'hotels.index'],
                        ['bi-plus-square', 'Add a room type', 'Set price, capacity, inventory.', 'rooms.index'],
                        ['bi-calendar-plus', 'Create a booking', 'Reserve rooms for dates.', 'bookings.index'],
                        ['bi-search', 'Check availability', 'Search by city and dates.', 'search.index'],
                    ] as [$icon, $title, $desc, $route])
                        <div class="col-sm-6">
                            <a href="{{ route($route) }}" class="text-decoration-none text-reset">
                                <div class="d-flex gap-3 p-3 rounded-3 border h-100" style="border-color:var(--hb-border)!important">
                                    <span class="hb-thumb hb-grad-indigo"><i class="bi {{ $icon }}"></i></span>
                                    <div>
                                        <div class="fw-semibold">{{ $title }}</div>
                                        <div class="text-muted small">{{ $desc }}</div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header">At a glance</div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom" style="border-color:var(--hb-border)!important">
                    <span class="text-muted"><i class="bi bi-buildings me-2"></i>Properties</span>
                    <span class="fw-semibold">{{ $stats['hotels'] }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom" style="border-color:var(--hb-border)!important">
                    <span class="text-muted"><i class="bi bi-door-open me-2"></i>Room types</span>
                    <span class="fw-semibold">{{ $stats['rooms'] }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom" style="border-color:var(--hb-border)!important">
                    <span class="text-muted"><i class="bi bi-calendar-check me-2"></i>Confirmed bookings</span>
                    <span class="fw-semibold">{{ $stats['bookings'] }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center py-2">
                    <span class="text-muted"><i class="bi bi-star me-2"></i>Average rating</span>
                    <span class="hb-chip hb-chip-amber">{{ number_format($stats['average_rating'], 1) }} ★</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
