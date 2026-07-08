@php
    $nav = [
        ['dashboard', 'Dashboard', 'bi-grid-1x2', 'dashboard'],
        ['hotels.index', 'Hotels', 'bi-buildings', 'hotels.*'],
        ['rooms.index', 'Rooms', 'bi-door-open', 'rooms.*'],
        ['bookings.index', 'Bookings', 'bi-calendar-check', 'bookings.*'],
        ['search.index', 'Search', 'bi-search', 'search.*'],
    ];
    $user = auth()->user();
    $initials = collect(explode(' ', trim($user?->name ?? 'A')))->filter()->map(fn ($p) => mb_substr($p, 0, 1))->take(2)->implode('');
@endphp

<header class="hb-nav">
    <div class="hb-container">
        <nav class="hb-nav-inner">
            <a href="{{ route('dashboard') }}" class="hb-brand">
                <span class="hb-logo"><i class="bi bi-tree-fill"></i></span>
                <span>Terra<span class="accent">Stay</span></span>
            </a>

            <button class="btn btn-soft d-lg-none ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#hbMenu">
                <i class="bi bi-list"></i>
            </button>

            <div class="collapse navbar-collapse d-lg-flex ms-lg-3" id="hbMenu">
                <div class="hb-menu py-2 py-lg-0">
                    @foreach ($nav as [$route, $label, $icon, $pattern])
                        <a href="{{ route($route) }}" class="{{ request()->routeIs($pattern) ? 'active' : '' }}">
                            <i class="bi {{ $icon }}"></i> {{ $label }}
                        </a>
                    @endforeach
                </div>

                <div class="ms-lg-auto mt-2 mt-lg-0 dropdown">
                    <button class="btn p-0 border-0 bg-transparent d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                        <span class="hb-avatar">{{ strtoupper($initials) }}</span>
                        <span class="d-none d-lg-block text-start small">
                            <span class="fw-bold d-block" style="line-height:1.1">{{ $user?->name }}</span>
                            <span class="text-muted">{{ $user?->email }}</span>
                        </span>
                        <i class="bi bi-chevron-down text-muted"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2" style="border-radius:14px">
                        <li><h6 class="dropdown-header">{{ $user?->name }}</h6></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="dropdown-item text-danger" type="submit"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </div>
</header>
