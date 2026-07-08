@if ($errors->any())
    <div class="text-danger small mb-3 ps-1">
        @foreach ($errors->all() as $error)
            <div><i class="bi bi-exclamation-circle me-1"></i>{{ $error }}</div>
        @endforeach
    </div>
@endif
