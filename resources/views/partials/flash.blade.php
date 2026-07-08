@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>
        <div>{{ session('success') }}</div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
        <i class="bi bi-exclamation-octagon-fill me-2"></i>
        <div>{{ session('error') }}</div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <div>Please fix the highlighted field(s) below.</div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
@endif
