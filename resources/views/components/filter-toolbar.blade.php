@props(['method' => 'GET', 'action' => null, 'class' => 'mb-3'])

<div {{ $attributes->merge(['class' => 'hb-toolbar '.$class]) }}>
    <form method="{{ $method }}" @if($action) action="{{ $action }}" @endif class="hb-filters">
        @if (strtoupper($method) !== 'GET')
            @csrf
        @endif
        {{ $slot }}
    </form>
    @isset($actions)
        {{ $actions }}
    @endisset
</div>
