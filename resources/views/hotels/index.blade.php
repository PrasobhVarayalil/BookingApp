@extends('layouts.app')

@section('title', 'Hotels')
@section('crumb', 'Inventory')

@section('content')
<div class="hb-toolbar mb-3">
    <form method="GET" class="hb-filters">
        <div class="hb-filter-field">
            <i class="bi bi-globe2"></i>
            <select name="country" id="filterCountry" class="form-select form-select-sm">
                <option value="">All countries</option>
                @foreach ($countries as $country)
                    <option value="{{ $country->name }}" @selected((string) $filters['country'] === (string) $country->name)>{{ $country->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="hb-filter-field">
            <i class="bi bi-geo-alt"></i>
            <select name="city" id="filterCity" data-selected="{{ $filters['city'] }}" class="form-select form-select-sm">
                <option value="">All cities</option>
                @foreach ($cities as $city)
                    <option value="{{ $city }}" @selected((string) $filters['city'] === (string) $city)>{{ $city }}</option>
                @endforeach
            </select>
        </div>
        <div class="hb-filter-field">
            <i class="bi bi-star"></i>
            <select name="rating" class="form-select form-select-sm" data-no-search>
                <option value="">Any rating</option>
                @for ($i = 5; $i >= 1; $i--)
                    <option value="{{ $i }}" @selected((string) $filters['rating'] === (string) $i)>{{ $i }}★ &amp; up</option>
                @endfor
            </select>
        </div>
        <button class="btn btn-sm btn-soft"><i class="bi bi-funnel me-1"></i>Filter</button>
        @if ($filters['country'] || $filters['city'] || $filters['rating'])
            <a href="{{ route('hotels.index') }}" class="btn btn-sm btn-link text-decoration-none px-2">Clear</a>
        @endif
    </form>

    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createHotel">
        <i class="bi bi-plus-lg me-1"></i>Add hotel
    </button>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table hb-table align-middle">
            <thead>
                <tr>
                    <th>Hotel</th>
                    <th>Location</th>
                    <th>Rating</th>
                    <th>Rooms</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($hotels as $hotel)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <span class="hb-thumb hb-grad-indigo">{{ strtoupper(mb_substr($hotel->name, 0, 1)) }}</span>
                                <span class="fw-semibold">{{ $hotel->name }}</span>
                            </div>
                        </td>
                        <td>{{ $hotel->city }}, {{ $hotel->country }}</td>
                        <td class="hb-star">{{ str_repeat('★', $hotel->rating) }}<span class="text-muted">{{ str_repeat('☆', 5 - $hotel->rating) }}</span></td>
                        <td><span class="badge text-bg-light">{{ $hotel->room_types_count }} types</span></td>
                        <td class="text-end text-nowrap">
                            <button class="btn btn-sm btn-soft" data-bs-toggle="modal" data-bs-target="#editHotel{{ $loop->index }}"><i class="bi bi-pencil"></i></button>
                            <form method="POST" action="{{ route('hotels.destroy', $hotel) }}" class="d-inline" onsubmit="return confirm('Delete {{ $hotel->name }}?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>

                    <div class="modal fade" id="editHotel{{ $loop->index }}" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form method="POST" action="{{ route('hotels.update', $hotel) }}">
                                    @csrf @method('PUT')
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit hotel</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        @include('hotels.partials.fields', ['hotel' => $hotel])
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary">Save changes</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <tr><td colspan="5"><div class="hb-empty"><i class="bi bi-buildings"></i>No hotels found.</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($hotels->hasPages())
        <div class="card-footer d-flex justify-content-end">{{ $hotels->links() }}</div>
    @endif
</div>

<div class="modal fade" id="createHotel" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('hotels.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add hotel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @include('hotels.partials.fields', ['hotel' => null])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save hotel</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if ($errors->any() && ! old('_method'))
    @push('scripts')
    <script>new bootstrap.Modal(document.getElementById('createHotel')).show();</script>
    @endpush
@endif

@push('scripts')
<script>
(() => {
    const country = document.getElementById('filterCountry');
    const city = document.getElementById('filterCity');
    if (!country || !city) return;

    const allCities = @json($cities);

    const fill = (selected) => {
        const list = country.value ? (window.hbLocations?.[country.value] || []) : allCities;
        const keep = list.includes(selected) ? selected : '';
        const ts = city.tomselect;

        if (ts) {
            ts.clear(true);
            ts.clearOptions();
            ts.addOption({ value: '', text: 'All cities' });
            list.forEach(name => ts.addOption({ value: name, text: name }));
            ts.refreshOptions(false);
            ts.setValue(keep, true);
        } else {
            city.innerHTML = '<option value="">All cities</option>'
                + list.map(n => `<option value="${n}">${n}</option>`).join('');
            city.value = keep;
        }
    };

    fill(city.dataset.selected || '');
    const onChange = () => fill('');
    country.tomselect ? country.tomselect.on('change', onChange) : country.addEventListener('change', onChange);
})();
</script>
@endpush
@endsection
