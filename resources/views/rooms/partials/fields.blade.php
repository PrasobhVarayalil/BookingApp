@php($r = $roomType ?? null)
<div class="mb-3">
    <label class="form-label">Hotel</label>
    <select name="hotel_id" class="form-select @error('hotel_id') is-invalid @enderror" required>
        <option value="">Select hotel</option>
        @foreach ($hotels as $hotel)
            <option value="{{ $hotel->id }}" @selected(old('hotel_id', $r?->hotel_id) === $hotel->id)>
                {{ $hotel->name }} ({{ $hotel->city }})
            </option>
        @endforeach
    </select>
    @error('hotel_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="mb-3">
    <label class="form-label">Room type name</label>
    <input type="text" name="name" value="{{ old('name', $r?->name) }}" class="form-control @error('name') is-invalid @enderror" required>
    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="row g-2">
    <div class="col-md-6 mb-3">
        <label class="form-label">Price / night</label>
        <input type="number" step="0.01" min="0" name="price_per_night" value="{{ old('price_per_night', $r?->price_per_night) }}" class="form-control @error('price_per_night') is-invalid @enderror" required>
        @error('price_per_night')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Max guests</label>
        <input type="number" min="1" name="max_occupancy" value="{{ old('max_occupancy', $r?->max_occupancy ?? 2) }}" class="form-control @error('max_occupancy') is-invalid @enderror" required>
        @error('max_occupancy')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
@if (! $r)
    <div class="mb-3">
        <label class="form-label">Room numbers</label>
        <textarea name="room_numbers" rows="2" class="form-control @error('room_numbers') is-invalid @enderror" placeholder="101, 102, 103" required>{{ old('room_numbers') }}</textarea>
        <div class="form-text">Comma or space separated physical room numbers for this type.</div>
        @error('room_numbers')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
@else
    <div class="mb-0">
        <label class="form-label">Room numbers</label>
        <div class="d-flex flex-wrap gap-2">
            @forelse ($r->units as $unit)
                <span class="hb-chip hb-chip-green">{{ $unit->room_number }}</span>
            @empty
                <span class="text-muted small">No units</span>
            @endforelse
        </div>
    </div>
@endif
