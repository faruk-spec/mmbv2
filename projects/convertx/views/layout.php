<!DOCTYPE html>
<?php
// Cache busting
$uiVersion = '20260223120000';
header("Cache-Control: no-cache, no-store, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// Current view is set by the controller via extract()
$currentView = $view ?? 'dashboard';

// Detect active URI segments for nav highlighting
$uri = $_SERVER['REQUEST_URI'] ?? '';
?>
<html lang="en" data-theme="dark">
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

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Universal theme -->
    <link rel="stylesheet" href="/css/universal-theme.css?v=<?= $uiVersion ?>">

    <style>
        /* ── ConvertX colour palette (unchanged) ── */
        :root {
            --cx-primary:    #6366f1;
            --cx-secondary:  #8b5cf6;
            --cx-accent:     #06b6d4;
            --cx-success:    #10b981;
            --cx-warning:    #f59e0b;
            --cx-danger:     #ef4444;
            --bg-primary:    #06060a;
            --bg-secondary:  #0c0c12;
            --bg-card:       #0f0f18;
            --text-primary:  #e8eefc;
            --text-secondary:#8892a6;
            --border-color:  rgba(255,255,255,0.1);
            --sidebar-width: 15rem;          /* 240 px */
            --navbar-height: 3.75rem;        /* 60 px  */

            /* Compact spacing scale */
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
        }

        /* ── Reset ── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html {
            scroll-behavior: smooth;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            overflow-x: hidden;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            font-size: var(--font-md);
            line-height: 1.5;
            -webkit-overflow-scrolling: touch;
            overflow-x: auto;
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
            transition: all 0.2s ease;
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
            contain: layout style;
            overflow-x: auto;
            max-width: calc(100vw - var(--sidebar-width));
        }

        .cx-main.expanded {
            margin-left: 0;
            max-width: 100vw;
        }

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
            transition: transform 0.2s ease;
            will-change: transform;
            font-size: 1.25rem;
        }

        .sidebar-toggle:active { transform: scale(0.95); }

        /* ── Sidebar overlay ── */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: var(--navbar-height);
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.55);
            z-index: 99;
        }

        .sidebar-overlay.active { display: block; }

        /* ── Cards ── */
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 0.75rem;
            padding: var(--space-xl);
            margin-bottom: var(--space-xl);
            transition: border-color 0.3s, box-shadow 0.3s, transform 0.3s;
            will-change: transform, box-shadow;
        }

        .card:hover {
            border-color: rgba(99,102,241,0.35);
            box-shadow: 0 0.25rem 1.25rem rgba(0,0,0,0.25);
            transform: translateY(-2px);
        }

        .card-header {
            font-weight: 600;
            margin-bottom: var(--space-lg);
            display: flex;
            align-items: center;
            gap: var(--space-sm);
            font-size: var(--font-lg);
        }

        .card-header i { color: var(--cx-primary); }

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
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        .stat-card:hover {
            border-color: rgba(99,102,241,0.3);
            box-shadow: 0 0.25rem 1rem rgba(0,0,0,0.2);
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
            box-shadow: 0 6px 20px rgba(99,102,241,0.5);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: var(--bg-secondary);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
        }

        .btn-secondary:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(0,0,0,0.18);
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

        .form-control {
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

        .form-control:focus {
            border-color: var(--cx-primary);
            box-shadow: 0 0 0 3px rgba(99,102,241,0.12);
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
            transition: border-color 0.2s, background 0.2s;
        }

        .upload-zone:hover,
        .upload-zone.drag-over {
            border-color: var(--cx-primary);
            background: rgba(99,102,241,0.06);
        }

        .upload-zone i {
            font-size: 2.5rem;
            color: var(--cx-primary);
            margin-bottom: var(--space-md);
            display: block;
        }

        .upload-zone p { color: var(--text-secondary); font-size: var(--font-sm); }

        /* ── Badges ── */
        .badge {
            display: inline-block;
            padding: 0.2rem 0.65rem;
            border-radius: 20px;
            font-size: var(--font-xs);
            font-weight: 600;
        }

        .badge-pending    { background: rgba(245,158,11,.15);  color: var(--cx-warning); }
        .badge-processing { background: rgba(99,102,241,.15);  color: var(--cx-primary); }
        .badge-completed  { background: rgba(16,185,129,.15);  color: var(--cx-success); }
        .badge-failed     { background: rgba(239,68,68,.15);   color: var(--cx-danger); }
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

        .cx-table tr:last-child td { border-bottom: none; }

        /* ── Alerts ── */
        .alert {
            padding: 0.9375rem 1.25rem;
            border-radius: 0.5rem;
            margin-bottom: var(--space-lg);
            font-size: var(--font-sm);
        }

        .alert-success { background: rgba(16,185,129,.12); border: 1px solid var(--cx-success); color: var(--cx-success); }
        .alert-error   { background: rgba(239,68,68,.12);  border: 1px solid var(--cx-danger);  color: var(--cx-danger); }

        /* ── Grid helpers ── */
        .grid   { display: grid; gap: var(--space-lg); }
        .grid-2 { grid-template-columns: repeat(2, 1fr); }
        .grid-3 { grid-template-columns: repeat(3, 1fr); }
        .grid-4 { grid-template-columns: repeat(4, 1fr); }

        /* ── Responsive ── */
        @media (max-width: 64rem) {
            .grid-3, .grid-4 { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 48rem) {
            /* Slide sidebar off-screen on mobile */
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
// Initialise user timezone for all date displays
\Core\Timezone::init(\Core\Auth::id());

// Include the shared platform navbar
include BASE_PATH . '/views/layouts/navbar.php';
?>

<div class="cx-dashboard">
    <!-- ── Sidebar ── -->
    <aside class="cx-sidebar" id="cxSidebar">

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
            <div class="alert alert-success"><?= htmlspecialchars($flash['success']) ?></div>
        <?php endif;
        if (!empty($flash['error'])): ?>
            <div class="alert alert-error"><?= htmlspecialchars($flash['error']) ?></div>
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

    function open()  { sidebar.classList.add('open');    overlay.classList.add('active'); }
    function close() { sidebar.classList.remove('open'); overlay.classList.remove('active'); }

    if (toggle)  toggle.addEventListener('click', function () { sidebar.classList.contains('open') ? close() : open(); });
    if (overlay) overlay.addEventListener('click', close);

    // Close when a nav link is tapped on mobile
    sidebar.querySelectorAll('.sidebar-nav a').forEach(function (a) {
        a.addEventListener('click', function () { if (window.innerWidth <= 768) close(); });
    });

    // Tidy up on resize
    var resizeTimer;
    window.addEventListener('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function () { if (window.innerWidth > 768) close(); }, 250);
    });
})();
</script>
</body>
</html>
