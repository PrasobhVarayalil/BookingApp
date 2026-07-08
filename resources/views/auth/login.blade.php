<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in · TerraStay</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    @include('partials.styles')
    <style>
        body {
            min-height: 100vh; display: grid; place-items: center; padding: 24px;
            background:
                radial-gradient(700px 380px at 50% -140px, rgba(234, 122, 59, .28), transparent 60%),
                linear-gradient(180deg, #0f766e, #123a35 70%, #0e2b28);
        }
        .hb-auth-card { width: 100%; max-width: 430px; border-radius: 24px; background: #fff; box-shadow: 0 30px 70px rgba(6, 30, 27, .45); overflow: hidden; }
        .hb-auth-top { text-align: center; padding: 30px 32px 8px; }
        .hb-auth-badge { width: 62px; height: 62px; border-radius: 20px; display: grid; place-items: center; margin: 0 auto 14px; color: #fff; font-size: 1.7rem;
            background: linear-gradient(140deg, var(--hb-primary), #14b8a6); box-shadow: 0 12px 26px rgba(15, 118, 110, .4); }
        .hb-auth-body { padding: 8px 32px 32px; }
        .hb-strip { height: 6px; background: linear-gradient(90deg, var(--hb-primary), var(--hb-accent), var(--hb-gold)); }
    </style>
</head>
<body>
<div class="hb-auth-card">
    <div class="hb-strip"></div>
    <div class="hb-auth-top">
        <div class="hb-auth-badge"><i class="bi bi-tree-fill"></i></div>
        <h1 class="h4 fw-bold mb-1">Terra<span style="color:var(--hb-accent)">Stay</span></h1>
        <p class="text-muted mb-0">Sign in to manage your properties</p>
    </div>

    <div class="hb-auth-body">
        <form method="POST" action="{{ url('/login') }}" novalidate>
            @csrf
            <div class="mb-3">
                <label for="email" class="form-label">Email address</label>
                <div class="input-icon">
                    <i class="bi bi-envelope"></i>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                           placeholder="you@example.com" class="form-control @error('email') is-invalid @enderror">
                </div>
                @error('email')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="input-icon">
                    <i class="bi bi-lock"></i>
                    <input id="password" type="password" name="password" required
                           placeholder="••••••••" class="form-control @error('password') is-invalid @enderror">
                </div>
                @error('password')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>

            <div class="form-check mb-4">
                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                <label class="form-check-label small" for="remember">Keep me signed in</label>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2">
                <i class="bi bi-box-arrow-in-right me-1"></i>Sign in
            </button>
        </form>

        <div class="alert alert-light border small mt-4 mb-0 d-flex align-items-center">
            <i class="bi bi-info-circle me-2"></i>
            <div>Demo: <code>admin@example.com</code> / <code>password</code></div>
        </div>
    </div>
</div>
</body>
</html>
