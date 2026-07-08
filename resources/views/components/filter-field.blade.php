@props([
    'icon',
    'label' => null,
    'required' => false,
])

<div class="hb-filter-field">
    <i class="bi bi-{{ $icon }}" @if($label) title="{{ $label }}" @endif></i>
    @if ($required)
        <span class="hb-filter-required" aria-hidden="true">*</span>
    @endif
    {{ $slot }}
</div>
