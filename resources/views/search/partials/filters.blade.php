@php($today = now()->toDateString())
@php($selCity = old('city', $filters['city'] ?? ''))
@php($selCheckin = old('checkin_date', $filters['checkin_date'] ?? ''))
@php($selCheckout = old('checkout_date', $filters['checkout_date'] ?? ''))
@php($selGuests = old('guests', $filters['guests'] ?? 1))

<x-filter-field icon="geo-alt">
    <select name="city" class="form-select form-select-sm @error('city') is-invalid @enderror" style="min-width:190px" required>
        <option value="">Choose a city</option>
        @foreach ($cities as $city)
            <option value="{{ $city }}" @selected($selCity === $city)>{{ $city }}</option>
        @endforeach
    </select>
</x-filter-field>

<x-filter-field icon="calendar-check">
    <input type="date" name="checkin_date" value="{{ $selCheckin }}" min="{{ $today }}" title="Check-in" class="form-control form-control-sm @error('checkin_date') is-invalid @enderror" required>
</x-filter-field>

<x-filter-field icon="calendar-x">
    <input type="date" name="checkout_date" value="{{ $selCheckout }}" min="{{ $today }}" title="Check-out" class="form-control form-control-sm @error('checkout_date') is-invalid @enderror" required>
</x-filter-field>

<x-filter-field icon="people">
    <input type="number" name="guests" min="1" value="{{ $selGuests }}" title="Guests" style="min-width:96px" class="form-control form-control-sm @error('guests') is-invalid @enderror" required>
</x-filter-field>

<x-filter-submit
    label="Search"
    icon="search"
    :clear-url="route('search.index')"
    :show-clear="(bool) $payload"
/>
