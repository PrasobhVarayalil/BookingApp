@props([
    'icon',
    'label' => null,
])

<div class="hb-filter-field">
    <i class="bi bi-{{ $icon }}" @if($label) title="{{ $label }}" @endif></i>
    {{ $slot }}
</div>
