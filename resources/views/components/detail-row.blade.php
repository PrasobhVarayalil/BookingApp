@props(['label'])

<div class="hb-detail-row">
    <div class="hb-detail-label">{{ $label }}</div>
    <div class="hb-detail-value">{{ $slot }}</div>
</div>
