@props([
    'title' => null,
    'icon' => null,
])

<div {{ $attributes->merge(['class' => 'card mb-3']) }}>
    @if ($title)
        <div class="card-header d-flex align-items-center gap-2">
            @if ($icon)<i class="bi bi-{{ $icon }}"></i>@endif
            <span>{{ $title }}</span>
            @isset($tools)
                <span class="ms-auto">{{ $tools }}</span>
            @endisset
        </div>
    @endif
    <div class="card-body">
        {{ $slot }}
    </div>
</div>
