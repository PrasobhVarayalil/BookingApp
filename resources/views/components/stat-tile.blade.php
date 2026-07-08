@props([
    'icon',
    'value',
    'label',
    'tone' => 'indigo',
])

<div class="card h-100">
    <div class="card-body d-flex align-items-center gap-3">
        <span class="hb-thumb hb-grad-{{ $tone }}"><i class="bi bi-{{ $icon }}"></i></span>
        <div>
            <div class="hb-stat-value" style="font-size:1.5rem">{{ $value }}</div>
            <div class="hb-stat-label">{{ $label }}</div>
        </div>
    </div>
</div>
