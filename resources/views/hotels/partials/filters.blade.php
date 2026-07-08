<x-filter-field icon="globe2">
    <select name="country" id="filterCountry" class="form-select form-select-sm">
        <option value="">All countries</option>
        @foreach ($countries as $country)
            <option value="{{ $country->name }}" @selected((string) $filters['country'] === (string) $country->name)>{{ $country->name }}</option>
        @endforeach
    </select>
</x-filter-field>

<x-filter-field icon="geo-alt">
    <select name="city" id="filterCity" data-selected="{{ $filters['city'] }}" class="form-select form-select-sm">
        <option value="">All cities</option>
        @foreach ($cities as $city)
            <option value="{{ $city }}" @selected((string) $filters['city'] === (string) $city)>{{ $city }}</option>
        @endforeach
    </select>
</x-filter-field>

<x-filter-field icon="star">
    <select name="rating" class="form-select form-select-sm" data-no-search>
        <option value="">Any rating</option>
        @for ($i = 5; $i >= 1; $i--)
            <option value="{{ $i }}" @selected((string) $filters['rating'] === (string) $i)>{{ $i }}★ &amp; up</option>
        @endfor
    </select>
</x-filter-field>

<x-filter-submit
    :clear-url="route('hotels.index')"
    :show-clear="(bool) ($filters['country'] || $filters['city'] || $filters['rating'])"
/>
