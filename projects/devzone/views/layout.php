<?php
/**
 * DevZone – Layout (ConvertX-style redesign)
 */

// ── Cache busting ─────────────────────────────────────────────────────────
$uiVersion = '20260418000000';
header("Cache-Control: no-cache, no-store, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// ── Current view ─────────────────────────────────────────────────────────
$currentView = $currentView ?? ($view ?? 'dashboard');

// ── Read default theme from DB ────────────────────────────────────────────
$defaultTheme  = 'dark';
$allowedThemes = ['dark', 'light'];
try {
    $db = \Core\Database::getInstance();
    $ns = $db->fetch("SELECT default_theme FROM navbar_settings WHERE id = 1");
    if ($ns && !empty($ns['default_theme']) && in_array($ns['default_theme'], $allowedThemes, true)) {
        $defaultTheme = $ns['default_theme'];
    }
} catch (\Exception $e) { /* fallthrough */ }

$csrfToken = \Core\Security::generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?= htmlspecialchars($defaultTheme) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta name="csrf-token" content="<?= $csrfToken ?>">
    <title><?= htmlspecialchars($title ?? 'DevZone') ?> – DevZone</title>

    <!-- Apply saved theme before paint to prevent flash -->
    <script>
    (function(){
        var s=localStorage.getItem('theme');
        if(s) document.documentElement.setAttribute('data-theme',s);
    })();
    </script>

    <!-- Google Fonts – Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
          integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw=="
          crossorigin="anonymous" referrerpolicy="no-referrer">

    <!-- Universal theme -->
    <link rel="stylesheet" href="/css/universal-theme.css?v=<?= $uiVersion ?>">

    <style>
        /* ═══════════════════════════════════════════════════════════════
           DevZone – CSS Variables & Theme (ConvertX-style structure)
        ═══════════════════════════════════════════════════════════════ */
        :root {
            /* DevZone brand */
            --dz-primary:   #ff2ec4;
            --dz-secondary: #00f0ff;
            --dz-success:   #00ff88;
            --dz-warning:   #ffaa00;
            --dz-danger:    #ff6b6b;
            --dz-purple:    #9945ff;

            /* Map DevZone brand onto cx- vars so shared CSS works */
            --cx-primary:   #ff2ec4;
            --cx-secondary: #00f0ff;
            --cx-accent:    #00ff88;
            --cx-success:   #00ff88;
            --cx-warning:   #ffaa00;
            --cx-danger:    #ff6b6b;

            /* Platform brand variables (for universal-theme) */
            --purple:  #9945ff;
            --cyan:    #00f0ff;
            --magenta: #ff2ec4;
            --green:   #00ff88;
            --orange:  #ffaa00;

            /* ── Dark structural vars ── */
            --bg-primary:    #06060a;
            --bg-secondary:  #0c0c12;
            --bg-card:       #0f0f18;
            --bg-tertiary:   #13131f;
            --text-primary:  #e8eefc;
            --text-secondary: #8892a6;
            --text-muted:    #8892a6;
            --border-color:  rgba(255,255,255,0.08);
            --border-hover:  rgba(255,46,196,0.45);

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
            --font-2xl: 1.5rem;
        }

        [data-theme="light"] {
            --dz-primary:   #c4006e;
            --dz-secondary: #0369a1;
            --cx-primary:   #c4006e;
            --cx-secondary: #0369a1;
            --cx-accent:    #16a34a;
            --cx-success:   #16a34a;
            --cx-warning:   #b45309;
            --cx-danger:    #dc2626;
            --dz-success:   #16a34a;
            --dz-warning:   #b45309;
            --dz-danger:    #dc2626;
            --border-hover: rgba(196,0,110,0.45);

            --purple:  #7c3aed;
            --cyan:    #0369a1;
            --magenta: #c4006e;
            --green:   #16a34a;
            --orange:  #b45309;

            --bg-primary:    #f4f5fa;
            --bg-secondary:  #ffffff;
            --bg-card:       #ffffff;
            --bg-tertiary:   #eef0f8;
            --text-primary:  #0f0f1a;
            --text-secondary: #4b5563;
            --text-muted:    #6b7280;
            --border-color:  rgba(0,0,0,0.08);
        }

        /* ── Reset ── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html {
            scroll-behavior: smooth;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            max-width: 100vw;
            background: var(--bg-primary);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            font-size: var(--font-md);
            line-height: 1.6;
            overflow-x: hidden;
            -webkit-overflow-scrolling: touch;
            max-width: 100vw;
        }

        /* Ambient glow */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background:
                radial-gradient(ellipse at 15% 0%,  rgba(255,46,196,0.08) 0%, transparent 50%),
                radial-gradient(ellipse at 85% 100%, rgba(0,240,255,0.06) 0%, transparent 50%);
            pointer-events: none;
            z-index: -2;
        }
        [data-theme="light"] body::before {
            background:
                radial-gradient(ellipse at 15% 0%,  rgba(255,46,196,0.04) 0%, transparent 50%),
                radial-gradient(ellipse at 85% 100%, rgba(0,240,255,0.03) 0%, transparent 50%);
        }

        /* Grid overlay */
        body::after {
            content: '';
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,46,196,0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,46,196,0.03) 1px, transparent 1px);
            background-size: 52px 52px;
            pointer-events: none;
            z-index: -1;
        }
        [data-theme="light"] body::after {
            background-image:
                linear-gradient(rgba(255,46,196,0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,46,196,0.05) 1px, transparent 1px);
        }

        /* ── Keyframes ── */
        @keyframes dz-fade-up {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes dz-slide-down {
            from { opacity: 0; transform: translateY(-10px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes dz-pulse-glow {
            0%, 100% { box-shadow: 0 0 0 0 rgba(255,46,196,0); }
            50%       { box-shadow: 0 0 18px 5px rgba(255,46,196,0.28); }
        }
        @keyframes dz-shimmer {
            0%   { background-position: -200% center; }
            100% { background-position:  200% center; }
        }
        @keyframes dz-neon-pulse {
            0%, 100% { box-shadow: 0 0 8px rgba(255,46,196,0.35), 0 0 24px rgba(255,46,196,0.10); }
            50%       { box-shadow: 0 0 16px rgba(255,46,196,0.60), 0 0 40px rgba(255,46,196,0.20); }
        }
        @keyframes dz-count-up {
            from { opacity: 0; transform: translateY(8px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes dz-spin { to { transform: rotate(360deg); } }

        /* ── Page wrapper ── */
        .dz-dashboard {
            display: flex;
            min-height: calc(100vh - var(--navbar-height));
            background: var(--bg-primary);
        }

        /* ── Sidebar ── */
        .dz-sidebar {
            width: var(--sidebar-width);
            background: var(--bg-card);
            border-right: 1px solid var(--border-color);
            padding: 0.875rem 0 2rem;
            position: fixed;
            left: 0;
            top: var(--navbar-height);
            bottom: 0;
            overflow-y: auto;
            overflow-x: hidden;
            transition: transform 0.3s cubic-bezier(0.4,0,0.2,1), background 0.3s;
            z-index: 100;
            will-change: transform;
            contain: layout style paint;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
            scrollbar-color: var(--border-color) transparent;
        }
        .dz-sidebar::-webkit-scrollbar { width: 4px; }
        .dz-sidebar::-webkit-scrollbar-track { background: transparent; }
        .dz-sidebar::-webkit-scrollbar-thumb { background: var(--border-color); border-radius: 4px; }

        /* Sidebar logo */
        .dz-sidebar-logo {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            padding: 0.25rem 1rem 0.875rem;
            margin-bottom: 0.25rem;
            border-bottom: 1px solid var(--border-color);
            text-decoration: none;
        }
        .dz-sidebar-logo .logo-icon {
            width: 1.875rem;
            height: 1.875rem;
            border-radius: 0.5rem;
            background: linear-gradient(135deg, var(--dz-primary), var(--dz-secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            color: #fff;
            flex-shrink: 0;
            box-shadow: 0 2px 8px rgba(255,46,196,0.45);
            animation: dz-neon-pulse 3s ease infinite;
        }
        .dz-sidebar-logo .logo-text {
            font-weight: 700;
            font-size: 1rem;
            color: var(--text-primary);
        }
        .dz-sidebar-logo .logo-text span {
            background: linear-gradient(135deg, var(--dz-primary), var(--dz-secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Sidebar sections */
        .sidebar-section { padding: 0.5rem 0.75rem; margin-top: 0.375rem; }
        .sidebar-title {
            font-size: 0.6875rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.07rem;
            margin-bottom: 0.375rem;
            padding: 0 0.375rem;
        }
        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            padding: 0.5625rem 0.625rem;
            color: var(--text-secondary);
            text-decoration: none;
            border-radius: 0.5rem;
            margin-bottom: 0.125rem;
            transition: background 0.2s, color 0.2s, transform 0.2s;
            font-size: var(--font-sm);
            font-weight: 500;
        }
        .sidebar-nav a:hover {
            background: var(--bg-secondary);
            color: var(--text-primary);
            transform: translateX(2px);
        }
        .sidebar-nav a.active {
            background: linear-gradient(135deg, var(--dz-primary), var(--dz-secondary));
            color: #fff;
            box-shadow: 0 3px 10px rgba(255,46,196,0.35);
        }
        .sidebar-nav a.active::after {
            content: '';
            position: absolute;
            right: 0; top: 15%; bottom: 15%;
            width: 3px;
            border-radius: 3px;
            background: var(--dz-secondary);
            box-shadow: 0 0 8px var(--dz-secondary);
        }
        .sidebar-nav a { position: relative; }
        .sidebar-nav a i { width: 1rem; text-align: center; flex-shrink: 0; font-size: 0.875rem; }
        .sidebar-nav a .badge-count {
            margin-left: auto;
            background: rgba(255,46,196,0.15);
            color: var(--dz-primary);
            border-radius: 1rem;
            padding: 0.0625rem 0.5rem;
            font-size: 0.7rem;
            font-weight: 700;
        }

        /* ── Main content ── */
        .dz-main {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: var(--space-xl);
            min-height: calc(100vh - var(--navbar-height));
            background: var(--bg-primary);
            transition: margin-left 0.3s cubic-bezier(0.4,0,0.2,1);
            overflow-x: auto;
            max-width: calc(100vw - var(--sidebar-width));
        }
        .dz-main > * { animation: dz-fade-up 0.4s ease both; }
        .dz-main > *:nth-child(1) { animation-delay: 0.04s; }
        .dz-main > *:nth-child(2) { animation-delay: 0.10s; }
        .dz-main > *:nth-child(3) { animation-delay: 0.16s; }
        .dz-main > *:nth-child(4) { animation-delay: 0.22s; }
        .dz-main > *:nth-child(5) { animation-delay: 0.28s; }

        /* ── Floating mobile toggle ── */
        .sidebar-toggle {
            position: fixed;
            bottom: var(--space-xl);
            right: var(--space-xl);
            width: 3.25rem;
            height: 3.25rem;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--dz-primary), var(--dz-secondary));
            border: none;
            color: #fff;
            cursor: pointer;
            display: none;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 16px rgba(255,46,196,0.5);
            z-index: 101;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            font-size: 1.1rem;
        }
        .sidebar-toggle:hover  { box-shadow: 0 6px 22px rgba(255,46,196,0.65); }
        .sidebar-toggle:active { transform: scale(0.93); }

        /* ── Sidebar overlay ── */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: var(--navbar-height);
            left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.65);
            z-index: 99;
            backdrop-filter: blur(3px);
            -webkit-backdrop-filter: blur(3px);
        }
        .sidebar-overlay.active { display: block; animation: dz-fade-up 0.2s ease; }

        /* ── Cards ── */
        .card, .glass-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 0.75rem;
            padding: var(--space-xl);
            margin-bottom: var(--space-xl);
            transition: border-color 0.3s, box-shadow 0.3s, transform 0.3s;
        }
        .card:hover, .glass-card:hover {
            border-color: var(--border-hover);
            box-shadow: 0 8px 32px rgba(255,46,196,0.10), 0 0 0 1px rgba(255,46,196,0.08);
            transform: translateY(-2px);
        }
        .card-header {
            font-weight: 600;
            margin-bottom: var(--space-lg);
            display: flex;
            align-items: center;
            gap: var(--space-sm);
            font-size: var(--font-md);
            padding-bottom: var(--space-md);
            padding-left: calc(var(--space-md) + 3px);
            border-bottom: 1px solid var(--border-color);
            color: var(--text-primary);
            position: relative;
        }
        .card-header::before {
            content: '';
            position: absolute;
            left: 0; top: 20%; bottom: 20%;
            width: 3px;
            border-radius: 3px;
            background: linear-gradient(180deg, var(--dz-primary), var(--dz-secondary));
        }
        .card-header i { color: var(--dz-primary); }

        /* ── Page header ── */
        .page-header { margin-bottom: var(--space-2xl); }
        .page-header h1 {
            font-size: clamp(1.5rem, 4vw, 2.25rem);
            font-weight: 700;
            background: linear-gradient(135deg, var(--dz-primary), var(--dz-secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1.2;
            margin-bottom: 0.5rem;
        }
        .page-header p {
            font-size: var(--font-sm);
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            gap: 0.375rem;
        }
        .page-header p::before {
            content: '';
            display: inline-block;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--dz-primary);
            box-shadow: 0 0 8px var(--dz-primary);
            animation: dz-pulse-glow 2s ease infinite;
            flex-shrink: 0;
        }

        /* ── Stats grid ── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: var(--space-lg);
            margin-bottom: var(--space-xl);
        }
        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 0.75rem;
            padding: var(--space-xl);
            text-align: center;
            transition: border-color 0.3s, box-shadow 0.3s, transform 0.3s;
            position: relative;
            overflow: hidden;
        }
        .stat-card::before {
            content: '';
            position: absolute;
            top: -30px; left: 50%;
            transform: translateX(-50%);
            width: 80px; height: 80px;
            background: radial-gradient(circle, rgba(255,46,196,0.12) 0%, transparent 70%);
            pointer-events: none;
        }
        .stat-card:hover {
            border-color: var(--border-hover);
            box-shadow: 0 6px 20px rgba(255,46,196,0.14), 0 0 0 1px rgba(255,46,196,0.10);
            transform: translateY(-3px);
        }
        .stat-card .stat-icon {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            display: block;
            background: linear-gradient(135deg, var(--dz-primary), var(--dz-secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .stat-card .value {
            font-size: 1.875rem;
            font-weight: 700;
            display: block;
            background: linear-gradient(135deg, var(--dz-primary), var(--dz-secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1.1;
            animation: dz-count-up 0.55s ease both;
            animation-delay: 0.2s;
        }
        .stat-card .label {
            font-size: var(--font-xs);
            color: var(--text-secondary);
            margin-top: 0.3rem;
            display: block;
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
            transition: all 0.25s ease;
            text-decoration: none;
            white-space: nowrap;
            position: relative;
            line-height: 1;
        }
        @media (min-width: 48rem) {
            .btn { padding: 0.625rem 1.25rem; font-size: var(--font-sm); border-radius: 0.625rem; }
        }
        .btn:disabled { opacity: 0.55; cursor: not-allowed; }
        .btn:active   { transform: translateY(0) !important; }

        .btn-primary {
            background: linear-gradient(135deg, var(--dz-primary), var(--dz-secondary));
            color: #fff;
        }
        .btn-primary:hover:not(:disabled) {
            box-shadow: 0 5px 18px rgba(255,46,196,0.45);
            transform: translateY(-2px);
            color: #fff;
        }
        .btn-primary::after {
            content: '';
            position: absolute;
            inset: -1px;
            border-radius: inherit;
            background: linear-gradient(135deg, var(--dz-primary), var(--dz-secondary));
            z-index: -1;
            opacity: 0;
            transition: opacity .25s;
            filter: blur(6px);
        }
        .btn-primary:hover:not(:disabled)::after { opacity: 0.55; }

        .btn-secondary {
            background: var(--bg-secondary);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
        }
        .btn-secondary:hover:not(:disabled) {
            border-color: var(--dz-primary);
            box-shadow: 0 0 14px rgba(255,46,196,0.2);
            transform: translateY(-2px);
        }
        .btn-success {
            background: linear-gradient(135deg, var(--dz-success), #059669);
            color: #fff;
        }
        .btn-success:hover:not(:disabled) {
            box-shadow: 0 5px 18px rgba(0,255,136,0.35);
            transform: translateY(-2px);
        }
        .btn-danger {
            background: linear-gradient(135deg, var(--dz-danger), #dc2626);
            color: #fff;
        }
        .btn-danger:hover:not(:disabled) {
            box-shadow: 0 5px 18px rgba(255,107,107,0.40);
            transform: translateY(-2px);
        }
        .btn-sm {
            padding: 0.375rem 0.75rem !important;
            font-size: var(--font-xs) !important;
            border-radius: 0.4rem !important;
        }
        .btn-block { width: 100%; justify-content: center; }

        /* ── Forms ── */
        .form-group { margin-bottom: var(--space-xl); }
        .form-group label, .form-label {
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
            padding: 0.6875rem 0.875rem;
            color: var(--text-primary);
            font-family: inherit;
            font-size: var(--font-sm);
            outline: none;
            transition: border-color 0.25s, box-shadow 0.25s, background 0.25s;
            line-height: 1.5;
        }
        .form-control:focus, .form-input:focus, .form-select:focus {
            border-color: var(--dz-primary);
            box-shadow: 0 0 0 3px rgba(255,46,196,0.15), 0 0 16px rgba(255,46,196,0.08);
        }
        .form-control::placeholder, .form-input::placeholder { color: var(--text-muted); }
        .form-actions { display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap; }

        /* ── Option row (checkbox label like ConvertX) ── */
        .dz-option {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            cursor: pointer;
            font-size: var(--font-sm);
            padding: 0.5rem 0.625rem;
            border-radius: 0.375rem;
            transition: background 0.15s;
            color: var(--text-primary);
            user-select: none;
        }
        .dz-option:hover { background: rgba(255,46,196,0.06); }
        .dz-option i { color: var(--dz-primary); width: 1rem; text-align: center; flex-shrink: 0; }
        .dz-option input[type="checkbox"] {
            accent-color: var(--dz-primary);
            width: 1rem; height: 1rem;
            flex-shrink: 0;
        }

        /* ── Badges ── */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.2rem 0.6rem;
            border-radius: 20px;
            font-size: var(--font-xs);
            font-weight: 600;
        }
        .badge-todo       { background: rgba(136,146,166,.15); color: var(--text-secondary); }
        .badge-in-progress{ background: rgba(255,46,196,.15);  color: var(--dz-primary); }
        .badge-done       { background: rgba(0,255,136,.15);   color: var(--dz-success); }
        .badge-blocked    { background: rgba(255,107,107,.15); color: var(--dz-danger); }

        .badge-low    { background: rgba(136,146,166,.15); color: var(--text-secondary); }
        .badge-medium { background: rgba(255,170,0,.15);   color: var(--dz-warning); }
        .badge-high   { background: rgba(255,107,107,.15); color: var(--dz-danger); }
        .badge-urgent { background: rgba(255,46,196,.15);  color: var(--dz-primary); }

        /* ── Table ── */
        .dz-table { width: 100%; border-collapse: collapse; }
        .dz-table th, .dz-table td {
            padding: 0.75rem 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
            font-size: var(--font-sm);
            color: var(--text-primary);
        }
        .dz-table th {
            color: var(--text-secondary);
            font-weight: 500;
            font-size: var(--font-xs);
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .dz-table tbody tr { transition: background 0.15s; }
        .dz-table tbody tr:hover td { background: rgba(255,46,196,0.04); }
        .dz-table tr:last-child td { border-bottom: none; }

        /* ── Alerts ── */
        .alert {
            padding: 0.875rem 1.125rem;
            border-radius: 0.5rem;
            margin-bottom: var(--space-lg);
            font-size: var(--font-sm);
            display: flex;
            align-items: center;
            gap: 0.625rem;
            animation: dz-slide-down 0.3s ease both;
        }
        .alert-success { background: rgba(0,255,136,.10);  border: 1px solid var(--dz-success); color: var(--dz-success); }
        .alert-error   { background: rgba(255,107,107,.10);border: 1px solid var(--dz-danger);  color: var(--dz-danger); }

        /* ── Quick action cards (like cx-quick-card) ── */
        .dz-quick-card {
            flex: 1;
            min-width: 160px;
            padding: 1.25rem;
            border-radius: 0.75rem;
            text-decoration: none;
            text-align: center;
            transition: transform 0.25s, box-shadow 0.25s;
            display: block;
            color: #fff;
        }
        .dz-quick-card:hover { transform: translateY(-4px); color: #fff; }
        .dz-quick-card .qc-icon { font-size: 1.75rem; margin-bottom: 0.5rem; display: block; color: #fff; }
        .dz-quick-card strong { display: block; font-size: 0.95rem; color: #fff; margin-bottom: 0.125rem; }
        .dz-quick-card p { font-size: 0.78rem; color: rgba(255,255,255,0.82); margin: 0; }

        /* Quick-action row */
        .dz-quick-row { display: flex; gap: 1rem; margin-bottom: 2rem; flex-wrap: wrap; }
        .dz-quick-row .dz-quick-card { flex: 1; min-width: 140px; }
        @media (max-width: 37.5rem) {
            .dz-quick-row { gap: .625rem; }
            .dz-quick-row .dz-quick-card { min-width: calc(50% - .3125rem); max-width: calc(50% - .3125rem); }
        }

        /* ── Feature tiles (like cx-ai-tile) ── */
        .dz-tile {
            display: block;
            text-decoration: none;
            padding: 1rem;
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 0.625rem;
            text-align: center;
            transition: border-color 0.25s, background 0.25s, transform 0.2s, box-shadow 0.25s;
            cursor: pointer;
            color: var(--text-primary);
        }
        .dz-tile:hover {
            border-color: var(--dz-primary);
            background: rgba(255,46,196,0.06);
            transform: translateY(-3px);
            box-shadow: 0 6px 18px rgba(255,46,196,0.12);
        }
        .dz-tile .tile-icon {
            width: 36px; height: 36px;
            margin: 0 auto 0.5rem;
            border-radius: 0.5rem;
            display: flex; align-items: center; justify-content: center;
        }
        .dz-tile .tile-icon i { color: #fff; font-size: 0.875rem; }
        .dz-tile .tile-title { font-size: var(--font-sm); font-weight: 600; margin-bottom: 0.2rem; color: var(--text-primary); }
        .dz-tile .tile-desc  { font-size: var(--font-xs); color: var(--text-secondary); }

        /* ── Kanban columns ── */
        .kanban-board {
            display: flex;
            gap: 1rem;
            overflow-x: auto;
            padding-bottom: 1rem;
            -webkit-overflow-scrolling: touch;
            min-height: 400px;
        }
        .kanban-col {
            flex: 0 0 280px;
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 0.75rem;
            padding: 0.875rem;
            display: flex;
            flex-direction: column;
            gap: 0.625rem;
            min-height: 300px;
        }
        .kanban-col-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.25rem;
        }
        .kanban-col-title {
            font-size: var(--font-sm);
            font-weight: 700;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 0.375rem;
        }
        .kanban-col-count {
            font-size: var(--font-xs);
            background: rgba(255,46,196,0.12);
            color: var(--dz-primary);
            border-radius: 1rem;
            padding: 0.1rem 0.5rem;
            font-weight: 700;
        }
        .task-card {
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            padding: 0.75rem;
            transition: border-color 0.2s, box-shadow 0.2s, transform 0.2s;
            cursor: pointer;
        }
        .task-card:hover {
            border-color: var(--border-hover);
            box-shadow: 0 4px 12px rgba(255,46,196,0.10);
            transform: translateY(-1px);
        }
        .task-card .task-title { font-size: var(--font-sm); font-weight: 600; color: var(--text-primary); margin-bottom: 0.375rem; }
        .task-card .task-meta  { display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center; }
        .task-card .task-due   { font-size: var(--font-xs); color: var(--text-muted); }
        .task-card .task-due.overdue { color: var(--dz-danger); }

        /* Board color swatch */
        .board-color-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }

        /* ── Grid helpers ── */
        .grid   { display: grid; gap: var(--space-lg); }
        .grid-2 { grid-template-columns: repeat(2,1fr); }
        .grid-3 { grid-template-columns: repeat(3,1fr); }
        .grid-4 { grid-template-columns: repeat(4,1fr); }

        /* ── Modal ── */
        .dz-modal-overlay {
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.65);
            z-index: 500;
            display: none;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
        }
        .dz-modal-overlay.open { display: flex; animation: dz-fade-up 0.2s ease; }
        .dz-modal {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            padding: 2rem;
            width: 100%;
            max-width: 520px;
            max-height: 90vh;
            overflow-y: auto;
            margin: 1rem;
            position: relative;
        }
        .dz-modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }
        .dz-modal-title { font-size: 1.1rem; font-weight: 700; color: var(--text-primary); }
        .dz-modal-close {
            width: 1.75rem; height: 1.75rem;
            border: none;
            background: var(--bg-secondary);
            color: var(--text-secondary);
            border-radius: 50%;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.8rem;
            transition: background 0.2s, color 0.2s;
        }
        .dz-modal-close:hover { background: rgba(255,107,107,0.15); color: var(--dz-danger); }

        /* ── Separator ── */
        .dz-separator {
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--dz-primary), var(--dz-secondary), transparent);
            margin: 1.25rem 0;
            opacity: 0.35;
        }

        /* ── Responsive breakpoints ── */
        @media (max-width: 64rem) {
            .grid-3, .grid-4 { grid-template-columns: repeat(2,1fr); }
        }
        @media (max-width: 48rem) {
            .dz-sidebar { transform: translateX(-100%); }
            .dz-sidebar.open { transform: translateX(0); }
            .dz-main {
                margin-left: 0;
                max-width: 100vw;
                padding: var(--space-lg) 0.9375rem;
                padding-bottom: 5.5rem;
            }
            .sidebar-toggle { display: flex; }
            .grid-2, .grid-3, .grid-4 { grid-template-columns: 1fr; }
            .stats-grid { grid-template-columns: repeat(2,1fr); }
            .card, .glass-card { padding: var(--space-lg); }
        }
        @media (max-width: 30rem) {
            .dz-main { padding: 0.75rem 0.625rem 5.5rem; }
            .stats-grid { grid-template-columns: 1fr; }
            .form-actions { flex-direction: column; width: 100%; }
            .form-actions .btn { width: 100%; }
        }
    </style>
</head>
<body>
<?php \Core\Timezone::init(\Core\Auth::id()); ?>
<?php include BASE_PATH . '/views/layouts/navbar.php'; ?>

<div class="dz-dashboard">
    <!-- ── Sidebar ── -->
    <aside class="dz-sidebar" id="dzSidebar">
        <a href="/projects/devzone" class="dz-sidebar-logo">
            <div class="logo-icon"><i class="fas fa-terminal"></i></div>
            <div class="logo-text">Dev<span>Zone</span></div>
        </a>

        <div class="sidebar-section">
            <div class="sidebar-title">Workspace</div>
            <nav class="sidebar-nav">
                <a href="/projects/devzone"
                   class="<?= in_array($currentView, ['dashboard'], true) ? 'active' : '' ?>">
                    <i class="fa-solid fa-gauge-high"></i> Dashboard
                </a>
                <a href="/projects/devzone/boards"
                   class="<?= in_array($currentView, ['boards', 'board', 'board-form'], true) ? 'active' : '' ?>">
                    <i class="fas fa-columns"></i> My Boards
                </a>
                <a href="/projects/devzone/tasks"
                   class="<?= ($currentView === 'tasks') ? 'active' : '' ?>">
                    <i class="fas fa-list-check"></i> My Tasks
                </a>
            </nav>
        </div>

        <div class="sidebar-section">
            <div class="sidebar-title">Account</div>
            <nav class="sidebar-nav">
                <a href="/projects/devzone/settings"
                   class="<?= ($currentView === 'settings') ? 'active' : '' ?>">
                    <i class="fa-solid fa-gear"></i> Settings
                </a>
            </nav>
        </div>
    </aside>

    <!-- Overlay for mobile -->
    <div class="sidebar-overlay" id="dzOverlay"></div>

    <!-- ── Main content ── -->
    <main class="dz-main" id="dzMain">
        <?php
        $flash = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
        if (!empty($flash['success'])): ?>
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check"></i>
                <?= htmlspecialchars($flash['success']) ?>
            </div>
        <?php endif;
        if (!empty($flash['error'])): ?>
            <div class="alert alert-error">
                <i class="fa-solid fa-circle-xmark"></i>
                <?= htmlspecialchars($flash['error']) ?>
            </div>
        <?php endif; ?>

        <!-- Toast container -->
        <div id="dz-toast-container" style="position:fixed;bottom:1.5rem;right:1.5rem;z-index:10000;display:flex;flex-direction:column;gap:.625rem;max-width:360px;pointer-events:none;"></div>

        <?php
        $viewFile = PROJECT_PATH . '/views/' . $currentView . '.php';
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            echo '<p style="color:var(--dz-danger);padding:2rem;">View not found: ' . htmlspecialchars($currentView) . '</p>';
        }
        ?>
    </main>

    <!-- Floating mobile sidebar toggle -->
    <button class="sidebar-toggle" id="dzToggle" aria-label="Toggle navigation">
        <i class="fa-solid fa-bars"></i>
    </button>
</div>

<script>
(function () {
    var sidebar = document.getElementById('dzSidebar');
    var overlay = document.getElementById('dzOverlay');
    var toggle  = document.getElementById('dzToggle');

    function openSidebar()  { sidebar.classList.add('open'); overlay.classList.add('active'); toggle.innerHTML = '<i class="fa-solid fa-xmark"></i>'; }
    function closeSidebar() { sidebar.classList.remove('open'); overlay.classList.remove('active'); toggle.innerHTML = '<i class="fa-solid fa-bars"></i>'; }

    if (toggle)  toggle.addEventListener('click', function () { sidebar.classList.contains('open') ? closeSidebar() : openSidebar(); });
    if (overlay) overlay.addEventListener('click', closeSidebar);
    sidebar.querySelectorAll('.sidebar-nav a').forEach(function (a) {
        a.addEventListener('click', function () { if (window.innerWidth <= 768) closeSidebar(); });
    });
    var rt;
    window.addEventListener('resize', function () {
        clearTimeout(rt);
        rt = setTimeout(function () { if (window.innerWidth > 768) closeSidebar(); }, 250);
    });

    // Theme change listener (from navbar toggle)
    document.addEventListener('themeChanged', function (e) {
        if (e && e.detail && e.detail.theme) {
            document.documentElement.setAttribute('data-theme', e.detail.theme);
        }
    });
})();

// ── Toast Notification System ──────────────────────────────────────────────
var DZNotify = (function () {
    var container;
    function getContainer() {
        if (!container) container = document.getElementById('dz-toast-container');
        return container;
    }
    function show(message, type, duration) {
        type = type || 'info';
        duration = duration || 4000;
        var colours = {
            success: { bg: 'rgba(0,255,136,.12)',   border: 'rgba(0,255,136,.4)',   text: '#00ff88', icon: 'fa-circle-check' },
            error:   { bg: 'rgba(255,107,107,.12)', border: 'rgba(255,107,107,.4)', text: '#ff6b6b', icon: 'fa-circle-xmark' },
            warning: { bg: 'rgba(255,170,0,.12)',   border: 'rgba(255,170,0,.4)',   text: '#ffaa00', icon: 'fa-triangle-exclamation' },
            info:    { bg: 'rgba(255,46,196,.12)',  border: 'rgba(255,46,196,.4)',  text: '#ff2ec4', icon: 'fa-circle-info' },
        };
        var c = colours[type] || colours.info;
        var toast = document.createElement('div');
        toast.style.cssText = 'background:' + c.bg + ';border:1px solid ' + c.border + ';color:' + c.text + ';' +
            'padding:.75rem 1rem;border-radius:.625rem;font-size:.875rem;display:flex;align-items:center;gap:.5rem;' +
            'pointer-events:all;backdrop-filter:blur(8px);animation:dz-slide-down .3s ease both;' +
            'box-shadow:0 4px 16px rgba(0,0,0,0.25);font-family:Poppins,sans-serif;';
        toast.innerHTML = '<i class="fa-solid ' + c.icon + '" style="flex-shrink:0;"></i>' +
            '<span style="flex:1;">' + message + '</span>' +
            '<button onclick="this.closest(\'div\').remove()" style="background:none;border:none;color:inherit;cursor:pointer;font-size:.8rem;padding:0;margin-left:.25rem;opacity:.7;">' +
            '<i class="fa-solid fa-xmark"></i></button>';
        var cont = getContainer();
        if (cont) {
            cont.appendChild(toast);
            setTimeout(function () { if (toast.parentNode) { toast.style.opacity='0'; setTimeout(function() { if (toast.parentNode) toast.parentNode.removeChild(toast); }, 300); } }, duration);
        }
    }
    return { success: function(m,d) { show(m,'success',d); }, error: function(m,d) { show(m,'error',d); }, warning: function(m,d) { show(m,'warning',d); }, info: function(m,d) { show(m,'info',d); } };
})();
</script>
</body>
</html>
