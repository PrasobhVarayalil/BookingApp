@php($h = $hotel ?? null)
<div class="mb-3">
    <label class="form-label">Name</label>
    <input type="text" name="name" value="{{ old('name', $h?->name) }}" class="form-control @error('name') is-invalid @enderror" required>
    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="mb-3">
    <label class="form-label">City</label>
    <input type="text" name="city" value="{{ old('city', $h?->city) }}" class="form-control @error('city') is-invalid @enderror" required>
    @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="mb-3">
    <label class="form-label">Country</label>
    <input type="text" name="country" value="{{ old('country', $h?->country) }}" class="form-control @error('country') is-invalid @enderror" required>
    @error('country')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="mb-3">
    <label class="form-label">Rating (1-5)</label>
    <input type="number" name="rating" min="1" max="5" value="{{ old('rating', $h?->rating ?? 3) }}" class="form-control @error('rating') is-invalid @enderror" required>
    @error('rating')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
