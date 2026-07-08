@props([
    'action',
    'confirm',
    'label' => null,
    'icon' => 'trash',
])

<form method="POST" action="{{ $action }}" class="d-inline" onsubmit="return confirm(@js($confirm))">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-sm btn-outline-danger">
        <i class="bi bi-{{ $icon }} {{ $label ? 'me-1' : '' }}"></i>{{ $label }}
    </button>
</form>
