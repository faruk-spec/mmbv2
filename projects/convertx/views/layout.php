<?php
// ── Cache busting ──────────────────────────────────────────────────────────
$uiVersion = '20260223235900';
header("Cache-Control: no-cache, no-store, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
?>
<!DOCTYPE html>
<?php

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

    <!-- Google Fonts – Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Universal theme (loaded first) -->
    <link rel="stylesheet" href="/css/universal-theme.css?v=<?= $uiVersion ?>">

    <style>
        /* ═══════════════════════════════════════════════════════════════════
           ConvertX – CSS Variables & Theme
        ═══════════════════════════════════════════════════════════════════ */
        :root {
            /* Brand identity */
            --cx-primary:    #6366f1;
            --cx-secondary:  #8b5cf6;
            --cx-accent:     #06b6d4;
            --cx-success:    #10b981;
            --cx-warning:    #f59e0b;
            --cx-danger:     #ef4444;

            /* Map brand onto platform vars so universal-theme glows use ConvertX colours */
            --purple:  #6366f1;
            --cyan:    #8b5cf6;
            --magenta: #06b6d4;

            /* ── Dark mode structural variables ── */
            --bg-primary:    #06060a;
            --bg-secondary:  #0c0c12;
            --bg-card:       #0f0f18;
            --bg-tertiary:   #13131f;
            --text-primary:  #e8eefc;
            --text-secondary: #8892a6;
            --text-muted:    #8892a6;
            --border-color:  rgba(255,255,255,0.08);
            --border-hover:  rgba(99,102,241,0.45);
            --cx-code-bg:    rgba(0,0,0,0.45);

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
            --cx-primary:    #4f46e5;
            --cx-secondary:  #7c3aed;
            --cx-accent:     #0891b2;
            --purple:  #4f46e5;
            --cyan:    #7c3aed;
            --magenta: #0891b2;

            --bg-primary:    #f4f5fa;
            --bg-secondary:  #ffffff;
            --bg-card:       #ffffff;
            --bg-tertiary:   #eef0f8;
            --text-primary:  #0f0f1a;
            --text-secondary: #4b5563;
            --text-muted:    #6b7280;
            --border-color:  rgba(0,0,0,0.08);
            --border-hover:  rgba(79,70,229,0.45);
            --cx-code-bg:    rgba(0,0,0,0.04);
        }

        /* ── Reset ── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        /* ── html – explicit bg prevents white flash on overscroll ── */
        html {
            scroll-behavior: smooth;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            overflow-x: hidden;
            max-width: 100vw;
            background: var(--bg-primary);   /* ← critical */
        }

        /* ── body – explicit bg+color (mirrors QR project pattern) ── */
        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg-primary);   /* ← critical */
            color: var(--text-primary);       /* ← critical */
            min-height: 100vh;
            font-size: var(--font-md);
            line-height: 1.6;
            overflow-x: hidden;
            -webkit-overflow-scrolling: touch;
            will-change: scroll-position;
            max-width: 100vw;
        }

        /* ── Body ambient glow (ConvertX indigo flavour) ── */
        body::before {
            background:
                radial-gradient(ellipse at 15% 0%,   rgba(99,102,241,0.10) 0%, transparent 50%),
                radial-gradient(ellipse at 85% 100%,  rgba(139,92,246,0.08) 0%, transparent 50%);
        }

        [data-theme="light"] body::before {
            background:
                radial-gradient(ellipse at 15% 0%,   rgba(99,102,241,0.05) 0%, transparent 50%),
                radial-gradient(ellipse at 85% 100%,  rgba(139,92,246,0.04) 0%, transparent 50%);
        }

        /* ── Neural-network grid overlay ── */
        body::after {
            content: '';
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(99,102,241,0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(99,102,241,0.04) 1px, transparent 1px);
            background-size: 52px 52px;
            pointer-events: none;
            z-index: -1;
        }
        [data-theme="light"] body::after {
            background-image:
                linear-gradient(rgba(99,102,241,0.07) 1px, transparent 1px),
                linear-gradient(90deg, rgba(99,102,241,0.07) 1px, transparent 1px);
        }

        /* ── Keyframe animations ── */
        @keyframes cx-fade-up {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        @keyframes cx-slide-down {
            from { opacity: 0; transform: translateY(-10px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        @keyframes cx-pulse-glow {
            0%, 100% { box-shadow: 0 0 0 0 rgba(99,102,241,0); }
            50%       { box-shadow: 0 0 18px 5px rgba(99,102,241,0.32); }
        }

        @keyframes cx-spin { to { transform: rotate(360deg); } }

        @keyframes cx-progress {
            0%   { transform: translateX(-100%); }
            100% { transform: translateX(200%); }
        }

        /* Upload zone subtle pulse */
        @keyframes cx-upload-pulse {
            0%, 100% { border-color: var(--border-color); }
            50%       { border-color: rgba(99,102,241,0.50); box-shadow: 0 0 18px rgba(99,102,241,0.10); }
        }

        /* AI badge shimmer */
        @keyframes cx-shimmer {
            0%   { background-position: -200% center; }
            100% { background-position:  200% center; }
        }

        /* Neon glow pulse for featured items */
        @keyframes cx-neon-pulse {
            0%, 100% { box-shadow: 0 0 8px rgba(99,102,241,0.35), 0 0 24px rgba(99,102,241,0.10); }
            50%       { box-shadow: 0 0 16px rgba(99,102,241,0.60), 0 0 40px rgba(99,102,241,0.20); }
        }

        /* ── Page wrapper ── */
        .cx-dashboard {
            display: flex;
            min-height: calc(100vh - var(--navbar-height));
            margin-top: var(--navbar-height);
            background: var(--bg-primary);   /* ← ensures no white behind sidebar */
        }

        /* ── Sidebar ── */
        .cx-sidebar {
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
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), background 0.3s;
            z-index: 100;
            will-change: transform;
            contain: layout style paint;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
            scrollbar-color: var(--border-color) transparent;
        }

        .cx-sidebar::-webkit-scrollbar { width: 4px; }
        .cx-sidebar::-webkit-scrollbar-track { background: transparent; }
        .cx-sidebar::-webkit-scrollbar-thumb { background: var(--border-color); border-radius: 4px; }

        /* Sidebar logo strip */
        .cx-sidebar-logo {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            padding: 0.25rem 1rem 0.875rem;
            margin-bottom: 0.25rem;
            border-bottom: 1px solid var(--border-color);
        }

        .cx-sidebar-logo .logo-icon {
            width: 1.875rem;
            height: 1.875rem;
            border-radius: 0.5rem;
            background: linear-gradient(135deg, var(--cx-primary), var(--cx-secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            color: #fff;
            flex-shrink: 0;
            box-shadow: 0 2px 8px rgba(99,102,241,0.45);
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
            padding: 0.5rem 0.75rem;
            margin-top: 0.375rem;
        }

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
            background: linear-gradient(135deg, var(--cx-primary), var(--cx-secondary));
            color: #fff;
            box-shadow: 0 3px 10px rgba(99,102,241,0.35);
        }

        .sidebar-nav a i {
            width: 1rem;
            text-align: center;
            flex-shrink: 0;
            font-size: 0.875rem;
        }

        /* ── Main content ── */
        .cx-main {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: var(--space-xl);
            min-height: calc(100vh - var(--navbar-height));
            background: var(--bg-primary);   /* ← critical */
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow-x: auto;
            max-width: calc(100vw - var(--sidebar-width));
        }

        /* Entry animation – staggered on every direct child */
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
            width: 3.25rem;
            height: 3.25rem;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--cx-primary), var(--cx-secondary));
            border: none;
            color: #fff;
            cursor: pointer;
            display: none;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 16px rgba(99,102,241,0.5);
            z-index: 101;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            will-change: transform;
            font-size: 1.1rem;
        }
        .sidebar-toggle:hover  { box-shadow: 0 6px 22px rgba(99,102,241,0.65); }
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
        .sidebar-overlay.active { display: block; animation: cx-fade-up 0.2s ease; }

        /* ── Cards ── */
        .card, .glass-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 0.75rem;
            padding: var(--space-xl);
            margin-bottom: var(--space-xl);
            transition: border-color 0.3s, box-shadow 0.3s, transform 0.3s;
            will-change: transform, box-shadow;
        }
        .card:hover, .glass-card:hover {
            border-color: var(--border-hover);
            box-shadow: 0 8px 32px rgba(99,102,241,0.14), 0 0 0 1px rgba(99,102,241,0.10);
            transform: translateY(-2px);
        }
        .glass-card {
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
        .card-header {
            font-weight: 600;
            margin-bottom: var(--space-lg);
            display: flex;
            align-items: center;
            gap: var(--space-sm);
            font-size: var(--font-md);
            padding-bottom: var(--space-md);
            border-bottom: 1px solid var(--border-color);
            color: var(--text-primary);
        }
        .card-header i { color: var(--cx-primary); }

        /* ── Page header ── */
        .page-header {
            margin-bottom: var(--space-2xl);
        }
        .page-header h1 {
            font-size: clamp(1.5rem, 4vw, 2.25rem);
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
            font-size: var(--font-sm);
        }

        /* Section title */
        .section-title {
            font-size: var(--font-xl);
            font-weight: 600;
            margin-bottom: var(--space-lg);
            display: flex;
            align-items: center;
            gap: var(--space-sm);
            color: var(--text-primary);
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
            will-change: transform;
            position: relative;
            overflow: hidden;
        }
        /* Glow orb inside each stat card */
        .stat-card::before {
            content: '';
            position: absolute;
            top: -30px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 80px;
            background: radial-gradient(circle, rgba(99,102,241,0.15) 0%, transparent 70%);
            pointer-events: none;
        }
        .stat-card:hover {
            border-color: var(--border-hover);
            box-shadow: 0 6px 20px rgba(99,102,241,0.16), 0 0 0 1px rgba(99,102,241,0.12);
            transform: translateY(-3px);
        }
        .stat-card .stat-icon {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            display: block;
            background: linear-gradient(135deg, var(--cx-primary), var(--cx-accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .stat-card .value {
            font-size: 1.875rem;
            font-weight: 700;
            display: block;
            background: linear-gradient(135deg, var(--cx-primary), var(--cx-accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1.1;
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
            will-change: transform;
            line-height: 1;
        }
        @media (min-width: 48rem) {
            .btn { padding: 0.625rem 1.25rem; font-size: var(--font-sm); border-radius: 0.625rem; }
        }
        .btn:disabled { opacity: 0.55; cursor: not-allowed; }
        .btn:active   { transform: translateY(0) !important; }

        .btn-primary {
            background: linear-gradient(135deg, var(--cx-primary), var(--cx-secondary));
            color: #fff;
        }
        .btn-primary:hover:not(:disabled) {
            box-shadow: 0 5px 18px rgba(99,102,241,0.50);
            transform: translateY(-2px);
        }
        .btn-secondary {
            background: var(--bg-secondary);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
        }
        .btn-secondary:hover:not(:disabled) {
            border-color: var(--cx-primary);
            box-shadow: 0 0 14px rgba(99,102,241,0.2);
            transform: translateY(-2px);
        }
        .btn-success {
            background: linear-gradient(135deg, var(--cx-success), #059669);
            color: #fff;
        }
        .btn-success:hover:not(:disabled) {
            box-shadow: 0 5px 18px rgba(16,185,129,0.40);
            transform: translateY(-2px);
        }
        .btn-danger {
            background: linear-gradient(135deg, var(--cx-danger), #dc2626);
            color: #fff;
        }
        .btn-danger:hover:not(:disabled) {
            box-shadow: 0 5px 18px rgba(239,68,68,0.40);
            transform: translateY(-2px);
        }
        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: var(--font-xs);
            border-radius: 0.4rem;
        }
        @media (min-width: 48rem) {
            .btn-sm { padding: 0.4375rem 0.875rem; }
        }

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
            border-color: var(--cx-primary);
            box-shadow: 0 0 0 3px rgba(99,102,241,0.15);
        }
        .form-control::placeholder, .form-input::placeholder { color: var(--text-muted); }
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
            padding: 2.5rem 1.5rem;
            min-height: 10rem;
            text-align: center;
            cursor: pointer;
            transition: border-color 0.25s, background 0.25s, transform 0.25s, box-shadow 0.25s;
            color: var(--text-secondary);
            animation: cx-upload-pulse 3.5s ease-in-out infinite;
            position: relative;
        }
        .upload-zone:hover, .upload-zone.drag-over {
            border-color: var(--cx-primary);
            background: rgba(99,102,241,0.06);
            transform: scale(1.01);
            box-shadow: 0 0 24px rgba(99,102,241,0.14);
            animation: none;
        }
        .upload-zone .upload-icon {
            font-size: 2.5rem;
            margin-bottom: 0.75rem;
            display: block;
            background: linear-gradient(135deg, var(--cx-primary), var(--cx-accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .upload-zone p { color: var(--text-secondary); font-size: var(--font-sm); margin: 0.25rem 0; }
        .upload-zone strong { color: var(--cx-primary); }
        .upload-zone.has-file {
            border-color: var(--cx-success);
            background: rgba(16,185,129,0.05);
            animation: none;
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
        .badge-pending    { background: rgba(245,158,11,.15);  color: var(--cx-warning); }
        .badge-processing { background: rgba(99,102,241,.15);  color: var(--cx-primary); }
        .badge-completed  { background: rgba(16,185,129,.15);  color: var(--cx-success); }
        .badge-failed     { background: rgba(239,68,68,.15);   color: var(--cx-danger);  }
        .badge-cancelled  { background: rgba(136,146,166,.15); color: var(--text-secondary); }

        /* ── AI badge – shimmer sweep ── */
        .ai-badge {
            background: linear-gradient(
                90deg,
                var(--cx-primary)   0%,
                var(--cx-secondary) 40%,
                var(--cx-accent)    60%,
                var(--cx-secondary) 80%,
                var(--cx-primary)   100%
            );
            background-size: 200% auto;
            animation: cx-shimmer 2.8s linear infinite;
            color: #fff;
            padding: 0.2rem 0.65rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        /* ── Server capability notice panel ── */
        .cx-notice {
            --cx-warning-text: #e0a000;
            background: rgba(245,158,11,0.07);
            border: 1px solid rgba(245,158,11,0.35);
            border-radius: 0.625rem;
            padding: 0.875rem 1.125rem;
            margin-bottom: 1.25rem;
            color: var(--cx-warning-text);
            font-size: var(--font-sm);
            display: flex;
            align-items: flex-start;
            gap: 0.625rem;
            animation: cx-slide-down 0.35s ease both;
            line-height: 1.5;
        }
        [data-theme="light"] .cx-notice { --cx-warning-text: #b45309; background: rgba(245,158,11,0.10); }
        .cx-notice > i { flex-shrink: 0; margin-top: 0.15rem; }
        .cx-notice strong { font-weight: 600; display: block; margin-bottom: 0.25rem; }
        .cx-notice .cx-notice-formats {
            display: flex;
            flex-wrap: wrap;
            gap: 0.375rem;
            margin-top: 0.5rem;
        }
        .cx-notice .cx-notice-tag {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.175rem 0.55rem;
            border-radius: 20px;
            font-size: var(--font-xs);
            font-weight: 600;
        }
        .cx-notice-tag.available   { background: rgba(16,185,129,0.12); color: var(--cx-success); }
        .cx-notice-tag.unavailable { background: rgba(239,68,68,0.10);  color: var(--cx-danger);  }

        /* Disabled optgroup label (client-side JS sets class) */
        select optgroup.cx-disabled { color: var(--text-muted); }

        /* ── Table ── */
        .cx-table { width: 100%; border-collapse: collapse; }
        .cx-table th, .cx-table td {
            padding: 0.75rem 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
            font-size: var(--font-sm);
            color: var(--text-primary);
        }
        .cx-table th {
            color: var(--text-secondary);
            font-weight: 500;
            font-size: var(--font-xs);
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .cx-table tbody tr { transition: background 0.15s; }
        .cx-table tbody tr:hover td { background: rgba(99,102,241,0.04); }
        .cx-table tr:last-child td { border-bottom: none; }

        /* ── Alerts ── */
        .alert {
            padding: 0.875rem 1.125rem;
            border-radius: 0.5rem;
            margin-bottom: var(--space-lg);
            font-size: var(--font-sm);
            display: flex;
            align-items: center;
            gap: 0.625rem;
            animation: cx-slide-down 0.3s ease both;
        }
        .alert-success { background: rgba(16,185,129,.10); border: 1px solid var(--cx-success); color: var(--cx-success); }
        .alert-error   { background: rgba(239,68,68,.10);  border: 1px solid var(--cx-danger);  color: var(--cx-danger); }

        /* ── Grid helpers ── */
        .grid   { display: grid; gap: var(--space-lg); }
        .grid-2 { grid-template-columns: repeat(2, 1fr); }
        .grid-3 { grid-template-columns: repeat(3, 1fr); }
        .grid-4 { grid-template-columns: repeat(4, 1fr); }

        /* ── Quick-action card (dashboard) ── */
        .cx-quick-card {
            flex: 1;
            min-width: 160px;
            max-width: 240px;
            padding: 1.25rem;
            border-radius: 0.75rem;
            text-decoration: none;
            text-align: center;
            transition: transform 0.25s, box-shadow 0.25s;
            display: block;
            color: #fff;
        }
        .cx-quick-card:hover { transform: translateY(-4px); }
        .cx-quick-card .qc-icon {
            font-size: 1.75rem;
            margin-bottom: 0.5rem;
            display: block;
            color: #fff;
        }
        .cx-quick-card strong { display: block; font-size: 0.95rem; color: #fff; margin-bottom: 0.125rem; }
        .cx-quick-card p { font-size: 0.78rem; color: rgba(255,255,255,0.82); margin: 0; }

        /* ── AI capability tile ── */
        .cx-ai-tile {
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
        .cx-ai-tile:hover {
            border-color: var(--cx-primary);
            background: rgba(99,102,241,0.08);
            transform: translateY(-3px);
            box-shadow: 0 6px 18px rgba(99,102,241,0.14);
        }
        .cx-ai-tile .tile-icon {
            width: 36px;
            height: 36px;
            margin: 0 auto 0.5rem;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .cx-ai-tile .tile-icon i { color: #fff; font-size: 0.875rem; }
        .cx-ai-tile .tile-title {
            font-size: var(--font-sm);
            font-weight: 600;
            margin-bottom: 0.2rem;
            color: var(--text-primary);
        }
        .cx-ai-tile .tile-desc {
            font-size: var(--font-xs);
            color: var(--text-secondary);
        }

        /* ── AI panel (convert form) ── */
        .cx-ai-panel {
            background: var(--bg-tertiary);
            border: 1px solid rgba(99,102,241,0.25);
            border-radius: 0.625rem;
            padding: 1rem 1.125rem;
            margin-bottom: 1.25rem;
        }
        .cx-ai-panel-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.75rem;
            cursor: pointer;
        }
        .cx-ai-panel-header span { font-size: var(--font-sm); font-weight: 600; color: var(--text-primary); }
        .cx-ai-panel-header i.cx-chevron { margin-left: auto; font-size: 0.75rem; color: var(--text-secondary); }

        /* ── AI option row (convert form checkbox labels) ── */
        .cx-ai-option {
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
        .cx-ai-option:hover { background: rgba(99,102,241,0.07); }
        .cx-ai-option i { color: var(--cx-primary); width: 1rem; text-align: center; flex-shrink: 0; }
        .cx-ai-option input[type="checkbox"] {
            accent-color: var(--cx-primary);
            width: 1rem;
            height: 1rem;
            flex-shrink: 0;
        }

        /* ── Clear preset button ── */
        .cx-clear-btn {
            margin-left: auto;
            padding: 0.375rem 0.75rem;
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 0.4rem;
            text-decoration: none;
            font-size: var(--font-xs);
            font-weight: 500;
            color: var(--text-secondary);
            transition: background 0.2s, color 0.2s, border-color 0.2s;
            flex-shrink: 0;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            cursor: pointer;
        }
        .cx-clear-btn:hover { background: var(--bg-card); color: var(--text-primary); border-color: var(--cx-primary); }

        /* ── Pricing card ── */
        .cx-price-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 0.875rem;
            padding: 1.75rem 1.5rem;
            transition: transform 0.25s, box-shadow 0.25s, border-color 0.25s;
            position: relative;
            overflow: hidden;
        }
        .cx-price-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 16px 40px rgba(99,102,241,0.12);
        }
        .cx-price-card.cx-price-featured { border: 2px solid var(--cx-primary); }
        .cx-price-card.cx-price-featured:hover { box-shadow: 0 16px 40px rgba(99,102,241,0.30); }
        .cx-price-card .plan-name { font-size: 1.15rem; font-weight: 700; text-align: center; margin-bottom: 0.25rem; color: var(--text-primary); }
        .cx-price-card .plan-price { font-size: 2.75rem; font-weight: 800; text-align: center; line-height: 1.1; }
        .cx-price-card .plan-period { font-size: var(--font-xs); color: var(--text-secondary); text-align: center; }
        .cx-price-card .plan-tagline { font-size: var(--font-xs); color: var(--text-secondary); text-align: center; margin-top: 0.5rem; margin-bottom: 1.25rem; }
        .cx-price-card ul {
            list-style: none;
            font-size: var(--font-sm);
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            color: var(--text-primary);
        }
        .cx-price-card ul li { display: flex; align-items: flex-start; gap: 0.5rem; }

        /* ── Responsive breakpoints ── */
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
            .card, .glass-card { padding: var(--space-lg); }
            .cx-quick-card { min-width: 0; max-width: 100%; }
        }

        @media (max-width: 30rem) {
            .cx-main { padding: 0.75rem 0.625rem; }
            .btn:not(.btn-sm) { width: 100%; justify-content: center; padding: 0.75rem 1rem; }
            .stats-grid { grid-template-columns: 1fr; }
            .form-actions { flex-direction: column; width: 100%; }
            .form-actions .btn { width: 100%; }
        }

        /* ═══════════════════════════════════════════════════════════════════
           Futuristic AI UI Enhancements
        ═══════════════════════════════════════════════════════════════════ */

        /* Scanning line effect on upload zone */
        @keyframes cx-scan-line {
            0%   { top: -2px; }
            100% { top: 100%; }
        }
        .upload-zone::before {
            content: '';
            position: absolute;
            left: 0; right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--cx-primary), var(--cx-accent), transparent);
            top: -2px;
            border-radius: inherit;
            animation: cx-scan-line 3.5s linear infinite;
            opacity: 0;
            pointer-events: none;
        }
        .upload-zone:hover::before, .upload-zone.drag-over::before { opacity: 1; }

        /* Holographic card effect */
        .card.cx-holo {
            background: linear-gradient(135deg,
                rgba(99,102,241,0.08) 0%,
                var(--bg-card) 40%,
                rgba(6,182,212,0.06) 100%);
            border-color: rgba(99,102,241,0.25);
        }

        /* Page header glow subtitle */
        .page-header p {
            font-size: var(--font-sm);
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            gap: .375rem;
        }
        .page-header p::before {
            content: '';
            display: inline-block;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--cx-primary);
            box-shadow: 0 0 8px var(--cx-primary);
            animation: cx-pulse-glow 2s ease infinite;
            flex-shrink: 0;
        }

        /* Sidebar nav — neon left accent on active */
        .sidebar-nav a.active {
            position: relative;
        }
        .sidebar-nav a.active::after {
            content: '';
            position: absolute;
            right: 0; top: 15%; bottom: 15%;
            width: 3px;
            border-radius: 3px;
            background: var(--cx-accent);
            box-shadow: 0 0 8px var(--cx-accent);
        }

        /* Stat cards — animate value number in */
        @keyframes cx-count-up {
            from { opacity: 0; transform: translateY(8px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .stat-card .value { animation: cx-count-up 0.55s ease both; animation-delay: 0.2s; }

        /* Button primary — gradient border glow on hover */
        .btn-primary::after {
            content: '';
            position: absolute;
            inset: -1px;
            border-radius: inherit;
            background: linear-gradient(135deg, var(--cx-primary), var(--cx-accent));
            z-index: -1;
            opacity: 0;
            transition: opacity .25s;
            filter: blur(6px);
        }
        .btn { position: relative; }
        .btn-primary:hover:not(:disabled)::after { opacity: 0.6; }

        /* Futuristic sidebar logo pulse */
        .cx-sidebar-logo .logo-icon {
            animation: cx-neon-pulse 3s ease infinite;
        }

        /* Card header — left accent bar */
        .card-header {
            position: relative;
            padding-left: calc(var(--space-md) + 3px);
        }
        .card-header::before {
            content: '';
            position: absolute;
            left: 0;
            top: 20%;
            bottom: 20%;
            width: 3px;
            border-radius: 3px;
            background: linear-gradient(180deg, var(--cx-primary), var(--cx-accent));
        }

        /* Glowing separator line */
        .cx-separator {
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--cx-primary), var(--cx-accent), transparent);
            margin: 1.25rem 0;
            opacity: 0.4;
        }

        /* Terminal-style code display */
        pre, code {
            font-family: 'JetBrains Mono', 'Fira Code', 'Cascadia Code', monospace;
        }

        /* Futuristic input focus ring */
        .form-control:focus, .form-input:focus, .form-select:focus {
            border-color: var(--cx-primary);
            box-shadow: 0 0 0 3px rgba(99,102,241,0.15), 0 0 16px rgba(99,102,241,0.10);
        }
    </style>
</head>
<body>
<?php
// Initialise user timezone
\Core\Timezone::init(\Core\Auth::id());

// Include shared platform navbar
include BASE_PATH . '/views/layouts/navbar.php';
?>

<div class="cx-dashboard">
    <!-- ── Sidebar ── -->
    <aside class="cx-sidebar" id="cxSidebar">

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
                <a href="/projects/convertx/apikeys"
                   class="<?= ($currentView === 'apikeys') ? 'active' : '' ?>">
                    <i class="fa-solid fa-key"></i> API Keys &amp; Analytics
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
                <a href="/projects/convertx/settings"
                   class="<?= ($currentView === 'settings') ? 'active' : '' ?>">
                    <i class="fa-solid fa-gear"></i> Settings
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

        <!-- Toast notification container -->
        <div id="cx-toast-container" style="position:fixed;bottom:1.5rem;right:1.5rem;z-index:10000;display:flex;flex-direction:column;gap:.625rem;max-width:360px;pointer-events:none;"></div>

        <?php
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

    function openSidebar() {
        sidebar.classList.add('open');
        overlay.classList.add('active');
        toggle.innerHTML = '<i class="fa-solid fa-xmark"></i>';
    }

    function closeSidebar() {
        sidebar.classList.remove('open');
        overlay.classList.remove('active');
        toggle.innerHTML = '<i class="fa-solid fa-bars"></i>';
    }

    if (toggle) toggle.addEventListener('click', function () {
        sidebar.classList.contains('open') ? closeSidebar() : openSidebar();
    });

    if (overlay) overlay.addEventListener('click', closeSidebar);

    sidebar.querySelectorAll('.sidebar-nav a').forEach(function (a) {
        a.addEventListener('click', function () {
            if (window.innerWidth <= 768) closeSidebar();
        });
    });

    var resizeTimer;
    window.addEventListener('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function () {
            if (window.innerWidth > 768) closeSidebar();
        }, 250);
    });
})();
</script>
<script>
/**
 * CX Notification System
 * Usage: CXNotify.success('Message') | CXNotify.error('Message') | CXNotify.info('Message') | CXNotify.warning('Message')
 */
var CXNotify = (function () {
    var container;
    function getContainer() {
        if (!container) container = document.getElementById('cx-toast-container');
        return container;
    }
    function show(message, type, duration) {
        type = type || 'info';
        duration = duration || 4000;
        var colours = {
            success: { bg: 'rgba(16,185,129,.12)', border: 'rgba(16,185,129,.4)', text: '#10b981', icon: 'fa-circle-check' },
            error:   { bg: 'rgba(239,68,68,.12)',  border: 'rgba(239,68,68,.4)',  text: '#ef4444', icon: 'fa-circle-xmark' },
            warning: { bg: 'rgba(245,158,11,.12)', border: 'rgba(245,158,11,.4)', text: '#f59e0b', icon: 'fa-triangle-exclamation' },
            info:    { bg: 'rgba(99,102,241,.12)', border: 'rgba(99,102,241,.4)', text: '#6366f1', icon: 'fa-circle-info' },
        };
        var c = colours[type] || colours.info;
        var toast = document.createElement('div');
        toast.style.cssText = [
            'display:flex;align-items:flex-start;gap:.625rem;',
            'background:' + c.bg + ';',
            'border:1px solid ' + c.border + ';',
            'border-radius:.625rem;padding:.75rem 1rem;',
            'font-size:.875rem;color:' + c.text + ';',
            'box-shadow:0 4px 20px rgba(0,0,0,.25);',
            'pointer-events:all;',
            'animation:cx-toast-in .3s ease;',
            'backdrop-filter:blur(8px);',
            'max-width:360px;word-break:break-word;',
        ].join('');
        var icon = document.createElement('i');
        icon.className = 'fa-solid ' + c.icon;
        icon.style.cssText = 'flex-shrink:0;margin-top:.1rem;';
        var msgSpan = document.createElement('span');
        msgSpan.style.flex = '1';
        msgSpan.textContent = message;  // textContent prevents XSS
        var closeBtn = document.createElement('button');
        closeBtn.style.cssText = 'background:none;border:none;color:' + c.text + ';cursor:pointer;font-size:1rem;padding:0;margin-left:.25rem;line-height:1;opacity:.7;';
        closeBtn.textContent = '\u00d7';
        closeBtn.addEventListener('click', function () { toast.remove(); });
        toast.appendChild(icon);
        toast.appendChild(msgSpan);
        toast.appendChild(closeBtn);
        getContainer().appendChild(toast);
        setTimeout(function () {
            toast.style.animation = 'cx-toast-out .3s ease forwards';
            setTimeout(function () { toast.remove(); }, 300);
        }, duration);
    }
    return {
        success: function (m, d) { show(m, 'success', d); },
        error:   function (m, d) { show(m, 'error',   d); },
        warning: function (m, d) { show(m, 'warning', d); },
        info:    function (m, d) { show(m, 'info',    d); },
    };
})();
</script>
<style>
@keyframes cx-toast-in  { from { opacity:0; transform:translateY(16px); } to { opacity:1; transform:translateY(0); } }
@keyframes cx-toast-out { from { opacity:1; transform:translateY(0); }    to { opacity:0; transform:translateY(8px); } }
</style>
</body>
</html>
