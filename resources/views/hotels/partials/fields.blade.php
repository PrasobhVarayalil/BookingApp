@php($h = $hotel ?? null)
@php($selCountry = old('country', $h?->country))
@php($selCity = old('city', $h?->city))
@php($currentCities = optional($countries->firstWhere('name', $selCountry))->cities ?? collect())
<div class="mb-3">
    <label class="form-label">Name</label>
    <input type="text" name="name" value="{{ old('name', $h?->name) }}" class="form-control @error('name') is-invalid @enderror" required>
    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Country</label>
        <select name="country" class="form-select js-country @error('country') is-invalid @enderror" required>
            <option value="">Select a country</option>
            @foreach ($countries as $country)
                <option value="{{ $country->name }}" @selected($selCountry === $country->name)>{{ $country->name }}</option>
            @endforeach
        </select>
        @error('country')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">City</label>
        <select name="city" class="form-select js-city @error('city') is-invalid @enderror" required>
            <option value="">Select a city</option>
            @foreach ($currentCities as $city)
                <option value="{{ $city->name }}" @selected($selCity === $city->name)>{{ $city->name }}</option>
            @endforeach
        </select>
        @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
<div class="mb-3 mt-3">
    <label class="form-label">Rating (1-5)</label>
    <input type="number" name="rating" min="1" max="5" value="{{ old('rating', $h?->rating ?? 3) }}" class="form-control @error('rating') is-invalid @enderror" required>
    @error('rating')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

@once
@push('scripts')
<script>
(() => {
    const locations = window.hbLocations = @json($countries->mapWithKeys(fn ($c) => [$c->name => $c->cities->pluck('name')->values()]));

    const repopulate = (country) => {
        const city = country.closest('form')?.querySelector('select.js-city');
        if (!city) return;

        const list = locations[country.value] || [];
        const cityTs = city.tomselect;

        if (cityTs) {
            cityTs.clear(true);
            cityTs.clearOptions();
            cityTs.addOption({ value: '', text: 'Select a city' });
            list.forEach(name => cityTs.addOption({ value: name, text: name }));
            cityTs.refreshOptions(false);
            list.length ? cityTs.enable() : cityTs.disable();
        } else {
            city.innerHTML = '<option value="">Select a city</option>'
                + list.map(n => `<option value="${n}">${n}</option>`).join('');
        }
    };

    document.querySelectorAll('select.js-country').forEach((country) => {
        const onChange = () => repopulate(country);
        country.tomselect ? country.tomselect.on('change', onChange) : country.addEventListener('change', onChange);
    });
})();
</script>
@endpush
@endonce
