<style>
    :root {
        --primary: #1A3C5E;
        --accent:  #2E86C1;
        --success: #1E8449;
        --warning: #D68910;
        --danger:  #C0392B;
        --bg:      #F8F9FA;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: system-ui, -apple-system, sans-serif; background: var(--bg); color: #333; }

    /* ── Sidebar (admin) ───────────────────────── */
    .admin-layout { display: flex; min-height: 100vh; }
    .sidebar { width: 220px; background: #fff; border-right: 1px solid #d0d5dd; display: flex; flex-direction: column; flex-shrink: 0; transition: width .22s cubic-bezier(.4,0,.2,1); position: relative; }
    .sidebar-logo { padding: 0; background: #fff; border-bottom: 1px solid #e5e7eb; line-height: 0; overflow: hidden; transition: box-shadow .22s, border-radius .22s; position: relative; }
    .sidebar-logo img { width: 100%; height: auto; display: block; }
    .sidebar-nav { display: flex; flex-direction: column; padding: .5rem 0; flex: 1; }
    .sidebar-nav a { color: var(--primary); text-decoration: none; padding: .6rem 1.25rem .6rem calc(1.25rem - 3px); font-size: .88rem; font-weight: 500; border-left: 3px solid transparent; transition: background .15s, color .15s, border-color .15s, padding .22s; position: relative; }
    .sidebar-nav a:hover { background: rgba(46,134,193,.08); color: var(--accent); border-left-color: rgba(46,134,193,.4); }
    .sidebar-nav a.active { background: rgba(46,134,193,.1); color: var(--accent); border-left-color: var(--accent); font-weight: 600; }
    .sidebar.collapsed { width: 64px; }
    .sidebar.collapsed .sidebar-logo { top: -6px; margin-bottom: -6px; border-radius: 0 0 10px 10px; box-shadow: 0 8px 22px rgba(0,0,0,.18); border-bottom-color: transparent; z-index: 3; }
    .sidebar.collapsed .sidebar-nav a { padding: .65rem 0; justify-content: center; }
    .sidebar.collapsed .sidebar-nav a.active { border-left-color: transparent !important; background: rgba(46,134,193,.12); border-radius: 8px; margin: 0 6px; width: calc(100% - 12px); }
    .sidebar.collapsed .nav-label { display: none !important; }
    .sidebar.collapsed .nav-badge { position: absolute !important; top: 5px; right: 5px; min-width: 15px; height: 15px; font-size: .55rem; padding: 0 3px; display: inline-flex !important; }
    .sidebar.collapsed .sidebar-nav a svg { width: 27px !important; height: 27px !important; }
    .sidebar.collapsed .nav-divider { opacity: 0; margin: .15rem 0 !important; }
    .sidebar.collapsed .sidebar-user-info { display: none !important; }
    .sidebar.collapsed .sidebar-user { justify-content: center; padding: .75rem 0; }
    .sidebar.collapsed .sidebar-nav a::after { content: attr(data-tooltip); position: absolute; left: calc(100% + 10px); top: 50%; transform: translateY(-50%); background: #1A3C5E; color: #fff; padding: .3rem .8rem; border-radius: 7px; font-size: .78rem; font-weight: 600; white-space: nowrap; pointer-events: none; opacity: 0; transition: opacity .12s; z-index: 200; box-shadow: 0 3px 10px rgba(0,0,0,.2); }
    .sidebar.collapsed .sidebar-nav a:hover::after { opacity: 1; }
    .sidebar-toggle { display: flex; align-items: center; justify-content: center; width: 26px; height: 26px; border-radius: 50%; border: 1.5px solid #e0e4ea; background: #fff; color: #9ca3af; cursor: pointer; flex-shrink: 0; transition: border-color .15s, color .15s, background .15s; }
    .sidebar-toggle:hover { border-color: var(--accent); color: var(--accent); background: rgba(46,134,193,.05); }
    .sidebar-toggle svg { transition: transform .22s cubic-bezier(.4,0,.2,1); flex-shrink: 0; }
    .sidebar.collapsed .sidebar-toggle svg { transform: rotate(180deg); }
    .sidebar-user { padding: .75rem 1rem; border-top: 1px solid #e5e7eb; display: flex; align-items: center; gap: .6rem; }
    .sidebar-user-avatar { width: 32px; height: 32px; border-radius: 50%; overflow: hidden; background: var(--accent); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .sidebar-user-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .sidebar-user-avatar span { color: #fff; font-size: .68rem; font-weight: 700; }
    .sidebar-user-info { min-width: 0; }
    .sidebar-user-name { font-size: .8rem; font-weight: 600; color: var(--primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .sidebar-user-role { font-size: .68rem; color: #9ca3af; }
    .main-content { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
    .topbar { background: #E8ECF0; padding: .3rem 1rem .3rem 1.5rem;
              display: flex; align-items: center; gap: 1rem; font-size: .9rem; min-height: 56px; border-bottom: 1px solid #d5dae0; }

    /* ── Top-nav (portal / employee) ──────────── */
    .portal-layout { min-height: 100vh; }
    .portal-header { background: #fff; border-bottom: 1px solid #d0d5dd; box-shadow: 0 2px 8px rgba(0,0,0,.06); padding: 0 2rem; height: 80px; overflow: visible; display: flex; align-items: center; gap: 2rem; }
    .portal-header img { height: 120px; }
    .portal-header nav { display: flex; gap: 0; height: 100%; align-items: stretch; margin-left: .5rem; }
    .portal-header nav a { color: #4a5568; text-decoration: none; font-size: .9rem; font-weight: 600; padding: 0 1.1rem; display: flex; align-items: center; border-bottom: 3px solid transparent; margin-bottom: -1px; transition: color .2s ease, border-color .2s ease, background .2s ease; }
    .portal-header nav a:hover { color: var(--accent); background: rgba(46,134,193,.05); border-bottom-color: rgba(46,134,193,.45); }
    .portal-header nav a.active { color: var(--accent); border-bottom-color: var(--accent); background: rgba(46,134,193,.04); }
    .portal-content { background: #E8ECF0; min-height: calc(100vh - 80px); padding: 2rem 1.5rem; }

    /* ── Portal user avatar menu ───────────────── */
    .portal-user-menu { position: relative; margin-left: auto; flex-shrink: 0; }
    .portal-avatar { width: 40px; height: 40px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; overflow: hidden; background: var(--primary); border: 2px solid #d0d5dd; transition: border-color .2s ease, box-shadow .2s ease; user-select: none; }
    .portal-avatar:hover { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(46,134,193,.15); }
    .portal-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .portal-avatar-initials { color: #fff; font-size: .78rem; font-weight: 700; letter-spacing: .02em; }
    .portal-dropdown { position: absolute; right: 0; top: calc(100% + 10px); background: #fff; border: 1px solid #d0d5dd; border-radius: 10px; box-shadow: 0 8px 24px rgba(0,0,0,.12); min-width: 180px; z-index: 200; display: none; overflow: hidden; }
    .portal-dropdown.open { display: block; }
    .portal-dropdown-header { padding: .65rem 1rem .5rem; border-bottom: 1px solid #e5e7eb; }
    .portal-dropdown-name { font-size: .85rem; font-weight: 700; color: var(--primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .portal-dropdown a { display: block; padding: .6rem 1rem; font-size: .875rem; color: #374151; text-decoration: none; transition: background .15s, color .15s; }
    .portal-dropdown a:hover { background: #f3f4f6; color: var(--accent); }
    .portal-dropdown hr { border: none; border-top: 1px solid #e5e7eb; margin: .2rem 0; }
    .portal-dropdown button { display: block; width: 100%; text-align: left; padding: .6rem 1rem; font-size: .875rem; color: #dc2626; background: none; border: none; cursor: pointer; transition: background .15s; }
    .portal-dropdown button:hover { background: #fef2f2; }

    /* ── Content body ──────────────────────────── */
    .content-body { padding: 1rem; flex: 1; overflow-y: auto; }
    .page-title { font-size: 1.6rem; color: var(--primary); margin-bottom: 1.5rem; }
    .section-title { font-size: 1.15rem; color: var(--primary); margin: 2rem 0 1rem; }

    /* ── KPI cards ─────────────────────────────── */
    .kpi-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px,1fr)); gap: 1rem; margin-bottom: 2rem; }
    .kpi-card { background: #fff; border-left: 4px solid var(--accent); border-radius: 6px;
                padding: 1.25rem; box-shadow: 0 1px 4px rgba(0,0,0,.07); }
    .kpi-card.kpi-warn  { border-left-color: var(--warning); }
    .kpi-card.kpi-info  { border-left-color: var(--accent); }
    .kpi-card.kpi-success { border-left-color: var(--success); }
    .kpi-number { display: block; font-size: 2rem; font-weight: 700; color: var(--primary); }
    .kpi-label  { font-size: .8rem; color: #777; text-transform: uppercase; letter-spacing: .05em; }

    /* ── Table ─────────────────────────────────── */
    .data-table { width: 100%; border-collapse: collapse; background: #fff;
                  border-radius: 6px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.07); }
    .data-table th { background: var(--primary); color: #fff; padding: .7rem 1rem; text-align: left; font-size: .85rem; }
    .data-table td { padding: .7rem 1rem; border-bottom: 1px solid #eef; font-size: .9rem; }
    .data-table tr:last-child td { border-bottom: none; }
    .data-table tr:hover td { background: #f0f4f8; }
    .data-table tr[data-href] { cursor: pointer; }
    .data-table tr[data-href]:hover td { background: #e8f0f8; }

    /* ── Status badges ─────────────────────────── */
    .badge { display: inline-block; padding: .2em .6em; border-radius: 99px; font-size: .75rem;
             font-weight: 600; text-transform: capitalize; background: #e0e0e0; color: #555; }
    .badge-new               { background: #dbeafe; color: #1e40af; }
    .badge-triaged           { background: #fef3c7; color: #92400e; }
    .badge-scheduled         { background: #d1fae5; color: #065f46; }
    .badge-awaiting_feedback { background: #fee2e2; color: #991b1b; }
    .badge-services_performed{ background: #ede9fe; color: #5b21b6; }
    .badge-invoice_prepared  { background: #fce7f3; color: #9d174d; }
    .badge-billed            { background: #cffafe; color: #155e75; }
    .badge-completed         { background: #d1fae5; color: #065f46; }
    .badge-canceled          { background: #f3f4f6; color: #6b7280; }

    /* ── Alerts ────────────────────────────────── */
    .alert { padding: .85rem 1.25rem; border-radius: 6px; margin-bottom: 1rem; font-size: .9rem; }
    .alert-success { background: #d1fae5; color: #065f46; }
    .alert-error   { background: #fee2e2; color: #991b1b; }
    .alert-warn    { background: #fef3c7; color: #92400e; }
    .alert-info    { background: #dbeafe; color: #1e40af; }

    /* ── Buttons ───────────────────────────────── */
    .btn-link { background: none; border: none; cursor: pointer; color: var(--primary);
                font-size: .9rem; text-decoration: underline; padding: 0; }
    .btn-link:hover { color: var(--accent); }
    .topbar .btn-link { color: var(--primary); }
    .portal-header .btn-link { color: var(--primary); }
    .portal-header .btn-link:hover { color: var(--accent); }
    .btn { display: inline-block; padding: .5rem 1.1rem; border-radius: 5px; font-size: .88rem; font-weight: 600; cursor: pointer; border: none; text-decoration: none; }
    .btn-primary { background: var(--primary); color: #fff; }
    .btn-primary:hover { background: var(--accent); color: #fff; }
    .btn-secondary { background: #e5e7eb; color: #374151; }
    .btn-secondary:hover { background: #d1d5db; }
    .btn-danger { background: var(--danger); color: #fff; }
    .btn-danger:hover { background: #a93226; color: #fff; }
    .btn-sm { padding: .3rem .75rem; font-size: .82rem; }

    /* ── Responsive / mobile (portal + employee top-nav layouts) ──── */
    @media (max-width: 768px) {
        .portal-header { padding: 0 .9rem; height: 60px; gap: .65rem; }
        .portal-header img { height: 50px; }
        .portal-header nav { margin-left: 0; gap: 0; overflow-x: auto; -webkit-overflow-scrolling: touch; scrollbar-width: none; }
        .portal-header nav::-webkit-scrollbar { display: none; }
        .portal-header nav a { padding: 0 .7rem; font-size: .85rem; white-space: nowrap; }
        .portal-content { padding: 1rem .75rem; min-height: calc(100vh - 60px); }
        .page-title { font-size: 1.25rem; margin-bottom: 1rem; }
    }

    /* ════════════════════════════════════════════════════════════════
       Dark mode — toggled via html.dark class
       Persisted in localStorage('adminDarkMode')
    ════════════════════════════════════════════════════════════════ */
    html.dark { color-scheme: dark; --accent: #60a5fa; }
    html.dark body { background: #0f172a; color: #e2e8f0; }

    /* Sidebar */
    html.dark .sidebar { background: #1e293b; border-right-color: #334155; }
    html.dark .sidebar-logo { background: #1e293b; border-bottom-color: #334155; }
    html.dark .sidebar-nav a { color: #cbd5e1; }
    html.dark .sidebar-nav a:hover { background: rgba(96,165,250,.1); color: #60a5fa; border-left-color: rgba(96,165,250,.4); }
    html.dark .sidebar-nav a.active { background: rgba(96,165,250,.15); color: #60a5fa; border-left-color: #60a5fa; }
    html.dark .sidebar.collapsed .sidebar-nav a.active { background: rgba(96,165,250,.15); border-left-color: transparent !important; }
    html.dark .sidebar.collapsed .sidebar-nav a::after { background: #334155; }
    html.dark .sidebar-toggle { background: #1e293b; border-color: #475569; color: #94a3b8; }
    html.dark .sidebar-toggle:hover { border-color: #60a5fa; color: #60a5fa; background: rgba(96,165,250,.08); }
    html.dark .sidebar-user { border-top-color: #334155; }
    html.dark .sidebar-user-name { color: #f1f5f9; }
    html.dark .sidebar-user-role { color: #64748b; }
    html.dark .nav-divider { background: #334155 !important; }

    /* Topbar */
    html.dark .topbar { background: #1e293b; border-bottom-color: #334155; }
    html.dark #topbar-title-slot p { color: #60a5fa !important; }
    html.dark #topbar-title-slot h1 { color: #f1f5f9 !important; }
    html.dark #topbar-title-slot h1 svg { stroke: #f1f5f9; }

    /* Content body */
    html.dark .content-body { background: #0f172a !important; }

    /* KPI cards */
    html.dark .kpi-card { background: #1e293b; }
    html.dark .kpi-number { color: #f1f5f9; }
    html.dark .kpi-label { color: #94a3b8; }

    /* Data tables */
    html.dark .data-table { background: #1e293b; box-shadow: 0 1px 4px rgba(0,0,0,.3); }
    html.dark .data-table th { background: #1a3a5c; }
    html.dark .data-table td { border-bottom-color: #334155; color: #e2e8f0; }
    html.dark .data-table tr:hover td { background: #243554; }
    html.dark .data-table tr[data-href]:hover td { background: #1e3a5f; }

    /* Status badges */
    html.dark .badge { background: #334155; color: #cbd5e1; }
    html.dark .badge-new { background: #1e3a7f; color: #93c5fd; }
    html.dark .badge-triaged { background: #451a03; color: #fde68a; }
    html.dark .badge-scheduled { background: #064e3b; color: #6ee7b7; }
    html.dark .badge-awaiting_feedback { background: #7f1d1d; color: #fca5a5; }
    html.dark .badge-services_performed { background: #2e1065; color: #c4b5fd; }
    html.dark .badge-invoice_prepared { background: #4d0020; color: #f9a8d4; }
    html.dark .badge-billed { background: #164e63; color: #67e8f9; }
    html.dark .badge-completed { background: #064e3b; color: #6ee7b7; }
    html.dark .badge-canceled { background: #1f2937; color: #6b7280; }

    /* Alerts */
    html.dark .alert-success { background: #052e16; color: #86efac; }
    html.dark .alert-error { background: #450a0a; color: #fca5a5; }
    html.dark .alert-warn { background: #451a03; color: #fde68a; }
    html.dark .alert-info { background: #082f49; color: #93c5fd; }

    /* Buttons */
    html.dark .btn-secondary { background: #334155; color: #e2e8f0; }
    html.dark .btn-secondary:hover { background: #475569; }
    html.dark .btn-link { color: #93c5fd; }
    html.dark .topbar .btn-link { color: #93c5fd; }

    /* Form inputs */
    html.dark input[type="text"], html.dark input[type="email"], html.dark input[type="number"],
    html.dark input[type="password"], html.dark input[type="tel"], html.dark input[type="search"],
    html.dark input[type="date"], html.dark input[type="time"], html.dark input[type="url"],
    html.dark textarea, html.dark select {
        background: #1e293b; border-color: #475569 !important; color: #f1f5f9;
    }
    html.dark input::placeholder, html.dark textarea::placeholder { color: #64748b; }
    html.dark label { color: #cbd5e1; }

    /* User avatar dropdown */
    html.dark .portal-dropdown { background: #1e293b; border-color: #334155; }
    html.dark .portal-dropdown-header { border-bottom-color: #334155; }
    html.dark .portal-dropdown-name { color: #f1f5f9; }
    html.dark .portal-dropdown a { color: #e2e8f0; }
    html.dark .portal-dropdown a:hover { background: #263652; color: #60a5fa; }
    html.dark .portal-dropdown hr { border-top-color: #334155; }
    html.dark .portal-dropdown button { color: #fca5a5; }
    html.dark .portal-dropdown button:hover { background: #450a0a; }
    html.dark .portal-avatar { border-color: #475569; }

    /* White card panels (inline style pattern) */
    html.dark [style*="background:#fff"], html.dark [style*="background: #fff"],
    html.dark [style*="background:white"], html.dark [style*="background: white"] { background: #1e293b !important; }

    /* Light gray backgrounds (queue items, row zebra, etc.) */
    html.dark [style*="background:#f9fafb"], html.dark [style*="background: #f9fafb"],
    html.dark [style*="background:#F9FAFB"], html.dark [style*="background:#f8f9fa"],
    html.dark [style*="background: #f8f9fa"], html.dark [style*="background:#F8F9FA"],
    html.dark [style*="background:#f3f4f6"], html.dark [style*="background: #f3f4f6"] { background: #253448 !important; }

    /* Service catalog tag pills */
    html.dark [style*="background:#f0f6ff"], html.dark [style*="background: #f0f6ff"] {
        background: #1e3a5f !important; border-color: #2d5a8e !important; color: #93c5fd !important;
    }

    /* "Default" queue badge */
    html.dark [style*="background:#e0f2fe"], html.dark [style*="background: #e0f2fe"] {
        background: #0c4a6e !important; border-color: #0284c7 !important; color: #38bdf8 !important;
    }

    /* Inline text color overrides */
    html.dark [style*="color:#111"], html.dark [style*="color: #111"] { color: #f1f5f9 !important; }
    html.dark [style*="color:#1e293b"], html.dark [style*="color: #1e293b"] { color: #f1f5f9 !important; }
    html.dark [style*="color:#1A3C5E"], html.dark [style*="color: #1A3C5E"],
    html.dark [style*="color:#1a3c5e"], html.dark [style*="color: #1a3c5e"] { color: #e2e8f0 !important; }
    html.dark [style*="color:#333"], html.dark [style*="color: #333"] { color: #e2e8f0 !important; }
    html.dark [style*="color:#374151"], html.dark [style*="color: #374151"] { color: #e2e8f0 !important; }
    html.dark [style*="color:#4a5568"], html.dark [style*="color: #4a5568"] { color: #cbd5e1 !important; }
    html.dark [style*="color:#444"], html.dark [style*="color: #444"] { color: #cbd5e1 !important; }
    html.dark [style*="color:#555"], html.dark [style*="color: #555"] { color: #94a3b8 !important; }
    html.dark [style*="color:#6b7280"], html.dark [style*="color: #6b7280"] { color: #94a3b8 !important; }
    html.dark [style*="color:#777"], html.dark [style*="color: #777"] { color: #94a3b8 !important; }
    html.dark [style*="color:#888"], html.dark [style*="color: #888"] { color: #64748b !important; }
    html.dark [style*="color:#999"], html.dark [style*="color: #999"] { color: #64748b !important; }
    html.dark [style*="color:#9ca3af"], html.dark [style*="color: #9ca3af"] { color: #64748b !important; }
    html.dark [style*="color:#aaa"], html.dark [style*="color: #aaa"] { color: #64748b !important; }

    /* Inline border overrides */
    html.dark [style*="border:1px solid #ccc"], html.dark [style*="border: 1px solid #ccc"] { border-color: #475569 !important; }
    html.dark [style*="border:1px solid #e5e7eb"], html.dark [style*="border: 1px solid #e5e7eb"] { border-color: #334155 !important; }
    html.dark [style*="border:1px solid #d0d5dd"], html.dark [style*="border: 1px solid #d0d5dd"] { border-color: #334155 !important; }
    html.dark [style*="border:1px solid #d1d5db"], html.dark [style*="border: 1px solid #d1d5db"] { border-color: #334155 !important; }
    html.dark [style*="border-top:1px solid #e5e7eb"], html.dark [style*="border-top: 1px solid #e5e7eb"] { border-top-color: #334155 !important; }
    html.dark [style*="border-bottom:1px solid #e5e7eb"], html.dark [style*="border-bottom: 1px solid #e5e7eb"],
    html.dark [style*="border-bottom:1px solid #eef"], html.dark [style*="border-bottom: 1px solid #eef"],
    html.dark [style*="border-bottom:1px solid #f3f4f6"], html.dark [style*="border-bottom: 1px solid #f3f4f6"] { border-bottom-color: #334155 !important; }

    /* Drag-and-drop queue items */
    html.dark .queue-item, html.dark .invoice-queue-item { background: #253448 !important; border-color: #334155 !important; }

    /* Visit card action bar */
    html.dark [style*="background:#fafafa"], html.dark [style*="background: #fafafa"] { background: #1a2436 !important; }
    html.dark [style*="border-top:1px solid #f0f0f0"], html.dark [style*="border-top: 1px solid #f0f0f0"] { border-top-color: #334155 !important; }

    /* Green tints (clock-in, completion sig badge, verified badge) */
    html.dark [style*="background:#f0fdf4"], html.dark [style*="background: #f0fdf4"] { background: #052e16 !important; border-color: #166534 !important; }
    html.dark [style*="background:#d1fae5"], html.dark [style*="background: #d1fae5"] { background: #052e16 !important; border-color: #166534 !important; }

    /* Red tints (clock-out) */
    html.dark [style*="background:#fef2f2"], html.dark [style*="background: #fef2f2"] { background: #2d0a0a !important; border-color: #7f1d1d !important; }
    html.dark [style*="background:#fee2e2"], html.dark [style*="background: #fee2e2"] { background: #2d0a0a !important; border-color: #7f1d1d !important; }

    /* Purple tints (awaiting-feedback badge, invoice prepared) */
    html.dark [style*="background:#ede9fe"], html.dark [style*="background: #ede9fe"] { background: #1e1148 !important; border-color: #4c1d95 !important; }
    html.dark [style*="background:#f5f3ff"], html.dark [style*="background: #f5f3ff"] { background: #1e1148 !important; }

    /* Blue tints (status tip) */
    html.dark [style*="background:#eff6ff"], html.dark [style*="background: #eff6ff"] { background: #082f49 !important; }
    html.dark [style*="background:#dbeafe"], html.dark [style*="background: #dbeafe"] { background: #082f49 !important; border-color: #1e40af !important; }

    /* Amber/yellow tints (status tip, pending badge) */
    html.dark [style*="background:#fffbeb"], html.dark [style*="background: #fffbeb"],
    html.dark [style*="background:#fef3c7"], html.dark [style*="background: #fef3c7"] { background: #1c1200 !important; border-color: #78350f !important; }

    /* Orange tints (status tip) */
    html.dark [style*="background:#fff7ed"], html.dark [style*="background: #fff7ed"] { background: #2a1000 !important; }

    /* Pink tints (invoice prepared badge) */
    html.dark [style*="background:#fce7f3"], html.dark [style*="background: #fce7f3"] { background: #4d0020 !important; border-color: #9d174d !important; }

    /* Signature images — keep a light gray wash so ink (even white-bg PNGs) stays visible */
    html.dark [data-sig-img] { background: #e2e8f0 !important; border-color: #475569 !important; }

    /* Signature preview popup */
    html.dark #sig-preview-popup { background: #1e293b !important; border-color: #475569 !important; }
    html.dark #sig-preview-img   { background: #e2e8f0 !important; }
    html.dark #sig-preview-cap   { color: #94a3b8 !important; }
</style>
