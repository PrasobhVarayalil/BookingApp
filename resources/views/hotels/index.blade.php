@extends('layouts.app')

@section('title', 'Hotels')
@section('crumb', 'Inventory')

@section('content')
<x-filter-toolbar>
    @include('hotels.partials.filters')

    <x-slot:actions>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createHotel">
            <i class="bi bi-plus-lg me-1"></i>Add hotel
        </button>
    </x-slot:actions>
</x-filter-toolbar>

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
                            <a href="{{ route('hotels.show', $hotel) }}" class="btn btn-sm btn-soft"><i class="bi bi-eye"></i></a>
                            <button class="btn btn-sm btn-soft" data-bs-toggle="modal" data-bs-target="#editHotel{{ $loop->index }}"><i class="bi bi-pencil"></i></button>
                            <x-delete-form :action="route('hotels.destroy', $hotel)" :confirm="'Delete '.$hotel->name.'?'" />
                        </td>
                    </tr>

                    <x-modal-form
                        :id="'editHotel'.$loop->index"
                        title="Edit hotel"
                        :action="route('hotels.update', $hotel)"
                        method="PUT"
                        submit="Save changes"
                    >
                        @include('hotels.partials.fields', ['hotel' => $hotel])
                    </x-modal-form>
                @empty
                    <tr><td colspan="5"><x-empty-state icon="buildings" message="No hotels found." /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($hotels->hasPages())
        <div class="card-footer d-flex justify-content-end">{{ $hotels->links() }}</div>
    @endif
</div>

<x-modal-form id="createHotel" title="Add hotel" :action="route('hotels.store')" submit="Save hotel">
    @include('hotels.partials.fields', ['hotel' => null])
</x-modal-form>

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
