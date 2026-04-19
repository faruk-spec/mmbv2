<?php
// ── Cache busting ──────────────────────────────────────────────────────────
$uiVersion = '20260223235900';
header("Cache-Control: no-cache, no-store, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
?>
<!DOCTYPE html>
<?php
// ── Anti-FOUC: apply theme from localStorage before first paint ──────────
// (JS snippet injected into <head> below does this at runtime)

// ── Read default theme from DB ─────────────────────────────────────────────
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

$csrfToken = \Core\Security::generateCsrfToken();
?>
<html lang="en" data-theme="<?= htmlspecialchars($defaultTheme) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta name="csrf-token" content="<?= htmlspecialchars($csrfToken) ?>">
    <title><?= htmlspecialchars($title ?? 'CodeXPro') ?> – CodeXPro</title>

    <!-- Anti-FOUC: apply saved theme before first paint -->
    <script>
    (function(){
        try {
            var t = localStorage.getItem('theme');
            if (t === 'light' || t === 'dark') {
                document.documentElement.setAttribute('data-theme', t);
            }
        } catch(e){}
    })();
    </script>

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
           CodeXPro – CSS Variables & Theme
        ═══════════════════════════════════════════════════════════════════ */
        :root {
            /* Brand identity – CodeXPro cyan / green / purple */
            --cx-primary:    #00f0ff;
            --cx-secondary:  #00ff88;
            --cx-accent:     #9945ff;
            --cx-success:    #10b981;
            --cx-warning:    #f59e0b;
            --cx-danger:     #ef4444;

            /* Map brand onto platform vars so universal-theme glows use CodeXPro colours */
            --cyan:    #00f0ff;
            --purple:  #9945ff;
            --magenta: #00ff88;

            /* ── Dark mode structural variables ── */
            --bg-primary:    #06060a;
            --bg-secondary:  #0c0c12;
            --bg-card:       #0f0f18;
            --bg-tertiary:   #13131f;
            --text-primary:  #e8eefc;
            --text-secondary: #8892a6;
            --text-muted:    #8892a6;
            --border-color:  rgba(255,255,255,0.08);
            --border-hover:  rgba(0,240,255,0.45);
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
            --cx-primary:    #0099bb;
            --cx-secondary:  #00aa55;
            --cx-accent:     #7c22dd;
            --cyan:    #0099bb;
            --purple:  #7c22dd;
            --magenta: #00aa55;

            --bg-primary:    #f4f5fa;
            --bg-secondary:  #ffffff;
            --bg-card:       #ffffff;
            --bg-tertiary:   #eef0f8;
            --text-primary:  #0f0f1a;
            --text-secondary: #4b5563;
            --text-muted:    #6b7280;
            --border-color:  rgba(0,0,0,0.08);
            --border-hover:  rgba(0,153,187,0.45);
            --cx-code-bg:    rgba(0,0,0,0.04);
        }

        /* ── Reset ── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        /* ── html – explicit bg prevents white flash on overscroll ── */
        html {
            scroll-behavior: smooth;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            max-width: 100vw;
            background: var(--bg-primary);
        }

        /* ── body ── */
        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            font-size: var(--font-md);
            line-height: 1.6;
            overflow-x: hidden;
            -webkit-overflow-scrolling: touch;
            will-change: scroll-position;
            max-width: 100vw;
        }

        /* ── Body ambient glow (CodeXPro cyan/purple flavour) ── */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background:
                radial-gradient(ellipse at 15% 0%,   rgba(0,240,255,0.10) 0%, transparent 50%),
                radial-gradient(ellipse at 85% 100%,  rgba(153,69,255,0.08) 0%, transparent 50%);
            pointer-events: none;
            z-index: -2;
        }

        [data-theme="light"] body::before {
            background:
                radial-gradient(ellipse at 15% 0%,   rgba(0,240,255,0.05) 0%, transparent 50%),
                radial-gradient(ellipse at 85% 100%,  rgba(153,69,255,0.04) 0%, transparent 50%);
        }

        /* ── Grid pattern overlay ── */
        body::after {
            content: '';
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(0,240,255,0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0,240,255,0.04) 1px, transparent 1px);
            background-size: 52px 52px;
            pointer-events: none;
            z-index: -1;
        }
        [data-theme="light"] body::after {
            background-image:
                linear-gradient(rgba(0,153,187,0.07) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0,153,187,0.07) 1px, transparent 1px);
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
            0%, 100% { box-shadow: 0 0 0 0 rgba(0,240,255,0); }
            50%       { box-shadow: 0 0 18px 5px rgba(0,240,255,0.32); }
        }

        @keyframes cx-spin { to { transform: rotate(360deg); } }

        @keyframes cx-shimmer {
            0%   { background-position: -200% center; }
            100% { background-position:  200% center; }
        }

        @keyframes cx-neon-pulse {
            0%, 100% { box-shadow: 0 0 8px rgba(0,240,255,0.35), 0 0 24px rgba(0,240,255,0.10); }
            50%       { box-shadow: 0 0 16px rgba(0,240,255,0.60), 0 0 40px rgba(0,240,255,0.20); }
        }

        @keyframes cx-count-up {
            from { opacity: 0; transform: translateY(8px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        @keyframes cx-scan-line {
            0%   { top: -2px; }
            100% { top: 100%; }
        }

        /* ── Page wrapper ── */
        .cx-dashboard {
            display: flex;
            min-height: calc(100vh - var(--navbar-height));
            background: var(--bg-primary);
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
            color: #06060a;
            flex-shrink: 0;
            box-shadow: 0 2px 8px rgba(0,240,255,0.45);
            animation: cx-neon-pulse 3s ease infinite;
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
            background: linear-gradient(135deg, rgba(0,240,255,0.18), rgba(0,255,136,0.12));
            color: var(--cx-primary);
            box-shadow: 0 3px 10px rgba(0,240,255,0.15);
            position: relative;
        }

        /* Neon right indicator on active */
        .sidebar-nav a.active::after {
            content: '';
            position: absolute;
            right: 0; top: 15%; bottom: 15%;
            width: 3px;
            border-radius: 3px;
            background: var(--cx-accent);
            box-shadow: 0 0 8px var(--cx-accent);
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
            background: var(--bg-primary);
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

        /* ── Floating mobile toggle (bottom-right) ── */
        .sidebar-toggle {
            position: fixed;
            bottom: var(--space-xl);
            right: var(--space-xl);
            width: 3.25rem;
            height: 3.25rem;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--cx-primary), var(--cx-secondary));
            border: none;
            color: #06060a;
            cursor: pointer;
            display: none;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 16px rgba(0,240,255,0.5);
            z-index: 101;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            will-change: transform;
            font-size: 1.1rem;
        }
        .sidebar-toggle:hover  { box-shadow: 0 6px 22px rgba(0,240,255,0.65); }
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
            box-shadow: 0 8px 32px rgba(0,240,255,0.10), 0 0 0 1px rgba(0,240,255,0.08);
            transform: translateY(-2px);
        }
        .glass-card {
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        /* Card header – left neon bar */
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
        .card-header i { color: var(--cx-primary); }

        /* Holographic card variant */
        .card.cx-holo {
            background: linear-gradient(135deg,
                rgba(0,240,255,0.06) 0%,
                var(--bg-card) 40%,
                rgba(153,69,255,0.05) 100%);
            border-color: rgba(0,240,255,0.20);
        }

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
        /* Pulsing dot before subtitle */
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
        /* Glow orb */
        .stat-card::before {
            content: '';
            position: absolute;
            top: -30px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 80px;
            background: radial-gradient(circle, rgba(0,240,255,0.12) 0%, transparent 70%);
            pointer-events: none;
        }
        .stat-card:hover {
            border-color: var(--border-hover);
            box-shadow: 0 6px 20px rgba(0,240,255,0.14), 0 0 0 1px rgba(0,240,255,0.10);
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
            animation: cx-count-up 0.55s ease both;
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
            will-change: transform;
            line-height: 1;
            position: relative;
        }
        @media (min-width: 48rem) {
            .btn { padding: 0.625rem 1.25rem; font-size: var(--font-sm); border-radius: 0.625rem; }
        }
        .btn:disabled { opacity: 0.55; cursor: not-allowed; }
        .btn:active   { transform: translateY(0) !important; }

        .btn-primary {
            background: linear-gradient(135deg, var(--cx-primary), var(--cx-secondary));
            color: #06060a;
        }
        .btn-primary:hover:not(:disabled) {
            box-shadow: 0 5px 18px rgba(0,240,255,0.45);
            transform: translateY(-2px);
        }
        /* Gradient border glow on hover */
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
        .btn-primary:hover:not(:disabled)::after { opacity: 0.6; }

        .btn-secondary {
            background: var(--bg-secondary);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
        }
        .btn-secondary:hover:not(:disabled) {
            border-color: var(--cx-primary);
            box-shadow: 0 0 14px rgba(0,240,255,0.2);
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
            border-color: var(--cx-primary);
            box-shadow: 0 0 0 3px rgba(0,240,255,0.15), 0 0 16px rgba(0,240,255,0.08);
        }
        .form-control::placeholder, .form-input::placeholder { color: var(--text-muted); }
        .form-actions {
            display: flex;
            gap: 0.75rem;
            align-items: center;
            flex-wrap: wrap;
        }

        /* ── Quick-action cards ── */
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

        /* ── Feature hub tiles ── */
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
            background: rgba(0,240,255,0.06);
            transform: translateY(-3px);
            box-shadow: 0 6px 18px rgba(0,240,255,0.12);
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
        .cx-ai-tile .tile-title { font-size: 0.82rem; font-weight: 600; margin-bottom: 0.2rem; color: var(--text-primary); }
        .cx-ai-tile .tile-desc { font-size: 0.72rem; color: var(--text-secondary); }

        /* ── Badges ── */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.2rem 0.6rem;
            border-radius: 20px;
            font-size: var(--font-xs);
            font-weight: 600;
        }
        .badge-success   { background: rgba(0,255,136,.15);  color: var(--cx-secondary); }
        .badge-secondary { background: rgba(136,146,166,.15); color: var(--text-secondary); }
        .badge-pending   { background: rgba(245,158,11,.15);  color: var(--cx-warning); }
        .badge-danger    { background: rgba(239,68,68,.15);   color: var(--cx-danger); }
        .badge-info      { background: rgba(0,240,255,.12);   color: var(--cx-primary); }

        /* Language badges */
        .badge-javascript { background: rgba(247,223,30,.2);  color: #f7df1e; }
        .badge-python     { background: rgba(55,118,171,.2);  color: #3776ab; }
        .badge-php        { background: rgba(119,123,180,.2); color: #777bb4; }
        .badge-html       { background: rgba(227,76,38,.2);   color: #e34c26; }
        .badge-css        { background: rgba(38,77,228,.2);   color: #264de4; }

        /* Shimmer badge */
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
            color: #06060a;
            padding: 0.2rem 0.65rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        /* ── Glowing separator ── */
        .cx-separator {
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--cx-primary), var(--cx-accent), transparent);
            margin: 1.25rem 0;
            opacity: 0.4;
        }

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
        .alert-success { background: rgba(0,255,136,.08); border: 1px solid var(--cx-secondary); color: var(--cx-secondary); }
        .alert-error   { background: rgba(239,68,68,.10); border: 1px solid var(--cx-danger);    color: var(--cx-danger); }

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
        .cx-table tbody tr:hover td { background: rgba(0,240,255,0.04); }
        .cx-table tr:last-child td { border-bottom: none; }

        /* ── Grid helpers ── */
        .grid   { display: grid; gap: var(--space-lg); }
        .grid-2 { grid-template-columns: repeat(2, 1fr); }
        .grid-3 { grid-template-columns: repeat(3, 1fr); }
        .grid-4 { grid-template-columns: repeat(4, 1fr); }

        /* ── Content grid ── */
        .content-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: var(--space-xl);
        }

        /* ── Card body / footer ── */
        .card-body { margin-bottom: var(--space-lg); }
        .card-body p { color: var(--text-secondary); line-height: 1.6; }
        .card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: var(--space-lg);
            padding-top: var(--space-lg);
            border-top: 1px solid var(--border-color);
        }
        .card-meta {
            display: flex;
            gap: 0.75rem;
            align-items: center;
            font-size: var(--font-xs);
            color: var(--text-secondary);
        }
        .card-actions { display: flex; gap: 0.5rem; }

        /* ── Empty state ── */
        .empty-state {
            text-align: center;
            padding: 3.75rem 1.25rem;
            grid-column: 1 / -1;
        }
        .empty-state h3 { font-size: 1.5rem; color: var(--text-primary); margin-bottom: 0.75rem; }
        .empty-state p  { color: var(--text-secondary); margin-bottom: 1.5rem; }

        /* ── Modal (delete / generic) ── */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.75);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .modal-overlay.show { opacity: 1; }

        .modal-dialog {
            background: var(--bg-card);
            border-radius: 0.875rem;
            border: 1px solid var(--border-color);
            max-width: 500px;
            width: 90%;
            box-shadow: 0 10px 40px rgba(0,0,0,0.5);
            transform: scale(0.9);
            transition: transform 0.3s ease;
        }
        .modal-overlay.show .modal-dialog { transform: scale(1); }

        .modal-dialog.delete-modal .modal-header {
            background: linear-gradient(135deg, rgba(239,68,68,0.10), rgba(153,69,255,0.08));
            padding: 1.5rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 1rem;
            border-radius: 0.875rem 0.875rem 0 0;
        }
        .modal-dialog.delete-modal .modal-header i { font-size: 2rem; color: var(--cx-danger); }
        .modal-dialog.delete-modal .modal-header h3 { font-size: 1.5rem; color: var(--text-primary); margin: 0; }
        .modal-dialog .modal-body { padding: 1.5rem; }
        .modal-dialog .modal-body p { color: var(--text-secondary); line-height: 1.6; margin: 0; }
        .modal-dialog .modal-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--border-color);
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
        }

        /* ── Quick-edit panel ── */
        .quick-edit-panel {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            opacity: 0;
            max-height: 0;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        .quick-edit-panel.show {
            opacity: 1;
            max-height: 600px;
        }
        .quick-edit-content h3 {
            color: var(--cx-primary);
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.625rem;
            font-size: var(--font-lg);
        }
        .quick-edit-form .form-group { margin-bottom: 1.25rem; }
        .quick-edit-form label {
            display: block;
            color: var(--text-primary);
            font-weight: 600;
            margin-bottom: 0.5rem;
            font-size: var(--font-sm);
        }
        .quick-edit-form .form-control {
            width: 100%;
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            padding: 0.75rem;
            border-radius: 0.5rem;
            font-family: inherit;
            font-size: var(--font-sm);
            transition: border-color 0.3s;
            outline: none;
        }
        .quick-edit-form .form-control:focus {
            border-color: var(--cx-primary);
            box-shadow: 0 0 0 3px rgba(0,240,255,0.12);
        }
        .quick-edit-form textarea.form-control { resize: vertical; min-height: 80px; }
        .quick-edit-form .form-actions { margin-top: 1.5rem; }

        /* ── Toggle switch ── */
        .toggle-label {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            cursor: pointer;
            user-select: none;
        }
        .toggle-label input[type="checkbox"] { display: none; }
        .toggle-slider {
            position: relative;
            width: 50px;
            height: 26px;
            background: #333;
            border-radius: 26px;
            transition: background 0.3s;
            flex-shrink: 0;
        }
        .toggle-slider::before {
            content: '';
            position: absolute;
            top: 3px;
            left: 3px;
            width: 20px;
            height: 20px;
            background: #fff;
            border-radius: 50%;
            transition: transform 0.3s;
        }
        .toggle-label input[type="checkbox"]:checked + .toggle-slider { background: var(--cx-primary); }
        .toggle-label input[type="checkbox"]:checked + .toggle-slider::before { transform: translateX(24px); }
        .toggle-text { color: var(--text-primary); font-weight: 500; font-size: var(--font-sm); }

        /* ── Terminal/code styles ── */
        pre, code {
            font-family: 'JetBrains Mono', 'Fira Code', 'Cascadia Code', monospace;
        }

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
                padding-bottom: 5.5rem;
                overflow-x: auto !important;
            }
            .sidebar-toggle { display: flex; }
            .grid-2, .grid-3, .grid-4 { grid-template-columns: 1fr; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .card, .glass-card { padding: var(--space-lg); }
            .card-header { flex-wrap: wrap; }
            .content-grid { grid-template-columns: 1fr; }
        }

        @media (max-width: 30rem) {
            .cx-main { padding: 0.75rem 0.625rem 5.5rem; }
            .stats-grid { grid-template-columns: 1fr; }
            .form-actions { flex-direction: column; width: 100%; }
            .form-actions .btn { width: 100%; }
            .cx-table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        }
    </style>
</head>
<body>
<?php
\Core\Timezone::init(\Core\Auth::id());

// Include shared platform navbar
include BASE_PATH . '/views/layouts/navbar.php';
?>

<div class="cx-dashboard">
    <!-- ── Sidebar ── -->
    <aside class="cx-sidebar" id="cxSidebar">

        <div class="cx-sidebar-logo">
            <div class="logo-icon"><i class="fa-solid fa-code"></i></div>
            <div class="logo-text">Code<span>XPro</span></div>
        </div>

        <div class="sidebar-section">
            <div class="sidebar-title">Main</div>
            <nav class="sidebar-nav">
                <a href="/projects/codexpro"
                   class="<?= ($currentPage ?? '') === 'dashboard' ? 'active' : '' ?>">
                    <i class="fa-solid fa-gauge-high"></i> Dashboard
                </a>
                <a href="/projects/codexpro/editor"
                   class="<?= ($currentPage ?? '') === 'editor' ? 'active' : '' ?>">
                    <i class="fa-solid fa-terminal"></i> Code Editor
                </a>
            </nav>
        </div>

        <div class="sidebar-section">
            <div class="sidebar-title">Manage</div>
            <nav class="sidebar-nav">
                <a href="/projects/codexpro/projects"
                   class="<?= ($currentPage ?? '') === 'projects' ? 'active' : '' ?>">
                    <i class="fa-solid fa-folder-open"></i> Projects
                </a>
                <a href="/projects/codexpro/snippets"
                   class="<?= ($currentPage ?? '') === 'snippets' ? 'active' : '' ?>">
                    <i class="fa-solid fa-code"></i> Snippets
                </a>
                <a href="/projects/codexpro/templates"
                   class="<?= ($currentPage ?? '') === 'templates' ? 'active' : '' ?>">
                    <i class="fa-solid fa-file-code"></i> Templates
                </a>
            </nav>
        </div>

        <div class="sidebar-section">
            <div class="sidebar-title">Account</div>
            <nav class="sidebar-nav">
                <a href="/projects/codexpro/settings"
                   class="<?= ($currentPage ?? '') === 'settings' ? 'active' : '' ?>">
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

        <?= $content ?? '' ?>
    </main>

    <!-- Floating mobile sidebar toggle -->
    <button class="sidebar-toggle" id="cxToggle" aria-label="Toggle navigation">
        <i class="fa-solid fa-bars"></i>
    </button>
</div>

<script>
/* ── CSRF fetch interceptor ── */
(function initThemeAndCsrf() {
    var html = document.documentElement;
    try {
        var saved = localStorage.getItem('theme');
        if (saved === 'light' || saved === 'dark') html.setAttribute('data-theme', saved);
    } catch(e){}

    document.addEventListener('themeChanged', function(e) {
        if (e && e.detail && e.detail.theme) html.setAttribute('data-theme', e.detail.theme);
    });

    var csrfToken = (document.querySelector('meta[name="csrf-token"]') || {}).getAttribute('content') || '';
    var nativeFetch = window.fetch.bind(window);
    window.fetch = function(resource, init) {
        var requestInit = Object.assign({}, init || {});
        var method = String(requestInit.method || 'GET').toUpperCase();
        var url = typeof resource === 'string' ? resource : (resource && resource.url ? resource.url : '');
        var isSameOrigin = !url || url.charAt(0) === '/' || url.indexOf(window.location.origin) === 0;
        if (csrfToken && isSameOrigin && ['GET','HEAD','OPTIONS'].indexOf(method) === -1) {
            requestInit.headers = new Headers(requestInit.headers || {});
            if (!requestInit.headers.has('X-CSRF-Token')) requestInit.headers.set('X-CSRF-Token', csrfToken);
            if (!requestInit.headers.has('Accept'))       requestInit.headers.set('Accept', 'application/json');
        }
        return nativeFetch(resource, requestInit);
    };
})();

/* ── Mobile sidebar toggle ── */
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

    if (toggle)  toggle.addEventListener('click', function() { sidebar.classList.contains('open') ? closeSidebar() : openSidebar(); });
    if (overlay) overlay.addEventListener('click', closeSidebar);

    sidebar.querySelectorAll('.sidebar-nav a').forEach(function(a) {
        a.addEventListener('click', function() { if (window.innerWidth <= 768) closeSidebar(); });
    });

    var resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() { if (window.innerWidth > 768) closeSidebar(); }, 250);
    });
})();

/* ── CXPNotify toast system ── */
var CXPNotify = (function () {
    var container;
    function getContainer() {
        if (!container) container = document.getElementById('cx-toast-container');
        return container;
    }
    function show(message, type, duration) {
        type     = type     || 'info';
        duration = duration || 4000;
        var colours = {
            success: { bg: 'rgba(0,255,136,.12)',   border: 'rgba(0,255,136,.4)',   text: '#00ff88', icon: 'fa-circle-check' },
            error:   { bg: 'rgba(239,68,68,.12)',   border: 'rgba(239,68,68,.4)',   text: '#ef4444', icon: 'fa-circle-xmark' },
            warning: { bg: 'rgba(245,158,11,.12)',  border: 'rgba(245,158,11,.4)',  text: '#f59e0b', icon: 'fa-triangle-exclamation' },
            info:    { bg: 'rgba(0,240,255,.12)',   border: 'rgba(0,240,255,.4)',   text: '#00f0ff', icon: 'fa-circle-info' },
        };
        var c = colours[type] || colours.info;
        var toast = document.createElement('div');
        toast.style.cssText = [
            'display:flex;align-items:flex-start;gap:.625rem;',
            'background:' + c.bg + ';',
            'border:1px solid ' + c.border + ';',
            'border-radius:.625rem;padding:.75rem 1rem;',
            'font-size:.875rem;color:' + c.text + ';',
            'box-shadow:0 4px 20px rgba(0,0,0,.3);',
            'pointer-events:all;',
            'animation:cxp-toast-in .3s ease;',
            'backdrop-filter:blur(8px);',
            'max-width:360px;word-break:break-word;',
        ].join('');
        var icon = document.createElement('i');
        icon.className = 'fa-solid ' + c.icon;
        icon.style.cssText = 'flex-shrink:0;margin-top:.1rem;';
        var msgSpan = document.createElement('span');
        msgSpan.style.flex = '1';
        msgSpan.textContent = message;
        var closeBtn = document.createElement('button');
        closeBtn.style.cssText = 'background:none;border:none;color:' + c.text + ';cursor:pointer;font-size:1rem;padding:0;margin-left:.25rem;line-height:1;opacity:.7;';
        closeBtn.textContent = '\u00d7';
        closeBtn.addEventListener('click', function() { toast.remove(); });
        toast.appendChild(icon);
        toast.appendChild(msgSpan);
        toast.appendChild(closeBtn);
        getContainer().appendChild(toast);
        setTimeout(function() {
            toast.style.animation = 'cxp-toast-out .3s ease forwards';
            setTimeout(function() { toast.remove(); }, 300);
        }, duration);
    }
    return {
        success: function(m, d) { show(m, 'success', d); },
        error:   function(m, d) { show(m, 'error',   d); },
        warning: function(m, d) { show(m, 'warning', d); },
        info:    function(m, d) { show(m, 'info',    d); },
    };
})();

/* ── showNotification (views call this) ── */
function showNotification(message, type) {
    type = type || 'success';
    if (type === 'success') CXPNotify.success(message);
    else if (type === 'error')   CXPNotify.error(message);
    else if (type === 'warning') CXPNotify.warning(message);
    else                         CXPNotify.info(message);
}

/* ── Delete modal ── */
function showDeleteModal(title, message, onConfirm) {
    var overlay = document.createElement('div');
    overlay.id = 'deleteModalOverlay';
    overlay.className = 'modal-overlay';
    overlay.style.display = 'flex';

    var dialog = document.createElement('div');
    dialog.className = 'modal-dialog delete-modal';

    var hdr = document.createElement('div');
    hdr.className = 'modal-header';
    hdr.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i><h3>' + title + '</h3>';

    var body = document.createElement('div');
    body.className = 'modal-body';
    body.innerHTML = '<p>' + message + '</p>';

    var footer = document.createElement('div');
    footer.className = 'modal-footer';
    footer.innerHTML =
        '<button class="btn btn-secondary" onclick="closeDeleteModal()"><i class="fa-solid fa-xmark"></i> Cancel</button>' +
        '<button id="confirmDeleteBtn" class="btn btn-danger"><i class="fa-solid fa-trash"></i> Delete</button>';

    dialog.appendChild(hdr);
    dialog.appendChild(body);
    dialog.appendChild(footer);
    overlay.appendChild(dialog);
    document.body.appendChild(overlay);

    requestAnimationFrame(function() { overlay.classList.add('show'); });

    document.getElementById('confirmDeleteBtn').onclick = function() {
        closeDeleteModal();
        onConfirm();
    };
    overlay.addEventListener('click', function(e) { if (e.target === overlay) closeDeleteModal(); });
    document.addEventListener('keydown', function escHandler(e) {
        if (e.key === 'Escape') { closeDeleteModal(); document.removeEventListener('keydown', escHandler); }
    });
}

function closeDeleteModal() {
    var overlay = document.getElementById('deleteModalOverlay');
    if (overlay) {
        overlay.classList.remove('show');
        setTimeout(function() { overlay.remove(); }, 300);
    }
}

/* ── Relative time ── */
function getRelativeTime(timestamp) {
    var now  = Math.floor(Date.now() / 1000);
    var diff = now - timestamp;
    if (diff < 60)      return 'Just now';
    if (diff < 3600)    { var m = Math.floor(diff / 60);      return m + ' minute'  + (m > 1 ? 's' : '') + ' ago'; }
    if (diff < 86400)   { var h = Math.floor(diff / 3600);    return h + ' hour'    + (h > 1 ? 's' : '') + ' ago'; }
    if (diff < 604800)  { var d = Math.floor(diff / 86400);   return d + ' day'     + (d > 1 ? 's' : '') + ' ago'; }
    if (diff < 2592000) { var w = Math.floor(diff / 604800);  return w + ' week'    + (w > 1 ? 's' : '') + ' ago'; }
    if (diff < 31536000){ var mo= Math.floor(diff / 2592000); return mo + ' month'  + (mo > 1 ? 's' : '') + ' ago'; }
    var y = Math.floor(diff / 31536000); return y + ' year' + (y > 1 ? 's' : '') + ' ago';
}

function updateRelativeTimes() {
    document.querySelectorAll('.relative-time').forEach(function(el) {
        var ts = parseInt(el.getAttribute('data-time'));
        if (ts) el.textContent = getRelativeTime(ts);
    });
}

if (document.querySelector('.relative-time')) {
    updateRelativeTimes();
    setInterval(updateRelativeTimes, 60000);
}

/* ── Quick-edit (snippet) ── */
function toggleQuickEdit() {
    var panel = document.getElementById('quickEditPanel');
    if (!panel) return;
    if (panel.style.display === 'none' || !panel.style.display) {
        panel.style.display = 'block';
        setTimeout(function() { panel.classList.add('show'); }, 10);
    } else {
        panel.classList.remove('show');
        setTimeout(function() { panel.style.display = 'none'; }, 300);
    }
}

function saveQuickEdit() {
    var snippetId = window.snippetId || (document.querySelector('[data-snippet-id]') || {}).dataset && document.querySelector('[data-snippet-id]').dataset.snippetId;
    if (!snippetId) { showNotification('Snippet ID not found', 'error'); return; }

    var title       = (document.getElementById('quickTitle')       || {}).value;
    var description = (document.getElementById('quickDescription') || {}).value;
    var isPublic    = (document.getElementById('quickPublic')      || {}).checked;

    if (!title || !title.trim()) { showNotification('Title is required', 'error'); return; }

    var saveBtn = event.target;
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving…';

    fetch('/projects/codexpro/snippets/' + snippetId + '/quick-update', {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ title: title, description: description, is_public: isPublic })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            var titleEl = document.getElementById('snippetTitle');
            if (titleEl) titleEl.textContent = title;
            var descEl = document.getElementById('snippetDescription');
            if (descEl) descEl.textContent = description;
            var badge     = document.getElementById('visibilityBadge');
            var badgeIcon = badge && badge.querySelector('i');
            var badgeText = document.getElementById('visibilityText');
            if (badge && badgeIcon && badgeText) {
                if (isPublic) {
                    badge.className = 'badge badge-success';
                    badgeIcon.className = 'fas fa-globe';
                    badgeText.textContent = 'Public';
                } else {
                    badge.className = 'badge badge-secondary';
                    badgeIcon.className = 'fas fa-lock';
                    badgeText.textContent = 'Private';
                }
            }
            showNotification('Snippet updated successfully!', 'success');
            toggleQuickEdit();
        } else {
            showNotification(data.error || 'Failed to update snippet', 'error');
        }
    })
    .catch(function(err) {
        console.error('Save error:', err);
        showNotification('An error occurred while updating', 'error');
    })
    .finally(function() {
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Save Changes';
    });
}

function deleteSnippet(id) {
    showDeleteModal(
        'Delete Snippet',
        'Are you sure you want to delete this snippet? This action cannot be undone.',
        function() {
            fetch('/projects/codexpro/snippets/' + id, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({})
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    showNotification('Snippet deleted successfully!', 'success');
                    setTimeout(function() { window.location.href = '/projects/codexpro/snippets'; }, 1000);
                } else {
                    showNotification(data.error || 'Failed to delete snippet', 'error');
                }
            })
            .catch(function(err) {
                console.error('Delete error:', err);
                showNotification('An error occurred while deleting', 'error');
            });
        }
    );
}

function shareSnippet() {
    var url = window.location.href;
    if (navigator.clipboard) {
        navigator.clipboard.writeText(url)
            .then(function()  { showNotification('Share link copied to clipboard!', 'success'); })
            .catch(function() { showNotification('Failed to copy share link', 'error'); });
    } else {
        showNotification('Clipboard not supported', 'error');
    }
}

function copyCode() {
    var codeEl = document.querySelector('.code-container code') || document.querySelector('pre code');
    if (!codeEl) { showNotification('Code not found', 'error'); return; }
    if (navigator.clipboard) {
        navigator.clipboard.writeText(codeEl.textContent)
            .then(function()  { showNotification('Code copied to clipboard!', 'success'); })
            .catch(function() { showNotification('Failed to copy code', 'error'); });
    } else {
        showNotification('Clipboard not supported', 'error');
    }
}

/* ── Quick-edit (project) ── */
function toggleProjectQuickEdit(id) {
    var panel = document.getElementById('projectQuickEditPanel' + id);
    if (!panel) return;
    if (panel.style.display === 'none' || !panel.style.display) {
        panel.style.display = 'block';
        setTimeout(function() { panel.classList.add('show'); }, 10);
    } else {
        panel.classList.remove('show');
        setTimeout(function() { panel.style.display = 'none'; }, 300);
    }
}

function saveProjectQuickEdit(id) {
    var name        = (document.getElementById('quickName' + id)          || {}).value;
    var description = (document.getElementById('quickDesc' + id)          || {}).value;
    var isPublic    = (document.getElementById('quickPublicProject' + id) || {}).checked;

    if (!name || !name.trim()) { showNotification('Project name is required', 'error'); return; }

    var saveBtn = event.target;
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving…';

    fetch('/projects/codexpro/projects/' + id + '/quick-update', {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name: name, description: description, visibility: isPublic ? 'public' : 'private' })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            showNotification('Project updated successfully!', 'success');
            setTimeout(function() { window.location.reload(); }, 1000);
        } else {
            showNotification(data.error || 'Failed to update project', 'error');
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Save Changes';
        }
    })
    .catch(function(err) {
        console.error('Save error:', err);
        showNotification('An error occurred while updating', 'error');
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Save Changes';
    });
}

function deleteProject(id) {
    showDeleteModal(
        'Delete Project',
        'Are you sure you want to delete this project? This action cannot be undone.',
        function() {
            fetch('/projects/codexpro/projects/' + id, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({})
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    showNotification('Project deleted successfully!', 'success');
                    setTimeout(function() { window.location.href = '/projects/codexpro/projects'; }, 1000);
                } else {
                    showNotification(data.error || 'Failed to delete project', 'error');
                }
            })
            .catch(function(err) {
                console.error('Delete error:', err);
                showNotification('An error occurred while deleting', 'error');
            });
        }
    );
}
</script>

<style>
@keyframes cxp-toast-in  { from { opacity:0; transform:translateY(16px); } to { opacity:1; transform:translateY(0); } }
@keyframes cxp-toast-out { from { opacity:1; transform:translateY(0); }    to { opacity:0; transform:translateY(8px); } }
</style>
</body>
</html>
