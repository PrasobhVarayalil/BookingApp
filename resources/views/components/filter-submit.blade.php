@props([
    'label' => 'Filter',
    'icon' => 'funnel',
    'clearUrl' => null,
    'showClear' => false,
])

<button type="submit" class="btn btn-sm btn-soft"><i class="bi bi-{{ $icon }} me-1"></i>{{ $label }}</button>
@if ($showClear && $clearUrl)
    <a href="{{ $clearUrl }}" class="btn btn-sm btn-link text-decoration-none px-2">Clear</a>
@endif
