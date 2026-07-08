@props(['title', 'subtitle', 'icon' => 'compass'])

<div class="card hb-hero mb-3">
    <div class="card-body p-4 position-relative" style="z-index:1">
        <h2 class="h5 fw-bold mb-1"><i class="bi bi-{{ $icon }} me-2"></i>{{ $title }}</h2>
        <p class="opacity-75 mb-0">{{ $subtitle }}</p>
    </div>
</div>
