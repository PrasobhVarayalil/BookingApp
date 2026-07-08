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
            <a href="{{ route('bookings.index') }}" class="btn btn-hero-ghost fw-semibold"><i class="bi bi-plus-circle me-1"></i>New booking</a>
        </div>
    </div>
</div>

<div class="row g-3">
    @foreach ([
        ['Total Hotels', $stats['hotels'], 'bi-buildings-fill', 'hb-grad-indigo', 'hotels.index'],
        ['Total Rooms', $stats['room_units'], 'bi-grid-fill', 'hb-grad-sky', 'rooms.index'],
        ['Room Types', $stats['room_types'], 'bi-door-open-fill', 'hb-grad-teal', 'rooms.index'],
        ['Bookings', $stats['bookings'], 'bi-calendar-check-fill', 'hb-grad-amber', 'bookings.index'],
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
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-graph-up-arrow me-2"></i>Bookings &amp; revenue</span>
                <span class="text-muted small">Last 6 months</span>
            </div>
            <div class="card-body">
                <canvas id="trendChart" height="110"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-pie-chart me-2"></i>Booking status</div>
            <div class="card-body d-flex flex-column">
                <div class="flex-grow-1 d-flex align-items-center justify-content-center">
                    <canvas id="statusChart" height="200"></canvas>
                </div>
                <div class="d-flex justify-content-between align-items-center pt-3 mt-2 border-top" style="border-color:var(--hb-border)!important">
                    <span class="text-muted"><i class="bi bi-building-check me-2"></i>Occupancy today</span>
                    <span class="hb-chip hb-chip-green">{{ $stats['occupancy'] }}%</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-trophy me-2"></i>Top hotels by bookings</div>
            <div class="card-body">
                @if (count($charts['topHotels']['labels']))
                    <canvas id="topHotelsChart" height="150"></canvas>
                @else
                    <x-empty-state icon="trophy" message="No confirmed bookings yet." />
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-geo-alt me-2"></i>Bookings by city</div>
            <div class="card-body">
                @if (count($charts['byCity']['labels']))
                    <canvas id="cityChart" height="150"></canvas>
                @else
                    <x-empty-state icon="geo-alt" message="No confirmed bookings yet." />
                @endif
            </div>
        </div>
    </div>
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
                    <span class="fw-semibold">{{ $stats['room_types'] }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom" style="border-color:var(--hb-border)!important">
                    <span class="text-muted"><i class="bi bi-grid me-2"></i>Total rooms</span>
                    <span class="fw-semibold">{{ $stats['room_units'] }}</span>
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
(() => {
    const charts = @json($charts);
    const teal = '#0f766e';
    const terracotta = '#ea7a3b';
    const gold = '#c79a3a';
    const muted = '#77837c';
    const grid = 'rgba(31,42,38,.08)';

    Chart.defaults.font.family = "'Plus Jakarta Sans', system-ui, sans-serif";
    Chart.defaults.color = muted;

    const trend = document.getElementById('trendChart');
    if (trend) {
        new Chart(trend, {
            data: {
                labels: charts.trend.labels,
                datasets: [
                    {
                        type: 'bar',
                        label: 'Bookings',
                        data: charts.trend.bookings,
                        backgroundColor: 'rgba(15,118,110,.85)',
                        borderRadius: 6,
                        yAxisID: 'y',
                        order: 2,
                    },
                    {
                        type: 'line',
                        label: 'Revenue ($)',
                        data: charts.trend.revenue,
                        borderColor: terracotta,
                        backgroundColor: 'rgba(234,122,59,.15)',
                        borderWidth: 2,
                        tension: .35,
                        fill: true,
                        yAxisID: 'y1',
                        order: 1,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8 } } },
                scales: {
                    y: { beginAtZero: true, position: 'left', grid: { color: grid }, ticks: { precision: 0 } },
                    y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false }, ticks: { callback: (v) => '$' + v } },
                    x: { grid: { display: false } },
                },
            },
        });
    }

    const status = document.getElementById('statusChart');
    if (status) {
        new Chart(status, {
            type: 'doughnut',
            data: {
                labels: ['Confirmed', 'Cancelled'],
                datasets: [{
                    data: [charts.status.confirmed, charts.status.cancelled],
                    backgroundColor: [teal, terracotta],
                    borderWidth: 0,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8 } } },
            },
        });
    }

    const topHotels = document.getElementById('topHotelsChart');
    if (topHotels) {
        new Chart(topHotels, {
            type: 'bar',
            data: {
                labels: charts.topHotels.labels,
                datasets: [{
                    label: 'Bookings',
                    data: charts.topHotels.data,
                    backgroundColor: 'rgba(15,118,110,.85)',
                    borderRadius: 6,
                }],
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true, grid: { color: grid }, ticks: { precision: 0 } }, y: { grid: { display: false } } },
            },
        });
    }

    const city = document.getElementById('cityChart');
    if (city) {
        new Chart(city, {
            type: 'bar',
            data: {
                labels: charts.byCity.labels,
                datasets: [{
                    label: 'Bookings',
                    data: charts.byCity.data,
                    backgroundColor: [teal, terracotta, gold, '#1d4ed8', '#be123c'],
                    borderRadius: 6,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, grid: { color: grid }, ticks: { precision: 0 } }, x: { grid: { display: false } } },
            },
        });
    }
})();
</script>
@endpush
@endsection
