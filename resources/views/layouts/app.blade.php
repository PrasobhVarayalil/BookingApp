<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') · TerraStay</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    @include('partials.styles')
    @stack('styles')
</head>
<body>
    @include('partials.navbar')

    <main class="hb-page">
        <div class="hb-container">
            <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-4">
                <div>
                    <div class="hb-crumb"><i class="bi bi-house-door me-1"></i>@yield('crumb', 'Overview')</div>
                    <h1 class="hb-title">@yield('title', 'Dashboard')</h1>
                </div>
                @hasSection('actions')
                    <div>@yield('actions')</div>
                @endif
            </div>

            @include('partials.flash')
            @yield('content')
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
    <script>
        window.hbInitSelect = (el) => {
            if (!el || el.tomselect) return el?.tomselect;
            return new TomSelect(el, {
                allowEmptyOption: true,
                controlInput: '<input>',
                render: {
                    no_results: () => '<div class="no-results px-3 py-2 text-muted">No matches found</div>',
                },
            });
        };

        document.querySelectorAll('select.form-select:not([data-no-search])').forEach(window.hbInitSelect);
    </script>
    @stack('scripts')
</body>
</html>
