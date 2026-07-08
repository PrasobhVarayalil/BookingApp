<style>
    :root {
        --hb-bg: #f6f3ec;
        --hb-surface: #ffffff;
        --hb-ink: #1f2a26;
        --hb-muted: #77837c;
        --hb-border: #e7e1d5;
        --hb-primary: #0f766e;      /* deep teal-green */
        --hb-primary-2: #0d9488;
        --hb-accent: #ea7a3b;       /* terracotta */
        --hb-gold: #c79a3a;
        --hb-ring: rgba(15, 118, 110, .15);
        --hb-shadow-sm: 0 1px 2px rgba(31, 42, 38, .05);
        --hb-shadow: 0 2px 6px rgba(31, 42, 38, .06), 0 18px 40px rgba(31, 42, 38, .08);
        --hb-radius: 18px;
        --hb-container: 1160px;
    }

    * { -webkit-font-smoothing: antialiased; }

    body {
        font-family: 'Plus Jakarta Sans', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
        background:
            radial-gradient(900px 340px at 100% -80px, rgba(234, 122, 59, .10), transparent 60%),
            radial-gradient(760px 300px at -60px -60px, rgba(15, 118, 110, .10), transparent 60%),
            var(--hb-bg);
        color: var(--hb-ink);
    }

    .hb-container { max-width: var(--hb-container); margin: 0 auto; padding-left: 20px; padding-right: 20px; }

    /* Top navbar */
    .hb-nav {
        position: sticky; top: 0; z-index: 1030;
        background: rgba(255, 255, 255, .82); backdrop-filter: blur(12px);
        border-bottom: 1px solid var(--hb-border);
    }
    .hb-nav-inner { display: flex; align-items: center; gap: 18px; height: 68px; }
    .hb-brand { display: flex; align-items: center; gap: 11px; text-decoration: none; color: var(--hb-ink); font-weight: 800; font-size: 1.2rem; letter-spacing: -.02em; }
    .hb-logo { width: 40px; height: 40px; border-radius: 13px; display: grid; place-items: center; color: #fff; font-size: 1.15rem;
        background: linear-gradient(140deg, var(--hb-primary), #14b8a6); box-shadow: 0 8px 18px rgba(15, 118, 110, .35); }
    .hb-brand .accent { color: var(--hb-accent); }

    .hb-menu { display: flex; align-items: center; gap: 4px; }
    .hb-menu a { display: inline-flex; align-items: center; gap: 8px; padding: 9px 15px; border-radius: 999px;
        color: #55605a; text-decoration: none; font-weight: 600; font-size: .92rem; transition: all .15s ease; }
    .hb-menu a i { font-size: 1.05rem; }
    .hb-menu a:hover { background: #eef2f0; color: var(--hb-primary); }
    .hb-menu a.active { color: #fff; background: linear-gradient(135deg, var(--hb-primary), var(--hb-primary-2)); box-shadow: 0 8px 18px rgba(15, 118, 110, .3); }

    .hb-avatar { width: 40px; height: 40px; border-radius: 50%; display: grid; place-items: center; font-weight: 700; color: #fff; border: none;
        background: linear-gradient(135deg, var(--hb-accent), #d9531e); }

    /* Page header */
    .hb-page { padding: 30px 0 56px; }
    .hb-crumb { color: var(--hb-muted); font-size: .8rem; font-weight: 600; letter-spacing: .02em; }
    .hb-title { font-size: 1.9rem; font-weight: 800; letter-spacing: -.02em; margin: 2px 0 0; }

    /* Cards + components */
    .card { border: 1px solid var(--hb-border); border-radius: var(--hb-radius); box-shadow: var(--hb-shadow-sm); background: var(--hb-surface); }
    .card-header { background: transparent; border-bottom: 1px solid var(--hb-border); font-weight: 700; padding: 1rem 1.2rem; }

    .hb-stat { position: relative; overflow: hidden; }
    .hb-stat::before { content: ""; position: absolute; inset: 0 auto 0 0; width: 5px; background: var(--hb-primary); }
    .hb-stat .card-body { display: flex; align-items: center; gap: 16px; }
    .hb-stat-icon { width: 52px; height: 52px; border-radius: 14px; display: grid; place-items: center; font-size: 1.4rem; flex: 0 0 52px; }
    .hb-stat-value { font-size: 1.95rem; font-weight: 800; line-height: 1; letter-spacing: -.02em; }
    .hb-stat-label { color: var(--hb-muted); font-size: .74rem; text-transform: uppercase; letter-spacing: .06em; font-weight: 700; }

    /* Soft tinted icon tiles (names kept stable across pages) */
    .hb-grad-indigo { background: rgba(15, 118, 110, .12); color: #0f766e; }
    .hb-grad-teal   { background: rgba(234, 122, 59, .14); color: #c2571f; }
    .hb-grad-sky    { background: rgba(37, 99, 235, .12); color: #1d4ed8; }
    .hb-grad-amber  { background: rgba(199, 154, 58, .16); color: #a4791f; }
    .hb-grad-rose   { background: rgba(225, 29, 72, .12); color: #be123c; }

    .btn { border-radius: 999px; font-weight: 600; padding: .5rem 1.05rem; }
    .btn-primary { background: linear-gradient(135deg, var(--hb-primary), var(--hb-primary-2)); border: none; box-shadow: 0 8px 18px rgba(15, 118, 110, .26); }
    .btn-primary:hover, .btn-primary:focus { filter: brightness(1.06); background: linear-gradient(135deg, var(--hb-primary), var(--hb-primary-2)); }
    .btn-soft { background: #eaf1ef; color: var(--hb-primary); border: none; }
    .btn-soft:hover { background: #dcebe8; color: var(--hb-primary); }
    .btn-outline-danger { border-radius: 999px; }
    .btn-light { background: #f1ede4; border: 1px solid var(--hb-border); }

    .hb-table { margin: 0; }
    .hb-table thead th { text-transform: uppercase; font-size: .7rem; letter-spacing: .05em; color: var(--hb-muted); font-weight: 700; border-bottom: 1px solid var(--hb-border); background: #faf8f3; }
    .hb-table > :not(caption) > * > * { padding: .92rem 1.15rem; }
    .hb-table tbody tr { border-color: var(--hb-border); }
    .hb-table tbody tr:hover { background: #faf7f1; }

    .hb-thumb { width: 42px; height: 42px; border-radius: 13px; display: grid; place-items: center; font-weight: 800; flex: 0 0 42px; }

    .badge { font-weight: 700; border-radius: 8px; padding: .42em .62em; }
    .hb-chip { display: inline-flex; align-items: center; gap: 5px; padding: .32rem .62rem; border-radius: 999px; font-size: .78rem; font-weight: 700; }
    .hb-chip-green { background: #d7efe6; color: #0f766e; }
    .hb-chip-amber { background: #fbead6; color: #b45309; }
    .hb-star { color: var(--hb-gold); letter-spacing: 1px; }

    .form-control, .form-select { border-radius: 12px; border-color: var(--hb-border); padding: .58rem .8rem; background: #fdfcf9; }
    .form-control:focus, .form-select:focus { border-color: var(--hb-primary-2); box-shadow: 0 0 0 .22rem var(--hb-ring); background: #fff; }
    .form-label { font-weight: 600; font-size: .85rem; color: #3d4842; margin-bottom: .35rem; }
    .input-icon { position: relative; }
    .input-icon > i { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); color: var(--hb-muted); }
    .input-icon > .form-control { padding-left: 38px; }

    .modal-content { border: none; border-radius: 20px; box-shadow: var(--hb-shadow); }
    .modal-header { border-bottom: 1px solid var(--hb-border); }
    .modal-footer { border-top: 1px solid var(--hb-border); }

    .hb-empty { text-align: center; padding: 54px 20px; color: var(--hb-muted); }
    .hb-empty i { font-size: 2.6rem; opacity: .5; display: block; margin-bottom: 10px; }

    .alert { border: none; border-radius: 14px; box-shadow: var(--hb-shadow-sm); }
    .page-link { border-radius: 10px !important; margin: 0 2px; border-color: var(--hb-border); color: var(--hb-primary); }
    .page-item.active .page-link { background: var(--hb-primary); border-color: var(--hb-primary); }

    /* Hero band (dashboard/search) */
    .hb-hero { border: none; border-radius: var(--hb-radius); color: #fff; overflow: hidden;
        background: linear-gradient(120deg, #0f766e 0%, #115e59 55%, #1f3a34 100%); position: relative; }
    .hb-hero::after { content: ""; position: absolute; right: -40px; top: -60px; width: 240px; height: 240px; border-radius: 50%;
        background: radial-gradient(circle, rgba(234, 122, 59, .55), transparent 65%); }

    @media (max-width: 767.98px) {
        .hb-title { font-size: 1.5rem; }
        .hb-menu { flex-wrap: wrap; }
    }
</style>
