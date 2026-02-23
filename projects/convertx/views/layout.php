<!DOCTYPE html>
<?php
// ── Cache busting ──────────────────────────────────────────────────────────
$uiVersion = '20260223220000';
header("Cache-Control: no-cache, no-store, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// ── Current view (set by the controller via extract()) ─────────────────────
$currentView = $view ?? 'dashboard';

// ── Read default theme from DB (same pattern as QR project) ───────────────
$defaultTheme = 'dark';
$allowedThemes = ['dark', 'light'];
try {
    $db = \Core\Database::getInstance();
    $navbarSettings = $db->fetch("SELECT default_theme FROM navbar_settings WHERE id = 1");
    if ($navbarSettings && !empty($navbarSettings['default_theme'])
        && in_array($navbarSettings['default_theme'], $allowedThemes, true)) {
        $defaultTheme = $navbarSettings['default_theme'];
    }
} catch (\Exception $e) {
    // fall through to dark default
}
?>
<html lang="en" data-theme="<?= htmlspecialchars($defaultTheme) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta name="csrf-token" content="<?= \Core\Security::generateCsrfToken() ?>">
    <title><?= htmlspecialchars($title ?? 'ConvertX') ?> – ConvertX</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
          integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw=="
          crossorigin="anonymous" referrerpolicy="no-referrer">

    <!-- Google Fonts – Poppins (matches platform standard) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Universal theme (loaded first so we can override selectively below) -->
    <link rel="stylesheet" href="/css/universal-theme.css?v=<?= $uiVersion ?>">

    <style>
        /* ═══════════════════════════════════════════════════════════════════
           ConvertX Theme Override
           Maps the project's indigo/violet identity onto the platform's CSS
           variable system so universal-theme.css animations and glows all
           use the right colours automatically.
        ═══════════════════════════════════════════════════════════════════ */
        :root {
            /* ConvertX brand identity */
            --cx-primary:    #6366f1;   /* indigo */
            --cx-secondary:  #8b5cf6;   /* violet */
            --cx-accent:     #06b6d4;   /* cyan-teal */
            --cx-success:    #10b981;
            --cx-warning:    #f59e0b;
            --cx-danger:     #ef4444;

            /* Map brand to platform variables so body::before gradient
               and universal-theme glows fire with ConvertX colours */
            --purple:  #6366f1;
            --cyan:    #8b5cf6;
            --magenta: #06b6d4;

            /* Explicit dark-mode structural variables (makes layout
               self-contained; mirrors universal-theme.css dark defaults) */
            --bg-primary:    #06060a;
            --bg-secondary:  #0c0c12;
            --bg-card:       #0f0f18;
            --text-primary:  #e8eefc;
            --text-secondary: #8892a6;
            --border-color:  rgba(255,255,255,0.1);

            /* Code block backgrounds */
            --cx-code-bg: rgba(0,0,0,0.45);

            /* Layout */
            --sidebar-width: 15rem;
            --navbar-height: 3.75rem;

            /* Spacing */
            --space-xs:  0.25rem;
            --space-sm:  0.375rem;
            --space-md:  0.75rem;
            --space-lg:  1rem;
            --space-xl:  1.5rem;
            --space-2xl: 2rem;

            /* Font scale */
            --font-xs:  0.75rem;
            --font-sm:  0.875rem;
            --font-md:  1rem;
            --font-lg:  1.125rem;
            --font-xl:  1.25rem;

            /* --text-muted alias for backward compatibility with view partials */
            --text-muted: #8892a6;
        }

        [data-theme="light"] {
            /* ConvertX brand – slightly deeper for readability on white */
            --cx-primary:    #4f46e5;
            --cx-secondary:  #7c3aed;
            --cx-accent:     #0891b2;
            --purple:  #4f46e5;
            --cyan:    #7c3aed;
            --magenta: #0891b2;

            /* Structural variables for light mode */
            --bg-primary:    #f8f9fa;
            --bg-secondary:  #ffffff;
            --bg-card:       #ffffff;
            --text-primary:  #1a1a1a;
            --text-secondary: #666666;
            --border-color:  rgba(0,0,0,0.1);
            --text-muted:    #666666;

            /* Code block background for light mode */
            --cx-code-bg: rgba(0,0,0,0.05);
        }

        /* Body font overrides universal-theme.css default Poppins to stay consistent */
        body {
            font-family: 'Poppins', sans-serif;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            will-change: scroll-position;
        }

        /* ── Body ambient glow – ConvertX indigo flavour ── */
        body::before {
            background:
                radial-gradient(ellipse at 15% 0%,   rgba(99,102,241,0.12) 0%, transparent 50%),
                radial-gradient(ellipse at 85% 100%,  rgba(139,92,246,0.10) 0%, transparent 50%);
        }

        [data-theme="light"] body::before {
            background:
                radial-gradient(ellipse at 15% 0%,   rgba(99,102,241,0.06) 0%, transparent 50%),
                radial-gradient(ellipse at 85% 100%,  rgba(139,92,246,0.04) 0%, transparent 50%);
        }

        /* ── Keyframe animations ── */
        @keyframes cx-fade-up {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        @keyframes cx-slide-down {
            from { opacity: 0; transform: translateY(-12px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        @keyframes cx-pulse-glow {
            0%, 100% { box-shadow: 0 0 0 0 rgba(99,102,241,0); }
            50%       { box-shadow: 0 0 16px 4px rgba(99,102,241,0.3); }
        }

        @keyframes cx-spin {
            to { transform: rotate(360deg); }
        }

        /* ── Page wrapper ── */
        .cx-dashboard {
            display: flex;
            min-height: calc(100vh - var(--navbar-height));
            margin-top: var(--navbar-height);
        }

        /* ── Sidebar ── */
        .cx-sidebar {
            width: var(--sidebar-width);
            background: var(--bg-card);
            border-right: 1px solid var(--border-color);
            padding: var(--space-lg) 0;
            position: fixed;
            left: 0;
            top: var(--navbar-height);
            bottom: 0;
            overflow-y: auto;
            overflow-x: hidden;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 100;
            will-change: transform;
            contain: layout style paint;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
            scrollbar-color: var(--border-color) transparent;
        }

        .cx-sidebar::-webkit-scrollbar { width: 0.375rem; }
        .cx-sidebar::-webkit-scrollbar-track { background: transparent; }
        .cx-sidebar::-webkit-scrollbar-thumb {
            background: var(--border-color);
            border-radius: 3px;
        }

        /* Sidebar logo strip */
        .cx-sidebar-logo {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            padding: 0 var(--space-lg) var(--space-lg);
            margin-bottom: var(--space-sm);
            border-bottom: 1px solid var(--border-color);
        }

        .cx-sidebar-logo .logo-icon {
            width: 2rem;
            height: 2rem;
            border-radius: 0.5rem;
            background: linear-gradient(135deg, var(--cx-primary), var(--cx-secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.875rem;
            color: #fff;
            flex-shrink: 0;
        }

        .cx-sidebar-logo .logo-text {
            font-weight: 700;
            font-size: 1rem;
            color: var(--text-primary);
        }

        .cx-sidebar-logo .logo-text span {
            background: linear-gradient(135deg, var(--cx-primary), var(--cx-accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Sidebar sections */
        .sidebar-section {
            padding: var(--space-sm) var(--space-lg);
            margin-bottom: var(--space-md);
        }

        .sidebar-title {
            font-size: 0.6875rem;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.0625rem;
            margin-bottom: var(--space-sm);
            padding: 0 var(--space-sm);
        }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: var(--space-md);
            padding: 0.625rem var(--space-md);
            color: var(--text-secondary);
            text-decoration: none;
            border-radius: 0.5rem;
            margin-bottom: var(--space-xs);
            transition: background 0.2s, color 0.2s, transform 0.2s;
            font-size: var(--font-sm);
            font-weight: 500;
            position: relative;
        }

        .sidebar-nav a:hover {
            background: var(--bg-secondary);
            color: var(--text-primary);
            transform: translateX(2px);
        }

        .sidebar-nav a.active {
            background: linear-gradient(135deg, var(--cx-primary), var(--cx-secondary));
            color: #fff;
            box-shadow: 0 4px 12px rgba(99,102,241,0.35);
        }

        .sidebar-nav a i {
            width: 1.125rem;
            text-align: center;
            flex-shrink: 0;
        }

        /* ── Main content area ── */
        .cx-main {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: var(--space-xl);
            min-height: calc(100vh - var(--navbar-height));
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow-x: auto;
            max-width: calc(100vw - var(--sidebar-width));
        }

        /* Entry animation for all direct children of cx-main */
        .cx-main > * {
            animation: cx-fade-up 0.4s ease both;
        }

        .cx-main > *:nth-child(1) { animation-delay: 0.04s; }
        .cx-main > *:nth-child(2) { animation-delay: 0.10s; }
        .cx-main > *:nth-child(3) { animation-delay: 0.16s; }
        .cx-main > *:nth-child(4) { animation-delay: 0.22s; }
        .cx-main > *:nth-child(5) { animation-delay: 0.28s; }

        /* ── Floating mobile toggle ── */
        .sidebar-toggle {
            position: fixed;
            bottom: var(--space-xl);
            right: var(--space-xl);
            width: 3.5rem;
            height: 3.5rem;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--cx-primary), var(--cx-secondary));
            border: none;
            color: #fff;
            cursor: pointer;
            display: none;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0.25rem 0.75rem rgba(99,102,241,0.45);
            z-index: 101;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            will-change: transform;
            font-size: 1.25rem;
        }

        .sidebar-toggle:hover  { box-shadow: 0 0.5rem 1.5rem rgba(99,102,241,0.6); }
        .sidebar-toggle:active { transform: scale(0.95); }

        /* ── Sidebar overlay ── */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: var(--navbar-height);
            left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.6);
            z-index: 99;
            backdrop-filter: blur(2px);
            -webkit-backdrop-filter: blur(2px);
        }

        .sidebar-overlay.active { display: block; animation: cx-fade-up 0.2s ease; }

        /* ── Cards ── */
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 0.75rem;
            padding: var(--space-xl);
            margin-bottom: var(--space-xl);
            transition: border-color 0.3s ease, box-shadow 0.3s ease, transform 0.3s ease;
            will-change: transform, box-shadow;
        }

        .card:hover {
            border-color: rgba(99,102,241,0.4);
            box-shadow: 0 8px 24px rgba(99,102,241,0.12);
            transform: translateY(-2px);
        }

        .glass-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 0.75rem;
            padding: var(--space-xl);
            margin-bottom: var(--space-xl);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            transition: border-color 0.3s ease, box-shadow 0.3s ease, transform 0.3s ease;
        }

        .glass-card:hover {
            border-color: rgba(99,102,241,0.4);
            box-shadow: 0 8px 24px rgba(99,102,241,0.12);
            transform: translateY(-2px);
        }

        .card-header {
            font-weight: 600;
            margin-bottom: var(--space-lg);
            display: flex;
            align-items: center;
            gap: var(--space-sm);
            font-size: var(--font-lg);
            padding-bottom: var(--space-md);
            border-bottom: 1px solid var(--border-color);
        }

        .card-header i { color: var(--cx-primary); }

        /* Section title (used in view partials) */
        .section-title {
            font-size: var(--font-xl);
            font-weight: 600;
            margin-bottom: var(--space-lg);
            display: flex;
            align-items: center;
            gap: var(--space-sm);
        }

        /* Page header */
        .page-header {
            margin-bottom: var(--space-2xl);
            text-align: center;
        }

        .page-header h1 {
            font-size: 2rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--cx-primary), var(--cx-accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1.2;
            margin-bottom: 0.5rem;
        }

        .page-header p {
            color: var(--text-secondary);
            font-size: 1rem;
            margin-top: 0.5rem;
        }

        /* ── Stats grid ── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: var(--space-lg);
            margin-bottom: var(--space-xl);
        }

        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 0.625rem;
            padding: var(--space-xl);
            text-align: center;
            transition: border-color 0.3s, box-shadow 0.3s, transform 0.3s;
            will-change: transform;
        }

        .stat-card:hover {
            border-color: rgba(99,102,241,0.4);
            box-shadow: 0 6px 20px rgba(99,102,241,0.15);
            transform: translateY(-3px);
        }

        .stat-card .stat-icon {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, var(--cx-primary), var(--cx-accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stat-card .value {
            font-size: 2rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--cx-primary), var(--cx-accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stat-card .label {
            font-size: var(--font-xs);
            color: var(--text-secondary);
            margin-top: var(--space-xs);
        }

        /* ── Buttons ── */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.375rem;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 0.5rem;
            font-family: inherit;
            font-size: var(--font-xs);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            white-space: nowrap;
            will-change: transform;
        }

        @media (min-width: 48rem) {
            .btn {
                padding: 0.625rem 1.25rem;
                font-size: var(--font-sm);
                border-radius: 0.625rem;
            }
        }

        .btn:disabled { opacity: 0.6; cursor: not-allowed; }
        .btn:active   { transform: translateY(0) !important; }

        .btn-primary {
            background: linear-gradient(135deg, var(--cx-primary), var(--cx-secondary));
            color: #fff;
        }

        .btn-primary:hover:not(:disabled) {
            box-shadow: 0 6px 20px rgba(99,102,241,0.55);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: var(--bg-secondary);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
        }

        .btn-secondary:hover:not(:disabled) {
            border-color: var(--cx-primary);
            box-shadow: 0 0 14px rgba(99,102,241,0.25);
            transform: translateY(-2px);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--cx-success), #059669);
            color: #fff;
        }

        .btn-success:hover:not(:disabled) {
            box-shadow: 0 6px 20px rgba(16,185,129,0.45);
            transform: translateY(-2px);
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--cx-danger), #dc2626);
            color: #fff;
        }

        .btn-danger:hover:not(:disabled) {
            box-shadow: 0 6px 20px rgba(239,68,68,0.45);
            transform: translateY(-2px);
        }

        /* ── Forms ── */
        .form-group { margin-bottom: var(--space-xl); }

        .form-group label,
        .form-label {
            display: block;
            font-size: var(--font-sm);
            font-weight: 500;
            color: var(--text-secondary);
            margin-bottom: var(--space-sm);
        }

        .form-control, .form-input, .form-select {
            width: 100%;
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            color: var(--text-primary);
            font-family: inherit;
            font-size: var(--font-sm);
            outline: none;
            transition: border-color 0.3s, box-shadow 0.3s;
            line-height: 1.5;
        }

        .form-control:focus,
        .form-input:focus,
        .form-select:focus {
            border-color: var(--cx-primary);
            box-shadow: 0 0 0 3px rgba(99,102,241,0.15);
        }

        .form-actions {
            display: flex;
            gap: 0.75rem;
            align-items: center;
            flex-wrap: wrap;
        }

        /* ── Upload zone ── */
        .upload-zone {
            border: 2px dashed var(--border-color);
            border-radius: 0.75rem;
            padding: 3rem 2rem;
            text-align: center;
            cursor: pointer;
            transition: border-color 0.25s, background 0.25s, transform 0.25s;
        }

        .upload-zone:hover,
        .upload-zone.drag-over {
            border-color: var(--cx-primary);
            background: rgba(99,102,241,0.06);
            transform: scale(1.01);
        }

        .upload-zone i {
            font-size: 2.5rem;
            margin-bottom: var(--space-md);
            display: block;
            background: linear-gradient(135deg, var(--cx-primary), var(--cx-accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .upload-zone p { color: var(--text-secondary); font-size: var(--font-sm); }

        /* ── Badges ── */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.2rem 0.65rem;
            border-radius: 20px;
            font-size: var(--font-xs);
            font-weight: 600;
        }

        .badge-pending    { background: rgba(245,158,11,.15);  color: var(--cx-warning); }
        .badge-processing { background: rgba(99,102,241,.15);  color: var(--cx-primary); }
        .badge-completed  { background: rgba(16,185,129,.15);  color: var(--cx-success); }
        .badge-failed     { background: rgba(239,68,68,.15);   color: var(--cx-danger);  }
        .badge-cancelled  { background: rgba(136,146,166,.15); color: var(--text-secondary); }

        /* ── Table ── */
        .cx-table { width: 100%; border-collapse: collapse; }

        .cx-table th,
        .cx-table td {
            padding: 0.75rem 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
            font-size: var(--font-sm);
        }

        .cx-table th {
            color: var(--text-secondary);
            font-weight: 500;
            font-size: var(--font-xs);
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .cx-table tbody tr {
            transition: background 0.15s;
        }

        .cx-table tbody tr:hover td {
            background: rgba(99,102,241,0.04);
        }

        .cx-table tr:last-child td { border-bottom: none; }

        /* ── Alerts ── */
        .alert {
            padding: 0.9375rem 1.25rem;
            border-radius: 0.5rem;
            margin-bottom: var(--space-lg);
            font-size: var(--font-sm);
            animation: cx-slide-down 0.3s ease both;
        }

        .alert-success { background: rgba(16,185,129,.12); border: 1px solid var(--cx-success); color: var(--cx-success); }
        .alert-error   { background: rgba(239,68,68,.12);  border: 1px solid var(--cx-danger);  color: var(--cx-danger); }

        /* ── Grid helpers ── */
        .grid   { display: grid; gap: var(--space-lg); }
        .grid-2 { grid-template-columns: repeat(2, 1fr); }
        .grid-3 { grid-template-columns: repeat(3, 1fr); }
        .grid-4 { grid-template-columns: repeat(4, 1fr); }

        /* ── AI badge (reusable) ── */
        .ai-badge {
            background: linear-gradient(135deg, var(--cx-primary), var(--cx-secondary));
            color: #fff;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        /* ── AI capability tile (dashboard) – theme-aware ── */
        .cx-ai-tile {
            display: block;
            text-decoration: none;
            padding: 1rem;
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 0.625rem;
            text-align: center;
            transition: border-color 0.3s, background 0.3s, transform 0.2s;
            cursor: pointer;
        }

        .cx-ai-tile:hover {
            border-color: var(--cx-primary);
            background: rgba(99,102,241,0.1);
            transform: translateY(-2px);
        }

        /* ── Clear-preset button (convert page) – theme-aware ── */
        .cx-clear-btn {
            margin-left: auto;
            padding: 0.4rem 0.875rem;
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 0.4rem;
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 500;
            color: var(--text-primary);
            transition: background 0.2s, color 0.2s;
            flex-shrink: 0;
            display: inline-block;
        }

        .cx-clear-btn:hover {
            background: var(--bg-card);
            color: var(--text-primary);
        }

        /* ── Responsive ── */
        @media (max-width: 64rem) {
            .grid-3, .grid-4 { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 48rem) {
            .cx-sidebar { transform: translateX(-100%); }
            .cx-sidebar.open { transform: translateX(0); }

            .cx-main {
                margin-left: 0;
                max-width: 100vw;
                padding: var(--space-lg) 0.9375rem;
                overflow-x: auto !important;
            }

            .sidebar-toggle { display: flex; }

            .grid-2, .grid-3, .grid-4 { grid-template-columns: 1fr; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .card { padding: var(--space-lg); }
            .page-header h1 { font-size: 1.5rem; }
        }

        @media (max-width: 30rem) {
            .cx-main { padding: 0.9375rem 0.625rem; }

            .btn:not(.btn-sm) {
                width: 100%;
                justify-content: center;
                padding: 0.75rem 1rem;
            }

            .stats-grid { grid-template-columns: 1fr; }
            .form-actions { flex-direction: column; width: 100%; }
            .form-actions .btn { width: 100%; }
        }
    </style>
</head>
<body>
<?php
// Initialise user timezone
\Core\Timezone::init(\Core\Auth::id());

// Include the shared platform navbar
include BASE_PATH . '/views/layouts/navbar.php';
?>

<div class="cx-dashboard">
    <!-- ── Sidebar ── -->
    <aside class="cx-sidebar" id="cxSidebar">

        <!-- Logo strip -->
        <div class="cx-sidebar-logo">
            <div class="logo-icon"><i class="fa-solid fa-shuffle"></i></div>
            <div class="logo-text">Convert<span>X</span></div>
        </div>

        <div class="sidebar-section">
            <div class="sidebar-title">Convert</div>
            <nav class="sidebar-nav">
                <a href="/projects/convertx/dashboard"
                   class="<?= ($currentView === 'dashboard') ? 'active' : '' ?>">
                    <i class="fa-solid fa-gauge-high"></i> Dashboard
                </a>
                <a href="/projects/convertx/convert"
                   class="<?= ($currentView === 'convert') ? 'active' : '' ?>">
                    <i class="fa-solid fa-arrow-right-arrow-left"></i> Convert File
                </a>
                <a href="/projects/convertx/batch"
                   class="<?= ($currentView === 'batch') ? 'active' : '' ?>">
                    <i class="fa-solid fa-layer-group"></i> Batch Convert
                </a>
                <a href="/projects/convertx/history"
                   class="<?= ($currentView === 'history') ? 'active' : '' ?>">
                    <i class="fa-solid fa-clock-rotate-left"></i> History
                </a>
            </nav>
        </div>

        <div class="sidebar-section">
            <div class="sidebar-title">Developers</div>
            <nav class="sidebar-nav">
                <a href="/projects/convertx/docs"
                   class="<?= ($currentView === 'docs') ? 'active' : '' ?>">
                    <i class="fa-solid fa-book-open"></i> API Docs
                </a>
                <a href="/projects/convertx/settings"
                   class="<?= ($currentView === 'settings') ? 'active' : '' ?>">
                    <i class="fa-solid fa-key"></i> API Keys
                </a>
            </nav>
        </div>

        <div class="sidebar-section">
            <div class="sidebar-title">Account</div>
            <nav class="sidebar-nav">
                <a href="/projects/convertx/plan"
                   class="<?= ($currentView === 'plan') ? 'active' : '' ?>">
                    <i class="fa-solid fa-star"></i> Plans &amp; Pricing
                </a>
            </nav>
        </div>

    </aside>

    <!-- Overlay for mobile -->
    <div class="sidebar-overlay" id="cxOverlay"></div>

    <!-- ── Main content ── -->
    <main class="cx-main" id="cxMain">
        <?php
        // Flash messages
        $flash = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
        if (!empty($flash['success'])): ?>
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($flash['success']) ?>
            </div>
        <?php endif;
        if (!empty($flash['error'])): ?>
            <div class="alert alert-error">
                <i class="fa-solid fa-circle-xmark"></i> <?= htmlspecialchars($flash['error']) ?>
            </div>
        <?php endif; ?>

        <?php
        // Render the current view partial
        $viewFile = PROJECT_PATH . '/views/' . $currentView . '.php';
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            echo '<p style="color:var(--cx-danger)">View not found: ' . htmlspecialchars($currentView) . '</p>';
        }
        ?>
    </main>

    <!-- Floating mobile sidebar toggle -->
    <button class="sidebar-toggle" id="cxToggle" aria-label="Toggle navigation">
        <i class="fa-solid fa-bars"></i>
    </button>
</div>

<script>
(function () {
    var sidebar = document.getElementById('cxSidebar');
    var overlay = document.getElementById('cxOverlay');
    var toggle  = document.getElementById('cxToggle');

    function openSidebar()  {
        sidebar.classList.add('open');
        overlay.classList.add('active');
        toggle.innerHTML = '<i class="fa-solid fa-xmark"></i>';
    }

    function closeSidebar() {
        sidebar.classList.remove('open');
        overlay.classList.remove('active');
        toggle.innerHTML = '<i class="fa-solid fa-bars"></i>';
    }

    if (toggle)  toggle.addEventListener('click', function () {
        sidebar.classList.contains('open') ? closeSidebar() : openSidebar();
    });
    if (overlay) overlay.addEventListener('click', closeSidebar);

    // Close on nav link tap (mobile)
    sidebar.querySelectorAll('.sidebar-nav a').forEach(function (a) {
        a.addEventListener('click', function () { if (window.innerWidth <= 768) closeSidebar(); });
    });

    // Tidy up on resize
    var resizeTimer;
    window.addEventListener('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function () { if (window.innerWidth > 768) closeSidebar(); }, 250);
    });
})();
</script>
</body>
</html>
