<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | CICS-MARSU Accreditation System</title>
    <link rel="icon" href="{{ asset('images/logos/cics_logo.png') }}">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 & Bootstrap Icons CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @yield('head')

    <!-- PDF.js CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';</script>

    <style>
        /* ================================================================
           DESIGN SYSTEM — Apple Green & Neutral
           ================================================================ */
        :root,
        [data-theme="light"] {
            --bg-body: #f5f7f3;
            --bg-surface: #ffffff;
            --bg-surface-hover: #f3f7ee;
            --bg-elevated: #ffffff;
            --bg-sidebar: #1f2d18;
            --bg-sidebar-hover: rgba(255,255,255,0.06);
            --bg-sidebar-active: rgba(255,255,255,0.10);

            --text-primary: #243020;
            --text-secondary: #667260;
            --text-tertiary: #9ba494;
            --text-inverse: #ffffff;
            --text-sidebar: #a8adb4;
            --text-sidebar-active: #ffffff;

            --border-color: #e2e8dd;
            --border-subtle: #d4ddcd;

            --accent: #78a22f;
            --accent-hover: #638b24;
            --accent-light: #ecf4df;
            --accent-text: #4f721c;

            --badge-admin-bg: #edf2e9;  --badge-admin-text: #3d5730;
            --badge-faculty-bg: #e2efd3; --badge-faculty-text: #4f721c;
            --badge-accreditor-bg: #f4f5f1; --badge-accreditor-text: #5a6554;

            --card-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 1px 2px rgba(0,0,0,0.06);
            --card-shadow-hover: 0 4px 12px rgba(0,0,0,0.06);

            --table-header-bg: #f7f9f4;
            --table-header-text: #667260;
            --table-row-bg: #ffffff;
            --table-row-alt-bg: #fbfcfd;
            --table-border: #e9ecef;

            --pill-bg: #f4f7f0;
            --pill-text: #586552;
            --pill-active-bg: var(--accent);
            --pill-active-text: #fff;
            --pill-border: #dee2e6;

            --param-card-bg: #ffffff;
            --param-card-border: #e9ecef;
            --param-title-text: #212529;

            --category-header-bg: #f4f7f0;
            --category-header-text: #586552;

            --doc-card-bg: #f8f9fa;
            --doc-card-border: #e9ecef;

            --checklist-bg: #fafbfc;

            --modal-bg: var(--bg-surface);
            --modal-header-bg: var(--accent);
            --modal-header-text: #fff;

            --footer-bg: #f8f9fa;
            --footer-text: #adb5bd;
            --footer-border: #e9ecef;
        }

        [data-theme="dark"] {
            --bg-body: #111318;
            --bg-surface: #1a1d24;
            --bg-surface-hover: #22262e;
            --bg-elevated: #20242c;
            --bg-sidebar: #0d0f13;
            --bg-sidebar-hover: rgba(255,255,255,0.04);
            --bg-sidebar-active: rgba(255,255,255,0.08);

            --text-primary: #e4e6ea;
            --text-secondary: #a3a8b2;
            --text-tertiary: #888e99;
            --text-inverse: #111318;
            --text-sidebar: #9ca3af;
            --text-sidebar-active: #ffffff;

            --border-color: #2a2e36;
            --border-subtle: #333842;

            --accent: #40916c;
            --accent-hover: #52b788;
            --accent-light: #1b3a2a;
            --accent-text: #95d5b2;

            --badge-admin-bg: #1e2a3a;  --badge-admin-text: #7da4cc;
            --badge-faculty-bg: #1b3a2a; --badge-faculty-text: #95d5b2;
            --badge-accreditor-bg: #3a3520; --badge-accreditor-text: #d4b44c;

            --card-shadow: 0 1px 3px rgba(0,0,0,0.2), 0 1px 2px rgba(0,0,0,0.3);
            --card-shadow-hover: 0 4px 16px rgba(0,0,0,0.3);

            --table-header-bg: #15181e;
            --table-header-text: #8b9099;
            --table-row-bg: #1a1d24;
            --table-row-alt-bg: #1e2128;
            --table-border: #2a2e36;

            --pill-bg: #1e2128;
            --pill-text: #8b9099;
            --pill-active-bg: var(--accent);
            --pill-active-text: #fff;
            --pill-border: #333842;

            --param-card-bg: #1a1d24;
            --param-card-border: #2a2e36;
            --param-title-text: #e4e6ea;

            --category-header-bg: #15181e;
            --category-header-text: #8b9099;

            --doc-card-bg: #15181e;
            --doc-card-border: #2a2e36;

            --checklist-bg: #15181e;

            --modal-bg: #1a1d24;
            --modal-header-bg: #15181e;
            --modal-header-text: #e4e6ea;

            --footer-bg: #0d0f13;
            --footer-text: #5c6370;
            --footer-border: #2a2e36;
        }

        /* ==================== BASE ==================== */
        * { box-sizing: border-box; }

        body {
            font-family: 'DM Sans', system-ui, sans-serif;
            background-color: var(--bg-body);
            background-image: linear-gradient(180deg, #f9fbf7 0, var(--bg-body) 320px);
            color: var(--text-primary);
            min-height: 100vh;
            margin: 0;
            -webkit-font-smoothing: antialiased;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        [data-theme="dark"] body {
            background-color: var(--bg-body);
            background-image: none;
        }
        [data-theme="dark"] .top-navbar {
            background: rgba(20, 23, 28, 0.96);
            border-bottom-color: var(--border-color);
        }
        [data-theme="dark"] .page-heading p {
            color: var(--text-secondary);
        }

        /* ==================== SIDEBAR ==================== */
        .sidebar {
            width: 260px;
            background-color: var(--bg-sidebar);
            height: 100vh;
            position: fixed;
            top: 0; left: 0;
            z-index: 1000;
            border-right: 1px solid rgba(255,255,255,0.08);
            transition: background-color 0.3s ease, transform 0.25s ease;
            display: flex;
            flex-direction: column;
        }

        .sidebar-brand {
            padding: 1.4rem 1.25rem;
            border-bottom: 1px solid rgba(255,255,255,0.06);
            flex-shrink: 0;
        }

        .sidebar-menu {
            padding: 0.75rem 0 2rem;
            list-style: none;
            margin: 0;
            overflow-y: auto;
            flex-grow: 1;
        }

        /* Custom scrollbar for sidebar menu */
        .sidebar-menu::-webkit-scrollbar {
            width: 5px;
        }
        .sidebar-menu::-webkit-scrollbar-track {
            background: transparent;
        }
        .sidebar-menu::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 4px;
        }
        .sidebar-menu::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.4);
        }

        .sidebar-menu .menu-header {
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: var(--text-sidebar);
            padding: 1.25rem 1.25rem 0.5rem;
            font-weight: 600;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 0.68rem 1.25rem;
            color: var(--text-sidebar);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.875rem;
            transition: all 0.15s ease;
            border-left: 3px solid transparent;
            margin: 1px 0;
        }

        .sidebar-menu a:hover {
            background: var(--bg-sidebar-hover);
            color: var(--text-sidebar-active);
        }

        .sidebar-menu a.active {
            background: rgba(163, 200, 93, 0.16);
            color: var(--text-sidebar-active);
            border-left-color: var(--accent);
        }

        /* ==================== MAIN LAYOUT ==================== */
        .main-wrapper {
            margin-left: 260px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: margin-left 0.25s ease;
        }

        body.sidebar-collapsed .sidebar {
            transform: translateX(-100%);
        }

        body.sidebar-collapsed .main-wrapper {
            margin-left: 0;
        }

        .top-navbar {
            background: rgba(255,255,255,0.88);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--border-color);
            padding: 0.9rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 999;
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }

        .sidebar-toggle-btn {
            align-items: center;
            background: var(--bg-surface);
            border: 1px solid var(--border-subtle);
            border-radius: 6px;
            color: var(--text-secondary);
            display: inline-flex;
            font-size: 1.1rem;
            height: 34px;
            justify-content: center;
            width: 34px;
        }
        .sidebar-toggle-btn:hover { background: var(--bg-surface-hover); color: var(--text-primary); }

        .content-body {
            padding: 2rem;
            flex: 1;
        }

        /* ==================== CARDS ==================== */
        .card-custom {
            border: 1px solid var(--border-color);
            border-radius: 10px;
            box-shadow: var(--card-shadow);
            background: var(--bg-surface);
            transition: all 0.2s ease;
        }
        .card-custom:hover {
            box-shadow: var(--card-shadow-hover);
        }

        .page-heading {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1.75rem;
        }
        .page-heading h1, .page-heading h3 {
            color: var(--text-primary);
            font-size: 1.45rem;
            letter-spacing: 0;
        }
        .page-heading p { color: var(--text-secondary); font-size: 0.9rem; }
        .metric-card { padding: 1.15rem; min-height: 132px; }
        .metric-label { color: var(--text-secondary); font-size: 0.71rem; font-weight: 700; letter-spacing: 0.07em; text-transform: uppercase; }
        .metric-value { color: var(--text-primary); font-size: 1.8rem; font-weight: 700; line-height: 1.15; }
        .metric-icon {
            width: 42px; height: 42px; display: inline-flex; align-items: center; justify-content: center;
            background: var(--accent-light); color: var(--accent-text); border-radius: 9px; font-size: 1.2rem;
        }
        .area-card { padding: 1.15rem; border-top: 3px solid var(--accent); }
        .area-card .area-title { color: var(--text-primary); font-size: 1rem; font-weight: 700; }
        .area-card .area-description { color: var(--text-secondary); font-size: 0.82rem; line-height: 1.55; }
        .section-title { color: var(--text-primary); font-size: 1rem; font-weight: 700; }
        .section-link { color: var(--accent-text); font-size: 0.8rem; font-weight: 700; text-decoration: none; }
        .section-link:hover { color: var(--accent-hover); }
        .quiet-notice { background: var(--accent-light); border: 1px solid #cfe1b9; border-radius: 9px; color: #3d5d1e; }

        /* ==================== BADGES ==================== */
        .badge-role {
            font-size: 0.7rem;
            padding: 0.3em 0.65em;
            border-radius: 4px;
            font-weight: 600;
            letter-spacing: 0.2px;
        }
        .badge-role-admin { background: var(--badge-admin-bg); color: var(--badge-admin-text); }
        .badge-role-faculty { background: var(--badge-faculty-bg); color: var(--badge-faculty-text); }
        .badge-role-accreditor { background: var(--badge-accreditor-bg); color: var(--badge-accreditor-text); }

        /* ==================== BREADCRUMB ==================== */
        .breadcrumb-custom {
            background: transparent;
            font-size: 0.8rem;
            padding: 0;
            margin-bottom: 0.75rem;
        }
        .breadcrumb-custom a {
            color: var(--accent-text);
            text-decoration: none;
            font-weight: 500;
        }
        .breadcrumb-custom a:hover { text-decoration: underline; }
        .breadcrumb-custom .active { color: var(--text-secondary); }

        /* ==================== BUTTONS ==================== */
        .btn-accent {
            background-color: var(--accent);
            color: #fff;
            border: 1px solid var(--accent);
            border-radius: 7px;
            font-weight: 500;
            font-size: 0.8rem;
            transition: all 0.15s ease;
        }
        .btn-accent:hover {
            background-color: var(--accent-hover);
            color: #fff;
        }

        /* Legacy name compatibility */
        .btn-apple-green { background-color: var(--accent); color: #fff; border: 1px solid var(--accent); border-radius: 7px; font-weight: 600; }
        .btn-apple-green:hover { background-color: var(--accent-hover); color: #fff; }
        .bg-apple-green { background-color: var(--accent) !important; color: #fff; }
        .bg-apple-dark { background-color: var(--bg-sidebar) !important; color: #fff; }
        .text-apple-green { color: var(--accent-text) !important; }
        .text-apple-dark { color: var(--text-primary) !important; }

        .btn-xs { font-size: 0.75rem; padding: 0.2rem 0.5rem; }

        .btn-outline-success {
            color: var(--accent-text);
            border-color: #b8ce98;
            border-radius: 7px;
        }
        .btn-outline-success:hover {
            color: #fff;
            background-color: var(--accent);
            border-color: var(--accent);
        }
        .btn-outline-dark {
            color: var(--text-secondary);
            border-color: var(--border-subtle);
            border-radius: 7px;
        }
        .btn-outline-dark:hover { color: var(--text-primary); background: var(--bg-surface-hover); border-color: var(--border-subtle); }
        .alert-apple-green {
            background: linear-gradient(135deg, rgba(120, 162, 47, 0.12) 0%, rgba(37, 56, 34, 0.18) 100%);
            border: 1px solid rgba(120, 162, 47, 0.35);
            color: var(--text-primary);
            border-radius: 9px;
        }

        .btn:focus-visible, .form-control:focus, .form-select:focus, .form-check-input:focus {
            box-shadow: 0 0 0 0.22rem rgba(120, 162, 47, 0.18);
            border-color: var(--accent);
        }

        /* ==================== AREA DETAIL HEADER & FILTER BAR ==================== */
        .area-detail-header {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 1.5rem 1.75rem;
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.04);
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1.5rem;
            flex-wrap: wrap;
        }

        .area-code-badge {
            display: inline-flex;
            align-items: center;
            background: var(--accent-light);
            color: var(--accent-text);
            font-size: 0.78rem;
            font-weight: 800;
            padding: 0.25rem 0.65rem;
            border-radius: 6px;
            border: 1px solid rgba(120, 162, 47, 0.25);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .area-detail-heading h3 {
            color: var(--text-primary);
            font-size: 1.55rem;
            font-weight: 800;
            letter-spacing: -0.01em;
            line-height: 1.3;
        }

        .area-detail-heading p {
            color: var(--text-secondary);
            font-size: 0.88rem;
            line-height: 1.45;
        }

        .evidence-progress {
            margin-top: 1rem;
            max-width: 380px;
            padding: 0.7rem 0.9rem;
            background: var(--doc-card-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
        }

        .area-detail-actions {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            flex-wrap: wrap;
        }

        .area-detail-actions .btn {
            font-weight: 600;
            font-size: 0.85rem;
            padding: 0.45rem 0.95rem;
            border-radius: 7px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.04);
            transition: all 0.2s ease;
        }

        .area-filter-bar {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 0.65rem 1rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .area-filter-bar .pill-group {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .area-filter-bar .search-box-wrapper {
            flex: 0 1 280px;
            min-width: 240px;
        }

        .btn-pill-filter {
            background: var(--pill-bg);
            color: var(--pill-text);
            border: 1px solid var(--pill-border);
            border-radius: 7px;
            padding: 0.42rem 0.9rem;
            font-size: 0.82rem;
            font-weight: 600;
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        .btn-pill-filter:hover {
            background: var(--bg-surface-hover);
            color: var(--text-primary);
            border-color: var(--border-subtle);
            transform: translateY(-1px);
        }

        .btn-pill-filter.active {
            background: var(--pill-active-bg);
            color: var(--pill-active-text);
            border-color: var(--pill-active-bg);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        /* ==================== PARAMETER CARD (Hierarchy) ==================== */
        .criterion-card-dark {
            background: var(--param-card-bg);
            border: 1px solid var(--param-card-border);
            border-radius: 8px;
            color: var(--text-primary);
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }

        .param-title,
        .parameter-title,
        .parameter-card-header h4,
        .parameter-card-header h5 {
            color: var(--text-primary);
            font-weight: 800;
            font-size: 1.35rem;
            letter-spacing: -0.01em;
            line-height: 1.3;
            margin-bottom: 0.25rem;
        }

        .parameter-eyebrow {
            color: var(--accent-text);
            font-size: 0.82rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-bottom: 0.25rem;
        }

        /* ==================== UNIFIED CATEGORY HEADER STYLE ==================== */
        .category-header {
            align-items: center;
            display: flex;
            justify-content: space-between;
            background: linear-gradient(135deg, #1e331c 0%, #2b4528 100%);
            border: 1px solid #162415;
            border-radius: 7px;
            padding: 0.55rem 0.85rem;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.08);
            transition: background-color 0.25s ease, border-color 0.25s ease, box-shadow 0.25s ease;
        }
        .category-header h6 {
            color: #ffffff !important;
            font-weight: 700;
            font-size: 0.82rem;
            letter-spacing: 0.05em;
            margin: 0;
        }
        .category-header h6 i {
            color: #8bc34a !important;
        }
        .category-header .category-add-button {
            background-color: rgba(139, 195, 74, 0.22);
            border: 1px solid rgba(139, 195, 74, 0.5);
            color: #ffffff;
            font-weight: 600;
            font-size: 0.75rem;
            padding: 0.25rem 0.65rem;
            border-radius: 5px;
            transition: all 0.2s ease;
        }
        .category-header .category-add-button:hover {
            background-color: #8bc34a;
            color: #122111;
            border-color: #8bc34a;
        }

        /* Dark mode adaptivity for unified category header */
        [data-theme="dark"] .category-header {
            background: linear-gradient(135deg, #132113 0%, #1c2f1b 100%) !important;
            border-color: #273e25 !important;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.35);
        }
        [data-theme="dark"] .category-header h6 {
            color: #e4e6ea !important;
        }
        [data-theme="dark"] .category-header h6 i {
            color: #52b788 !important;
        }
        [data-theme="dark"] .category-header .category-add-button {
            background-color: rgba(82, 183, 136, 0.2);
            border-color: rgba(82, 183, 136, 0.45);
            color: #52b788;
        }
        [data-theme="dark"] .category-header .category-add-button:hover {
            background-color: #52b788;
            color: #0b1a11;
            border-color: #52b788;
        }

        /* ==================== MATRIX TABLE ==================== */
        .table-matrix-dark {
            --bs-table-bg: var(--table-row-bg);
            --bs-table-color: var(--text-primary);
            --bs-table-hover-bg: var(--bg-surface-hover);
            border-color: var(--table-border);
            font-size: 0.85rem;
        }
        .table-matrix-dark thead {
            background: var(--table-header-bg);
        }
        .table-matrix-dark thead th {
            color: var(--table-header-text);
            font-weight: 600;
            font-size: 0.7rem;
            letter-spacing: 0.5px;
            border-bottom: 1px solid var(--table-border);
            padding: 0.6rem 0.75rem;
        }
        .table-matrix-dark td {
            border-color: var(--table-border);
            vertical-align: top;
            padding: 0.75rem;
        }

        .table {
            --bs-table-bg: transparent;
            --bs-table-color: var(--text-primary);
            --bs-table-hover-bg: var(--bg-surface-hover);
            --bs-table-border-color: var(--table-border);
        }
        .table > :not(caption) > * > * { padding: 0.9rem 1rem; }
        .table thead th { color: var(--table-header-text); font-size: 0.7rem; font-weight: 700; letter-spacing: 0.06em; }
        .card-header, .card-footer { background: transparent !important; border-color: var(--border-color) !important; }
        .form-control, .form-select { border-color: var(--border-subtle); border-radius: 7px; color: var(--text-primary); }
        .form-control::placeholder { color: var(--text-tertiary); }
        .badge { font-weight: 600; letter-spacing: 0.01em; }
        .bg-primary, .bg-info, .bg-success { background-color: var(--accent) !important; }
        .text-primary, .text-info, .text-success { color: var(--accent-text) !important; }
        .border-primary, .border-info, .border-success { border-color: #b8ce98 !important; }
        .alert-success { background: var(--accent-light); border-color: #cfe1b9 !important; color: #2d4516; }

        /* ==================== COMPREHENSIVE DARK MODE OVERRIDES ==================== */
        [data-theme="dark"] .alert-success,
        [data-theme="dark"] .quiet-notice {
            background-color: rgba(64, 145, 108, 0.22) !important;
            border-color: rgba(82, 183, 136, 0.45) !important;
            color: #95d5b2 !important;
        }
        [data-theme="dark"] .alert-success *,
        [data-theme="dark"] .quiet-notice * {
            color: #95d5b2 !important;
        }
        [data-theme="dark"] .alert-info {
            background-color: rgba(13, 110, 253, 0.2) !important;
            border-color: rgba(13, 110, 253, 0.4) !important;
            color: #7ab2ff !important;
        }
        [data-theme="dark"] .alert-info * {
            color: #7ab2ff !important;
        }
        [data-theme="dark"] .alert-warning {
            background-color: rgba(255, 193, 7, 0.2) !important;
            border-color: rgba(255, 193, 7, 0.4) !important;
            color: #ffe082 !important;
        }
        [data-theme="dark"] .alert-warning * {
            color: #ffe082 !important;
        }
        [data-theme="dark"] .alert-danger {
            background-color: rgba(220, 53, 69, 0.2) !important;
            border-color: rgba(220, 53, 69, 0.4) !important;
            color: #f8949d !important;
        }
        [data-theme="dark"] .alert-danger * {
            color: #f8949d !important;
        }

        [data-theme="dark"] .text-muted {
            color: #a3a8b2 !important;
        }
        .checklist-item-text,
        .checklist-item-title {
            color: var(--text-primary, #1e293b);
        }
        [data-theme="dark"] .checklist-item-text,
        [data-theme="dark"] .checklist-item-title {
            color: #ffffff !important;
        }

        [data-theme="dark"] .text-secondary {
            color: #b0b5c0 !important;
        }
        [data-theme="dark"] .text-dark {
            color: #e4e6ea !important;
        }
        [data-theme="dark"] strong.text-dark,
        [data-theme="dark"] b.text-dark,
        [data-theme="dark"] h1, [data-theme="dark"] h2,
        [data-theme="dark"] h3, [data-theme="dark"] h4,
        [data-theme="dark"] h5, [data-theme="dark"] h6 {
            color: #ffffff !important;
        }

        [data-theme="dark"] .area-personnel {
            color: #d1d5db !important;
        }
        [data-theme="dark"] .area-personnel div,
        [data-theme="dark"] .area-personnel span,
        [data-theme="dark"] .area-personnel p {
            color: #d1d5db !important;
        }
        [data-theme="dark"] .area-personnel strong,
        [data-theme="dark"] .area-personnel b {
            color: #ffffff !important;
        }
        [data-theme="dark"] .area-title {
            color: #ffffff !important;
        }
        [data-theme="dark"] .area-description {
            color: #b0b5c0 !important;
        }

        [data-theme="dark"] .card-custom,
        [data-theme="dark"] .card {
            background-color: var(--bg-surface);
            border-color: var(--border-color);
            color: var(--text-primary);
        }

        [data-theme="dark"] .bg-light {
            background-color: var(--bg-elevated) !important;
            color: var(--text-primary) !important;
        }
        [data-theme="dark"] .badge.bg-light,
        [data-theme="dark"] .badge.bg-secondary {
            background-color: #282c35 !important;
            color: #e4e6ea !important;
            border-color: #3a404d !important;
        }
        [data-theme="dark"] .bg-apple-dark {
            background-color: #282c35 !important;
            color: #ffffff !important;
        }

        [data-theme="dark"] .table {
            color: var(--text-primary);
            --bs-table-color: var(--text-primary);
            --bs-table-striped-color: var(--text-primary);
            --bs-table-hover-color: var(--text-primary);
        }
        [data-theme="dark"] .table > :not(caption) > * > * {
            background-color: transparent;
            color: var(--text-primary);
            border-bottom-color: var(--border-color);
        }
        [data-theme="dark"] .table-hover > tbody > tr:hover > * {
            background-color: var(--bg-surface-hover) !important;
            color: var(--text-primary) !important;
        }

        [data-theme="dark"] .select2-dropdown {
            background-color: var(--modal-bg) !important;
            border-color: var(--border-color) !important;
            color: var(--text-primary) !important;
        }
        [data-theme="dark"] .select2-container--default .select2-selection--single,
        [data-theme="dark"] .select2-container--default .select2-selection--multiple {
            background-color: var(--bg-body) !important;
            border-color: var(--border-color) !important;
            color: var(--text-primary) !important;
        }
        [data-theme="dark"] .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: var(--text-primary) !important;
        }
        [data-theme="dark"] .select2-container--default .select2-results__option--selectable {
            background-color: var(--modal-bg) !important;
            color: var(--text-primary) !important;
        }
        [data-theme="dark"] .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable {
            background-color: var(--accent) !important;
            color: #ffffff !important;
        }

        .checklist-cell {
            background: var(--checklist-bg);
        }

        .compression-option {
            background: var(--bg-surface-hover);
            border: 1px solid var(--border-color);
            border-radius: 7px;
            padding: 0.85rem 1rem;
        }
        .compression-option .form-check {
            margin: 0;
            min-height: 1.5rem;
            padding-left: 2.6rem;
        }
        .compression-option .form-check-input {
            margin-left: -2.6rem;
            margin-top: 0.1rem;
        }
        .compression-option small { margin-left: 2.6rem; }

        [data-theme="dark"] .compression-option {
            background: var(--bg-elevated);
            border-color: var(--border-color);
        }

        /* ==================== DOCUMENT CARD (in table) ==================== */
        .doc-file-card {
            background: var(--doc-card-bg);
            border: 1px solid var(--doc-card-border);
            border-radius: 6px;
            padding: 0.5rem 0.65rem;
            margin-bottom: 0.4rem;
            transition: background-color 0.15s ease;
        }
        .doc-file-card:hover {
            background: var(--bg-surface-hover);
        }

        /* ==================== MODAL OVERRIDES ==================== */
        [data-theme="dark"] .modal-content {
            background-color: var(--modal-bg);
            border-color: var(--border-color);
            color: var(--text-primary);
        }
        [data-theme="dark"] .modal-header {
            background-color: var(--modal-header-bg) !important;
            color: var(--modal-header-text) !important;
            border-bottom-color: var(--border-color);
        }
        [data-theme="dark"] .modal-footer {
            background-color: var(--bg-body) !important;
            border-top-color: var(--border-color);
        }
        [data-theme="dark"] .form-control,
        [data-theme="dark"] .form-select {
            background-color: var(--bg-body);
            border-color: var(--border-color);
            color: var(--text-primary);
        }
        [data-theme="dark"] .form-control:focus,
        [data-theme="dark"] .form-select:focus {
            background-color: var(--bg-elevated);
            border-color: var(--accent);
            color: var(--text-primary);
        }
        [data-theme="dark"] .form-label { color: var(--text-secondary); }
        [data-theme="dark"] .alert { border: 1px solid var(--border-color); }

        /* ==================== FOOTER ==================== */
        footer.site-footer {
            background: var(--footer-bg);
            border-top: 1px solid var(--footer-border);
            color: var(--footer-text);
            transition: all 0.3s ease;
        }

        /* ==================== THEME TOGGLE ==================== */
        .theme-toggle-btn {
            width: 34px; height: 34px;
            border-radius: 7px;
            border: 1px solid var(--border-color);
            background: var(--bg-surface);
            color: var(--text-secondary);
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            transition: all 0.15s ease;
            font-size: 1rem;
        }
        .theme-toggle-btn:hover {
            border-color: var(--accent);
            color: var(--accent-text);
        }

        /* ==================== UTILITIES ==================== */
        .fs-7 { font-size: 0.875rem !important; }
        .fs-8 { font-size: 0.8rem !important; }
        .fw-500 { font-weight: 500 !important; }
        .text-accent { color: var(--accent-text) !important; }
        .bg-accent-light { background-color: var(--accent-light) !important; }

        /* Statement code badge */
        .code-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 36px;
            padding: 2px 8px;
            border-radius: 5px;
            font-size: 0.72rem;
            font-weight: 700;
            background: var(--accent);
            color: #fff;
            flex-shrink: 0;
        }
        .code-badge.depth-1 {
            background: var(--text-secondary);
        }
        .code-badge.depth-2 {
            background: var(--text-tertiary);
        }

        /* File count badge */
        .file-count-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.72rem;
            font-weight: 500;
            background: var(--doc-card-bg);
            border: 1px solid var(--doc-card-border);
            color: var(--text-secondary);
        }

        /* Watermark */
        .watermark-overlay {
            position: absolute; top: 0; left: 0;
            width: 100%; height: 100%;
            pointer-events: none; z-index: 10;
            display: flex; align-items: center; justify-content: center;
            opacity: 0.15; user-select: none; overflow: hidden;
        }
        .watermark-text {
            font-size: 2rem; font-weight: 800; color: #dc3545;
            transform: rotate(-30deg); text-align: center;
            text-transform: uppercase; letter-spacing: 2px;
        }

        /* Smooth transitions for all themed elements */
        .top-navbar, .content-body, .card-custom, .criterion-card-dark,
        .table-matrix-dark, .category-header, .doc-file-card, footer.site-footer,
        .btn-pill-filter, .code-badge, .file-count-badge, .checklist-cell {
            transition: background-color 0.25s ease, border-color 0.25s ease, color 0.25s ease;
        }
        .sidebar {
            transition: background-color 0.25s ease, border-color 0.25s ease, color 0.25s ease, transform 0.25s ease;
        }

        /* Dark mode overrides for Bootstrap components */
        [data-theme="dark"] .bg-white { background-color: var(--bg-surface) !important; color: var(--text-primary); }
        [data-theme="dark"] .border { border-color: var(--border-color) !important; }
        [data-theme="dark"] .shadow-sm { box-shadow: var(--card-shadow) !important; }
        [data-theme="dark"] .text-dark { color: #e4e6ea !important; }
        [data-theme="dark"] .text-muted { color: #a3a8b2 !important; }
        [data-theme="dark"] .text-secondary { color: #b0b5c0 !important; }
        [data-theme="dark"] .btn-outline-dark {
            border-color: var(--border-subtle);
            color: #b0b5c0;
        }
        [data-theme="dark"] .btn-outline-dark:hover {
            background: var(--bg-surface-hover);
            color: #ffffff;
            border-color: var(--accent);
        }
        [data-theme="dark"] .btn-outline-danger {
            border-color: #5c2b2b;
            color: #f8949d;
        }
        [data-theme="dark"] .bg-light {
            background-color: var(--bg-elevated) !important;
            color: var(--text-primary) !important;
        }
        [data-theme="dark"] .dropdown-menu {
            background-color: var(--modal-bg) !important;
            border-color: var(--border-color) !important;
            color: var(--text-primary) !important;
            box-shadow: 0 8px 24px rgba(0,0,0,0.5) !important;
        }
        [data-theme="dark"] .dropdown-item {
            color: var(--text-primary) !important;
        }
        [data-theme="dark"] .dropdown-item:hover,
        [data-theme="dark"] .dropdown-item:focus {
            background-color: var(--bg-surface-hover) !important;
            color: var(--accent-text) !important;
        }
        [data-theme="dark"] .accordion-item {
            background-color: var(--bg-surface) !important;
            border-color: var(--border-color) !important;
            color: var(--text-primary) !important;
        }
        [data-theme="dark"] .accordion-button {
            background-color: var(--bg-elevated) !important;
            color: #ffffff !important;
        }
        [data-theme="dark"] .accordion-button:not(.collapsed) {
            background-color: var(--accent-light) !important;
            color: var(--accent-text) !important;
        }
        [data-theme="dark"] .breadcrumb-custom .active {
            color: #a3a8b2;
        }

        @media (max-width: 767.98px) {
            .sidebar { position: static; width: 100%; height: auto; min-height: auto; }
            .sidebar-brand { border-bottom: 0; }
            .sidebar-menu { display: flex; gap: 0.25rem; overflow-x: auto; overflow-y: visible; flex-grow: 0; padding: 0 0.75rem 0.75rem; }
            .sidebar-menu .menu-header { display: none; }
            .sidebar-menu li { flex: 0 0 auto; }
            .sidebar-menu a { border-left: 0; border-bottom: 2px solid transparent; padding: 0.5rem 0.75rem; border-radius: 6px; }
            .sidebar-menu a.active { border-left-color: transparent; border-bottom-color: var(--accent); }
            .main-wrapper { margin-left: 0; }
            body.sidebar-collapsed .sidebar { display: none; transform: none; }
            .top-navbar { padding: 0.75rem 1rem; }
            .content-body { padding: 1.25rem 1rem; }
            .page-heading { align-items: stretch; flex-direction: column; }
        }
    </style>

    @yield('styles')
</head>
<body>

    <!-- Sidebar Navigation -->
    <aside class="sidebar">
        <div class="sidebar-brand d-flex align-items-center gap-2">
            <div class="rounded d-flex align-items-center justify-content-center" style="width:40px; height:40px; font-weight:700; font-size:0.75rem; background: var(--accent); color:#fff; border-radius:6px;">
                <img src="{{ asset('images/logos/cics_logo.png') }}" alt="" width="50px" height="50px">
            </div>
            <div>
                <h6 class="mb-0 text-white fw-600" style="font-size: 0.85rem; font-weight:600;">SUC Accreditation</h6>
                <small style="font-size: 0.68rem; color: var(--text-sidebar);">CICS — MarSU</small>
            </div>
        </div>

        <ul class="sidebar-menu">
            <li class="menu-header">Main Menu</li>
            <li>
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-grid-1x2"></i> Dashboard
                </a>
            </li>

            @if(auth()->user()->isAdmin())
                <li class="menu-header">Administration</li>
                <li>
                    <a href="{{ route('admin.areas.index') }}" class="{{ request()->routeIs('admin.areas*') ? 'active' : '' }}">
                        <i class="bi bi-folder2"></i> Areas & Parameters
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                        <i class="bi bi-people"></i> User Accounts
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.assignments.index') }}" class="{{ request()->routeIs('admin.assignments*') ? 'active' : '' }}">
                        <i class="bi bi-person-check"></i> Assignments
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.audit_logs.index') }}" class="{{ request()->routeIs('admin.audit_logs*') ? 'active' : '' }}">
                        <i class="bi bi-clock-history"></i> Audit Logs
                    </a>
                </li>
            @endif

            <li class="menu-header">Accreditation</li>
            <li>
                <a href="{{ route('accreditor.browse') }}" class="{{ request()->routeIs('accreditor*') ? 'active' : '' }}">
                    <i class="bi bi-archive"></i> Browse Areas
                </a>
            </li>
            <li>
                <a href="{{ route('compliance-reports.index') }}" class="{{ request()->routeIs('compliance-reports*') ? 'active' : '' }}">
                    <i class="bi bi-clipboard2-check"></i> Compliance Report
                </a>
            </li>
            <li>
                <a href="{{ route('program-performance-compliance.index') }}" class="{{ request()->routeIs('program-performance-compliance*') ? 'active' : '' }}">
                    <i class="bi bi-bar-chart-line"></i> Program Performance Compliance
                </a>
            </li>
            <li>
                <a href="{{ route('technical-review-approval.index') }}" class="{{ request()->routeIs('technical-review-approval*') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-check"></i> Technical Review and Approval
                </a>
            </li>
            <li>
                <a href="{{ route('copc.index') }}" class="{{ request()->routeIs('copc.*') ? 'active' : '' }}">
                    <i class="bi bi-award"></i> COPC
                </a>
            </li>
        </ul>
    </aside>

    <!-- Main Wrapper -->
    <div class="main-wrapper">
        <!-- Top Navbar -->
        <header class="top-navbar">
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="sidebar-toggle-btn" id="sidebarToggleBtn" title="Hide navigation" aria-label="Hide navigation" aria-expanded="true">
                    <i class="bi bi-list"></i>
                </button>
                <span class="fw-500" style="font-size: 0.85rem; color: var(--text-secondary);">Document Management System</span>
            </div>
            <div class="d-flex align-items-center gap-3">
                <!-- Theme Toggle -->
                <button type="button" class="theme-toggle-btn" id="themeToggleBtn" title="Toggle theme">
                    <i class="bi bi-moon" id="themeIcon"></i>
                </button>

                @php
                    $unreadNotifications = auth()->user()->unreadNotifications()->latest()->take(8)->get();
                @endphp
                <div class="dropdown">
                    <button type="button" class="btn btn-sm btn-outline-secondary position-relative" data-bs-toggle="dropdown" aria-expanded="false" title="Notifications">
                        <i class="bi bi-bell"></i>
                        <span id="notificationBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger {{ $unreadNotifications->isEmpty() ? 'd-none' : '' }}">{{ $unreadNotifications->count() }}</span>
                    </button>
                    <div id="notificationMenu" class="dropdown-menu dropdown-menu-end p-0 shadow" style="width: 340px; max-width: calc(100vw - 2rem);">
                        <div class="px-3 py-2 border-bottom fw-semibold">Notifications</div>
                        <div id="notificationItems">
                        @forelse($unreadNotifications as $notification)
                            <a class="dropdown-item text-wrap py-2 notification-item" data-notification-id="{{ $notification->id }}" href="{{ isset($notification->data['area_id']) ? route('accreditor.show_area', $notification->data['area_id']) : route('dashboard') }}">
                                <span class="fw-semibold d-block fs-7">{{ $notification->data['title'] }}</span>
                                <small class="text-muted">{{ $notification->data['message'] }}</small>
                            </a>
                        @empty
                            <div class="px-3 py-3 text-muted fs-7">No unread notifications.</div>
                        @endforelse
                        </div>
                        <a href="{{ route('notifications.index') }}" class="dropdown-item text-center border-top py-2 fs-7 fw-semibold">View all notifications</a>
                    </div>
                </div>

                <div class="text-end">
                    <div class="fw-600" style="font-size: 0.85rem; color: var(--text-primary);">{{ auth()->user()->name }}</div>
                    <div>
                        @if(auth()->user()->isAdmin())
                            <span class="badge badge-role badge-role-admin">Admin</span>
                        @elseif(auth()->user()->isFaculty())
                            <span class="badge badge-role badge-role-faculty">Faculty</span>
                        @else
                            <span class="badge badge-role badge-role-accreditor">Accreditor</span>
                        @endif
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-danger" style="font-size:0.78rem;" title="Logout">
                        <i class="bi bi-box-arrow-right"></i>
                    </button>
                </form>
            </div>
        </header>

        <!-- Main Body -->
        <main class="content-body">
            @yield('content')
        </main>

        <footer class="site-footer py-3 text-center fs-8 mt-auto">
            <small>&copy; {{ date('Y') }} College of Information and Computing Sciences — Marinduque State University.</small>
        </footer>
    </div>

    <!-- Include PDF Viewer Modal Component -->
    @include('components.pdf-viewer-modal')

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    @php
        $flashToast = null;

        if (session('success')) {
            $successMessage = session('success');
            $normalizedMessage = strtolower($successMessage);

            if (str_contains($normalizedMessage, 'deleted') || str_contains($normalizedMessage, 'removed')) {
                $flashToast = ['icon' => 'error', 'title' => 'Deleted successfully', 'text' => $successMessage];
            } elseif (str_contains($normalizedMessage, 'upload')) {
                $flashToast = ['icon' => 'success', 'title' => 'Upload complete', 'text' => $successMessage];
            } elseif (str_contains($normalizedMessage, 'compress')) {
                $flashToast = ['icon' => 'success', 'title' => 'File compressed', 'text' => $successMessage];
            } elseif (str_contains($normalizedMessage, 'updated') || str_contains($normalizedMessage, 'status updated')) {
                $flashToast = ['icon' => 'success', 'title' => 'Updated successfully', 'text' => $successMessage];
            } elseif (str_contains($normalizedMessage, 'created') || str_contains($normalizedMessage, 'added') || str_contains($normalizedMessage, 'assigned')) {
                $flashToast = ['icon' => 'success', 'title' => 'Saved successfully', 'text' => $successMessage];
            } else {
                $flashToast = ['icon' => 'success', 'title' => 'Action completed', 'text' => $successMessage];
            }
        } elseif (session('warning')) {
            $flashToast = ['icon' => 'warning', 'title' => 'Action completed with warnings', 'text' => session('warning')];
        } elseif (session('error')) {
            $flashToast = ['icon' => 'error', 'title' => 'Action failed', 'text' => session('error')];
        } elseif ($errors->any()) {
            $flashToast = ['icon' => 'error', 'title' => 'Action needs attention', 'text' => implode("\n", $errors->all())];
        }
    @endphp

    <script>
        const flashToast = @json($flashToast ?? null);

        if (flashToast) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: flashToast.icon,
                title: flashToast.title,
                text: flashToast.text,
                showConfirmButton: false,
                timer: flashToast.icon === 'error' ? 7000 : 4500,
                timerProgressBar: true,
                customClass: { popup: 'accredms-toast' },
            });
        }

        // Theme Management
        const themeBtn = document.getElementById('themeToggleBtn');
        const themeIcon = document.getElementById('themeIcon');
        const html = document.documentElement;
        const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');

        function applySidebarState(isCollapsed) {
            document.body.classList.toggle('sidebar-collapsed', isCollapsed);
            sidebarToggleBtn?.setAttribute('aria-expanded', String(!isCollapsed));
            sidebarToggleBtn?.setAttribute('title', isCollapsed ? 'Show navigation' : 'Hide navigation');
            sidebarToggleBtn?.setAttribute('aria-label', isCollapsed ? 'Show navigation' : 'Hide navigation');
        }

        applySidebarState(localStorage.getItem('accredms_sidebar_collapsed') === 'true');

        sidebarToggleBtn?.addEventListener('click', function () {
            const isCollapsed = !document.body.classList.contains('sidebar-collapsed');
            localStorage.setItem('accredms_sidebar_collapsed', String(isCollapsed));
            applySidebarState(isCollapsed);
        });

        function applyTheme(theme) {
            html.setAttribute('data-theme', theme);
            if (themeIcon) {
                themeIcon.className = theme === 'dark' ? 'bi bi-sun' : 'bi bi-moon';
            }
        }

        // Load saved theme
        const savedTheme = localStorage.getItem('accredms_theme') || 'light';
        applyTheme(savedTheme);

        if (themeBtn) {
            themeBtn.addEventListener('click', function() {
                const current = html.getAttribute('data-theme');
                const next = current === 'dark' ? 'light' : 'dark';
                localStorage.setItem('accredms_theme', next);
                applyTheme(next);
            });
        }

        const notificationBadge = document.getElementById('notificationBadge');
        const notificationItems = document.getElementById('notificationItems');
        const notificationUrl = @json(route('notifications.unread'));
        const notificationReadUrlTemplate = @json(route('notifications.read', ['notification' => '__NOTIFICATION_ID__']));
        const areaUrlTemplate = @json(route('accreditor.show_area', ['area' => '__AREA_ID__']));

        function renderNotifications(payload) {
            notificationBadge.textContent = payload.count;
            notificationBadge.classList.toggle('d-none', payload.count === 0);

            if (payload.notifications.length === 0) {
                notificationItems.innerHTML = '<div class="px-3 py-3 text-muted fs-7">No unread notifications.</div>';
                return;
            }

            notificationItems.replaceChildren();
            payload.notifications.forEach((notification) => {
                const item = document.createElement('a');
                item.className = 'dropdown-item text-wrap py-2 notification-item';
                item.dataset.notificationId = notification.id;
                item.href = notification.area_id ? areaUrlTemplate.replace('__AREA_ID__', notification.area_id) : @json(route('dashboard'));
                const title = document.createElement('span');
                title.className = 'fw-semibold d-block fs-7';
                title.textContent = notification.title;
                const message = document.createElement('small');
                message.className = 'text-muted';
                message.textContent = notification.message;
                item.append(title, message);
                notificationItems.append(item);
            });
        }

        function refreshNotifications() {
            fetch(notificationUrl, { headers: { Accept: 'application/json' }, credentials: 'same-origin' })
                .then((response) => response.ok ? response.json() : null)
                .then((payload) => { if (payload) renderNotifications(payload); })
                .catch(() => {});
        }

        document.addEventListener('click', function (event) {
            const item = event.target.closest('.notification-item');
            if (!item) return;

            event.preventDefault();
            const destination = item.href;
            const readUrl = notificationReadUrlTemplate.replace('__NOTIFICATION_ID__', item.dataset.notificationId);

            fetch(readUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    Accept: 'application/json',
                },
                credentials: 'same-origin',
            }).finally(() => {
                window.location.assign(destination);
            });
        });

        window.setInterval(refreshNotifications, 30000);
    </script>
    @yield('scripts')
</body>
</html>
