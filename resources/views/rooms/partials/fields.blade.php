@php($r = $room ?? null)
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
    <label class="form-label">Room name</label>
    <input type="text" name="name" value="{{ old('name', $r?->name) }}" class="form-control @error('name') is-invalid @enderror" required>
    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="row g-2">
    <div class="col-md-4 mb-3">
        <label class="form-label">Price / night</label>
        <input type="number" step="0.01" min="0" name="price_per_night" value="{{ old('price_per_night', $r?->price_per_night) }}" class="form-control @error('price_per_night') is-invalid @enderror" required>
        @error('price_per_night')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Max guests</label>
        <input type="number" min="1" name="max_occupancy" value="{{ old('max_occupancy', $r?->max_occupancy ?? 2) }}" class="form-control @error('max_occupancy') is-invalid @enderror" required>
        @error('max_occupancy')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Total rooms</label>
        <input type="number" min="1" name="total_rooms" value="{{ old('total_rooms', $r?->total_rooms ?? 1) }}" class="form-control @error('total_rooms') is-invalid @enderror" required>
        @error('total_rooms')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
