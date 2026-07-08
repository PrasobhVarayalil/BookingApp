@props(['icon'])

<div class="hb-filter-field">
    <i class="bi bi-{{ $icon }}"></i>
    {{ $slot }}
</div>
