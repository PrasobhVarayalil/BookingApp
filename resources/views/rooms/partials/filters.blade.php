<x-filter-field icon="search">
    <input type="text" name="search" value="{{ $filters['search'] }}" class="form-control form-control-sm" placeholder="Search type or hotel" style="min-width:200px">
</x-filter-field>

<x-filter-field icon="buildings">
    <select name="hotel" class="form-select form-select-sm">
        <option value="">All hotels</option>
        @foreach ($hotels as $hotel)
            <option value="{{ $hotel->id }}" @selected($filters['hotel'] === $hotel->id)>{{ $hotel->name }}</option>
        @endforeach
    </select>
</x-filter-field>

<x-filter-submit
    :clear-url="route('rooms.index')"
    :show-clear="(bool) ($filters['search'] || $filters['hotel'])"
/>
