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
        --hb-container: 100%;
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

    .hb-container { max-width: var(--hb-container); width: 100%; margin: 0 auto; padding-left: 32px; padding-right: 32px; }

    @media (max-width: 767.98px) {
        .hb-container { padding-left: 16px; padding-right: 16px; }
    }

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
    /* Outline button on the dark hero band — stays readable on hover */
    .btn-hero-ghost { background: rgba(255, 255, 255, .12); color: #fff; border: 1px solid rgba(255, 255, 255, .55); }
    .btn-hero-ghost:hover, .btn-hero-ghost:focus { background: #fff; color: var(--hb-primary); border-color: #fff; }
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

    .form-control, .form-select { border-radius: 12px; border-color: var(--hb-border); padding: .5rem .8rem; background: #fdfcf9; font-size: .875rem; }
    .form-control:focus, .form-select:focus { border-color: var(--hb-primary-2); box-shadow: 0 0 0 .22rem var(--hb-ring); background: #fff; }
    .form-label { font-weight: 600; font-size: .8rem; color: #3d4842; margin-bottom: .4rem; }
    .hb-required { color: #dc3545; margin-left: 2px; font-weight: 700; }
    .form-text { font-size: .76rem; }
    .invalid-feedback { font-size: .76rem; }
    .input-icon { position: relative; }
    .input-icon > i { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); color: var(--hb-muted); }
    .input-icon > .form-control { padding-left: 38px; }

    /* Consistent breathing room for form field grids */
    form .g-2 { --bs-gutter-y: .9rem; }
    form .g-3 { --bs-gutter-y: 1.2rem; }

    /* Searchable selects (Tom Select) */
    .ts-wrapper { min-width: 170px; }
    .ts-wrapper.form-select { padding: 0 !important; background-image: none; }
    .ts-control { border-radius: 12px !important; border: 1px solid var(--hb-border) !important; background: #fdfcf9 !important; padding: .42rem 2rem .42rem .8rem !important; min-height: 41px; font-size: .875rem; box-shadow: none !important; }
    .ts-wrapper.focus .ts-control { border-color: var(--hb-primary-2) !important; box-shadow: 0 0 0 .22rem var(--hb-ring) !important; background: #fff !important; }
    .ts-wrapper.single .ts-control, .ts-control input { color: var(--hb-ink); font-size: .875rem; }
    .ts-dropdown { border: 1px solid var(--hb-border); border-radius: 12px; box-shadow: var(--hb-shadow); overflow: hidden; margin-top: 4px; font-size: .875rem; }
    .ts-dropdown .option { padding: .55rem .8rem; }
    .ts-dropdown .option.active { background: var(--hb-ring); color: var(--hb-primary); }
    .ts-dropdown .option .fw, .ts-dropdown .active { color: var(--hb-primary); }
    .ts-wrapper.is-invalid .ts-control { border-color: #dc3545 !important; }

    .modal-content { border: none; border-radius: 20px; box-shadow: var(--hb-shadow); overflow: hidden; }
    .modal-header { border-bottom: 1px solid var(--hb-border); }
    .modal-footer { border-top: 1px solid var(--hb-border); }

    /* Booking modal — two-panel layout */
    .hb-modal-head { align-items: flex-start; padding: 1.15rem 1.35rem; }
    .hb-booking-form { padding: 1.35rem 1.45rem; }
    .hb-section-title { display: flex; align-items: center; gap: 8px; font-size: .74rem; text-transform: uppercase;
        letter-spacing: .07em; font-weight: 800; color: var(--hb-muted); margin: .2rem 0 .9rem; }
    .hb-section-title::after { content: ""; flex: 1; height: 1px; background: var(--hb-border); }
    .hb-section-title i { color: var(--hb-primary); font-size: .95rem; }
    .hb-booking-form .hb-section + .hb-section { margin-top: 1.5rem; }

    .hb-booking-aside { background: linear-gradient(165deg, #0f766e 0%, #115e59 55%, #1f3a34 100%); color: #fff;
        padding: 1.5rem 1.4rem; position: relative; overflow: hidden; }
    .hb-booking-aside::after { content: ""; position: absolute; right: -50px; top: -60px; width: 210px; height: 210px;
        border-radius: 50%; background: radial-gradient(circle, rgba(234, 122, 59, .5), transparent 65%); }
    .hb-aside-head { position: relative; z-index: 1; display: flex; align-items: center; gap: 12px; margin-bottom: 1.15rem; }
    .hb-aside-ico { width: 44px; height: 44px; border-radius: 13px; display: grid; place-items: center; font-size: 1.25rem;
        background: rgba(255, 255, 255, .14); }
    .hb-aside-head .t { font-weight: 800; letter-spacing: -.01em; }
    .hb-aside-head .s { font-size: .78rem; color: rgba(255, 255, 255, .72); }
    .hb-sum { position: relative; z-index: 1; }
    .hb-sum-row { display: flex; align-items: baseline; justify-content: space-between; gap: 12px; padding: .5rem 0;
        border-bottom: 1px dashed rgba(255, 255, 255, .16); font-size: .9rem; }
    .hb-sum-row .k { color: rgba(255, 255, 255, .68); font-weight: 600; }
    .hb-sum-row .v { font-weight: 700; text-align: right; }
    .hb-sum-total { display: flex; align-items: baseline; justify-content: space-between; margin-top: 1.1rem;
        padding-top: 1rem; border-top: 1px solid rgba(255, 255, 255, .22); }
    .hb-sum-total .k { font-size: .78rem; text-transform: uppercase; letter-spacing: .06em; color: rgba(255, 255, 255, .72); font-weight: 700; }
    .hb-sum-total .v { font-size: 1.7rem; font-weight: 800; letter-spacing: -.02em; }
    .hb-room-pill { display: inline-flex; align-items: center; gap: 6px; padding: .3rem .7rem; border-radius: 999px;
        background: rgba(255, 255, 255, .16); font-weight: 700; font-size: .82rem; }
    .hb-room-pill.is-empty { background: rgba(255, 255, 255, .08); color: rgba(255, 255, 255, .6); font-weight: 600; }

    @media (max-width: 991.98px) {
        .hb-booking-aside { border-top: 1px solid rgba(255, 255, 255, .15); }
    }

    /* Compact filter toolbar */
    .hb-toolbar { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; }
    .hb-filters { display: flex; flex-wrap: wrap; align-items: center; gap: 8px;
        background: var(--hb-surface); border: 1px solid var(--hb-border); border-radius: 999px; padding: 6px 8px 6px 12px; box-shadow: var(--hb-shadow-sm); }
    .hb-filter-field { position: relative; display: flex; align-items: center; }
    .hb-filter-field > i { position: absolute; left: 11px; z-index: 2; color: var(--hb-muted); font-size: .9rem; pointer-events: none; }
    .hb-filter-field .form-select, .hb-filter-field .form-control, .hb-filter-field .ts-wrapper { min-width: 158px; }
    .hb-filter-field .form-select, .hb-filter-field .form-control { padding-left: 32px; border: none; background-color: transparent; border-radius: 999px; }
    .hb-filter-field .form-control:focus, .hb-filter-field .form-select:focus { box-shadow: none; background-color: transparent; }
    .hb-filter-field .ts-control { border: none !important; background: transparent !important; padding-left: 32px !important; min-height: 36px; }
    .hb-filter-field .ts-wrapper.focus .ts-control { box-shadow: none !important; }
    .hb-filter-field + .hb-filter-field { border-left: 1px solid var(--hb-border); }
    .form-select-sm, .form-select-sm + .ts-wrapper .ts-control { font-size: .82rem; }

    .hb-empty { text-align: center; padding: 54px 20px; color: var(--hb-muted); }
    .hb-empty i { font-size: 2.6rem; opacity: .5; display: block; margin-bottom: 10px; }

    /* Detail (show) pages */
    .hb-detail-row { display: flex; gap: 16px; padding: .6rem 0; border-bottom: 1px dashed var(--hb-border); }
    .hb-detail-row:last-child { border-bottom: none; }
    .hb-detail-label { flex: 0 0 150px; color: var(--hb-muted); font-size: .82rem; font-weight: 600; }
    .hb-detail-value { flex: 1; font-weight: 600; font-size: .9rem; }
    .hb-detail-head { display: flex; align-items: center; gap: 16px; }
    .hb-detail-head .hb-thumb { width: 58px; height: 58px; flex: 0 0 58px; font-size: 1.4rem; }

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
