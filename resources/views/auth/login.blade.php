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
                radial-gradient(760px 420px at 15% -120px, rgba(234, 122, 59, .22), transparent 60%),
                radial-gradient(700px 460px at 100% 120%, rgba(20, 184, 166, .22), transparent 55%),
                linear-gradient(160deg, #0f766e, #123a35 68%, #0b2320);
        }

        .hb-auth-shell {
            width: 100%; max-width: 960px; border-radius: 28px; overflow: hidden;
            display: grid; grid-template-columns: 1.05fr .95fr;
            background: #fff; box-shadow: 0 40px 90px rgba(6, 30, 27, .5);
        }

        /* Showcase panel */
        .hb-auth-showcase {
            position: relative; color: #fff; padding: 46px 40px;
            display: flex; flex-direction: column; justify-content: space-between;
            background: linear-gradient(165deg, #0f766e 0%, #115e59 55%, #143b36 100%);
            overflow: hidden;
        }
        .hb-auth-showcase::after {
            content: ""; position: absolute; right: -70px; top: -80px; width: 280px; height: 280px; border-radius: 50%;
            background: radial-gradient(circle, rgba(234, 122, 59, .55), transparent 65%);
        }
        .hb-auth-showcase::before {
            content: ""; position: absolute; left: -60px; bottom: -90px; width: 260px; height: 260px; border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 255, 255, .12), transparent 65%);
        }
        .hb-auth-brand { position: relative; z-index: 1; display: flex; align-items: center; gap: 12px; font-weight: 800; font-size: 1.35rem; }
        .hb-auth-brand .hb-logo { width: 44px; height: 44px; border-radius: 14px; display: grid; place-items: center; font-size: 1.25rem;
            background: rgba(255, 255, 255, .16); box-shadow: none; }
        .hb-auth-brand .accent { color: var(--hb-accent); }

        .hb-showcase-copy { position: relative; z-index: 1; margin: 30px 0; }
        .hb-showcase-copy h2 { font-weight: 800; font-size: 1.85rem; line-height: 1.2; letter-spacing: -.02em; margin-bottom: 12px; }
        .hb-showcase-copy p { color: rgba(255, 255, 255, .78); margin: 0; }

        .hb-feature-list { position: relative; z-index: 1; display: flex; flex-direction: column; gap: 16px; }
        .hb-feature { display: flex; align-items: center; gap: 13px; }
        .hb-feature-ico { width: 40px; height: 40px; border-radius: 12px; display: grid; place-items: center; font-size: 1.15rem; flex: 0 0 40px;
            background: rgba(255, 255, 255, .14); }
        .hb-feature .t { font-weight: 700; font-size: .95rem; line-height: 1.1; }
        .hb-feature .s { font-size: .8rem; color: rgba(255, 255, 255, .68); }

        /* Form panel */
        .hb-auth-form { padding: 48px 44px; display: flex; flex-direction: column; justify-content: center; }
        .hb-auth-form h1 { font-weight: 800; letter-spacing: -.02em; }
        .hb-auth-form .lead-sub { color: var(--hb-muted); margin-bottom: 26px; }
        .input-icon > .hb-toggle-pass {
            position: absolute; top: 50%; left: auto; right: 10px; transform: translateY(-50%);
            display: grid; place-items: center; width: 30px; height: 30px; line-height: 1;
            cursor: pointer; pointer-events: auto; background: none; border: none; padding: 0; color: var(--hb-muted);
        }
        .input-icon > .hb-toggle-pass:hover { color: var(--hb-primary); }
        .input-icon > .form-control.has-toggle { padding-right: 42px; }
        .hb-auth-form .btn-primary { padding: .7rem; font-size: .98rem; }
        .hb-demo { background: #f5f2ea; border: 1px dashed var(--hb-border); border-radius: 14px; padding: .7rem .9rem; }

        @media (max-width: 767.98px) {
            .hb-auth-shell { grid-template-columns: 1fr; max-width: 440px; }
            .hb-auth-showcase { padding: 34px 30px; }
            .hb-showcase-copy { margin: 22px 0; }
            .hb-showcase-copy h2 { font-size: 1.5rem; }
            .hb-auth-form { padding: 34px 30px; }
        }
    </style>
</head>
<body>
<div class="hb-auth-shell">
    <aside class="hb-auth-showcase">
        <div class="hb-auth-brand">
            <span class="hb-logo"><i class="bi bi-tree-fill"></i></span>
            <span>Terra<span class="accent">Stay</span></span>
        </div>

        <div class="hb-showcase-copy">
            <h2>Manage your properties with confidence.</h2>
            <p>Hotels, room inventory, and real-time availability — all in one clean workspace.</p>
        </div>

        <div class="hb-feature-list">
            <div class="hb-feature">
                <span class="hb-feature-ico"><i class="bi bi-buildings"></i></span>
                <div>
                    <div class="t">Centralized inventory</div>
                    <div class="s">Hotels, room types and units in one place.</div>
                </div>
            </div>
            <div class="hb-feature">
                <span class="hb-feature-ico"><i class="bi bi-calendar-check"></i></span>
                <div>
                    <div class="t">Live availability</div>
                    <div class="s">Search and book by city and dates instantly.</div>
                </div>
            </div>
            <div class="hb-feature">
                <span class="hb-feature-ico"><i class="bi bi-graph-up-arrow"></i></span>
                <div>
                    <div class="t">Insightful dashboard</div>
                    <div class="s">Track bookings, revenue and occupancy.</div>
                </div>
            </div>
        </div>
    </aside>

    <div class="hb-auth-form">
        <h1 class="h4 mb-1">Welcome back</h1>
        <p class="lead-sub">Sign in to your TerraStay account</p>

        <form method="POST" action="{{ url('/login') }}" novalidate>
            @csrf
            <div class="mb-3">
                <x-form-label for="email" required>Email address</x-form-label>
                <div class="input-icon">
                    <i class="bi bi-envelope"></i>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                           placeholder="you@example.com" class="form-control @error('email') is-invalid @enderror">
                </div>
                @error('email')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <x-form-label for="password" required>Password</x-form-label>
                <div class="input-icon">
                    <i class="bi bi-lock"></i>
                    <input id="password" type="password" name="password" required
                           placeholder="••••••••" class="form-control has-toggle @error('password') is-invalid @enderror">
                    <button type="button" class="hb-toggle-pass text-muted" data-target="password" aria-label="Show password">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                @error('password')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>

            <div class="d-flex align-items-center justify-content-between mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label small" for="remember">Keep me signed in</label>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-box-arrow-in-right me-1"></i>Sign in
            </button>
        </form>

        <div class="hb-demo small mt-4 d-flex align-items-center">
            <i class="bi bi-info-circle me-2 text-muted"></i>
            <div>Demo login: <code>admin@terrastay.com</code> / <code>password</code></div>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.hb-toggle-pass').forEach((btn) => {
        btn.addEventListener('click', () => {
            const input = document.getElementById(btn.dataset.target);
            const icon = btn.querySelector('i');
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            icon.classList.toggle('bi-eye', !show);
            icon.classList.toggle('bi-eye-slash', show);
        });
    });
</script>
</body>
</html>
