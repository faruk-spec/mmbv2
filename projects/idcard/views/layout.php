<!DOCTYPE html>
<?php
$defaultTheme = 'dark';
try {
    $db = \Core\Database::getInstance();
    $navbarSettings = $db->fetch("SELECT default_theme FROM navbar_settings WHERE id = 1");
    if ($navbarSettings && !empty($navbarSettings['default_theme'])) {
        $defaultTheme = $navbarSettings['default_theme'];
    }
} catch (\Exception $e) {}

$uiVersion = '20260330000000';
header("Cache-Control: no-cache, no-store, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
?>
<html lang="en" data-theme="<?= htmlspecialchars($defaultTheme) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta name="csrf-token" content="<?= \Core\Security::generateCsrfToken() ?>">
    <title><?= htmlspecialchars($title ?? 'CardX') ?> - MyMultiBranch</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
          integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw=="
          crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/universal-theme.css?v=<?= $uiVersion ?>">

    <style>
        :root {
            --bg-primary: #06060a;
            --bg-secondary: #0c0c12;
            --bg-card: #0f0f18;
            --indigo: #6366f1;
            --indigo-dark: #4f46e5;
            --cyan: #00f0ff;
            --magenta: #ff2ec4;
            --green: #00ff88;
            --amber: #f59e0b;
            --text-primary: #e8eefc;
            --text-secondary: #8892a6;
            --border-color: rgba(255,255,255,0.1);
            --sidebar-width: 15rem;
            --space-xs: 0.25rem;
            --space-sm: 0.375rem;
            --space-md: 0.75rem;
            --space-lg: 1rem;
            --space-xl: 1.5rem;
            --space-2xl: 2rem;
            --font-xs: 0.75rem;
            --font-sm: 0.875rem;
            --font-md: 1rem;
            --font-lg: 1.125rem;
            --font-xl: 1.25rem;
            --font-2xl: 1.5rem;
        }
        [data-theme="light"] {
            --bg-primary: #f8f9fa;
            --bg-secondary: #ffffff;
            --bg-card: #ffffff;
            --text-primary: #1a1a1a;
            --text-secondary: #666666;
            --border-color: rgba(0,0,0,0.1);
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        html { scroll-behavior:smooth; -webkit-font-smoothing:antialiased; max-width:100vw; }
        body {
            font-family:'Poppins',sans-serif;
            background:var(--bg-primary);
            color:var(--text-primary);
            min-height:100vh;
            font-size:var(--font-md);
            line-height:1.5;
            overflow-x:hidden;
            max-width:100vw;
        }
        .cx-layout { display:flex; min-height:calc(100vh - 3.75rem); }

        /* ── Sidebar ── */
        .cx-sidebar {
            width:var(--sidebar-width);
            background:var(--bg-card);
            border-right:1px solid var(--border-color);
            padding:var(--space-lg) 0;
            position:fixed;
            left:0; top:3.75rem; bottom:0;
            overflow-y:auto; overflow-x:hidden;
            transition:transform 0.3s cubic-bezier(0.4,0,0.2,1);
            z-index:100;
            scrollbar-width:thin;
            scrollbar-color:var(--border-color) transparent;
        }
        .cx-sidebar::-webkit-scrollbar { width:0.375rem; }
        .cx-sidebar::-webkit-scrollbar-track { background:transparent; }
        .cx-sidebar::-webkit-scrollbar-thumb { background:var(--border-color); border-radius:0.1875rem; }
        .sidebar-section { padding:var(--space-sm) var(--space-lg); margin-bottom:var(--space-lg); }
        .sidebar-title {
            font-size:0.6875rem; font-weight:600; color:var(--text-secondary);
            text-transform:uppercase; letter-spacing:0.0625rem;
            margin-bottom:var(--space-sm); padding:0 var(--space-sm);
        }
        .sidebar-nav a {
            display:flex; align-items:center; gap:var(--space-md);
            padding:0.75rem var(--space-md); color:var(--text-secondary);
            text-decoration:none; border-radius:0.5rem;
            margin-bottom:var(--space-xs); transition:all 0.2s ease;
            font-size:var(--font-sm);
        }
        .sidebar-nav a:hover { background:var(--bg-secondary); color:var(--text-primary); transform:translateX(0.125rem); }
        .sidebar-nav a.active { background:linear-gradient(135deg,var(--indigo),var(--indigo-dark)); color:#fff; }
        .sidebar-nav svg { width:1.25rem; height:1.25rem; flex-shrink:0; }

        /* ── Main ── */
        .cx-main {
            flex:1; margin-left:var(--sidebar-width); padding:var(--space-lg);
            transition:margin-left 0.3s cubic-bezier(0.4,0,0.2,1);
            min-height:calc(100vh - 3.75rem);
            overflow-x:auto;
            max-width:calc(100vw - var(--sidebar-width));
        }

        /* ── Toggle (mobile) ── */
        .sidebar-toggle {
            position:fixed; bottom:var(--space-lg); right:var(--space-lg);
            width:3.5rem; height:3.5rem; border-radius:50%;
            background:linear-gradient(135deg,var(--indigo),var(--indigo-dark));
            border:none; color:#fff; cursor:pointer; display:none;
            align-items:center; justify-content:center;
            box-shadow:0 0.25rem 0.75rem rgba(99,102,241,0.4);
            z-index:210; transition:transform 0.2s ease;
        }
        .sidebar-toggle svg { width:1.5rem; height:1.5rem; }
        .sidebar-overlay {
            display:none; position:fixed; top:3.75rem; left:0; right:0; bottom:0;
            background:rgba(0,0,0,0.5); z-index:99;
        }
        .sidebar-overlay.active { display:block; }

        /* ── Cards / UI ── */
        .card, .glass-card {
            background:var(--bg-card); border:1px solid var(--border-color);
            border-radius:0.625rem; padding:var(--space-md); transition:all 0.3s ease;
        }
        .card:hover, .glass-card:hover {
            border-color:rgba(99,102,241,0.3);
            box-shadow:0 0.25rem 1.25rem rgba(0,0,0,0.2);
            transform:translateY(-0.125rem);
        }

        /* ── Buttons ── */
        .btn {
            display:inline-flex; align-items:center; justify-content:center;
            gap:0.375rem; padding:0.5rem 1rem; border:none; border-radius:0.5rem;
            font-family:inherit; font-size:var(--font-xs); font-weight:600;
            cursor:pointer; transition:all 0.3s ease; text-decoration:none; white-space:nowrap;
        }
        .btn:disabled { opacity:0.6; cursor:not-allowed; }
        .btn-primary {
            background:linear-gradient(135deg,var(--indigo),var(--indigo-dark)); color:#fff;
        }
        .btn-primary:hover:not(:disabled) { box-shadow:0 0.375rem 1.5rem rgba(99,102,241,0.5); transform:translateY(-0.125rem); }
        .btn-secondary {
            background:var(--bg-secondary); color:var(--text-primary); border:1px solid var(--border-color);
        }
        .btn-secondary:hover:not(:disabled) { transform:translateY(-0.125rem); box-shadow:0 0.375rem 1.25rem rgba(0,0,0,0.15); }
        .btn-danger { background:linear-gradient(135deg,#ff4757,#ff6b6b); color:#fff; }
        .btn-danger:hover:not(:disabled) { box-shadow:0 0.375rem 1.5rem rgba(255,71,87,0.5); transform:translateY(-0.125rem); }
        .btn-sm { padding:0.375rem 0.75rem !important; font-size:var(--font-xs) !important; }
        @media (min-width:48rem) {
            .btn, .btn-primary, .btn-secondary, .btn-danger { padding:0.625rem 1.25rem; font-size:var(--font-sm); border-radius:0.625rem; }
        }

        /* ── Forms ── */
        .form-group { margin-bottom:var(--space-lg); }
        .form-label { display:block; margin-bottom:var(--space-sm); color:var(--text-secondary); font-weight:500; font-size:var(--font-sm); }
        .form-input, .form-select, .form-textarea {
            width:100%; padding:0.75rem 1rem; background:var(--bg-secondary);
            border:1px solid var(--border-color); border-radius:0.5rem;
            color:var(--text-primary); font-family:inherit; font-size:var(--font-sm);
            transition:all 0.3s ease; line-height:1.5;
        }
        .form-input:focus, .form-select:focus, .form-textarea:focus {
            outline:none; border-color:var(--indigo); box-shadow:0 0 0 0.1875rem rgba(99,102,241,0.15);
        }
        .form-actions { display:flex; gap:0.75rem; align-items:center; flex-wrap:wrap; }

        /* ── Utilities ── */
        .alert { padding:0.9375rem 1.25rem; border-radius:0.5rem; margin-bottom:var(--space-lg); font-size:var(--font-sm); }
        .alert-success { background:rgba(0,255,136,0.1); border:1px solid var(--green); color:var(--green); }
        .alert-error   { background:rgba(255,107,107,0.1); border:1px solid #ff6b6b; color:#ff6b6b; }
        .alert-info    { background:rgba(99,102,241,0.1); border:1px solid var(--indigo); color:var(--indigo); }
        .grid { display:grid; gap:var(--space-lg); }
        .grid-2 { grid-template-columns:repeat(2,1fr); }
        .grid-3 { grid-template-columns:repeat(3,1fr); }
        .grid-4 { grid-template-columns:repeat(4,1fr); }
        .stat-card { text-align:center; padding:var(--space-xl); }
        .stat-value {
            font-size:2.5rem; font-weight:700;
            background:linear-gradient(135deg,var(--indigo),var(--cyan));
            -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;
        }
        .stat-label { color:var(--text-secondary); margin-top:var(--space-xs); font-size:var(--font-sm); }
        .section-title { font-size:var(--font-xl); margin-bottom:var(--space-lg); display:flex; align-items:center; gap:var(--space-sm); }
        .back-link { display:inline-flex; align-items:center; gap:var(--space-sm); color:var(--text-secondary); text-decoration:none; margin-bottom:var(--space-lg); font-size:var(--font-sm); transition:color 0.2s; }
        .back-link:hover { color:var(--indigo); }
        .empty-state { text-align:center; padding:3.75rem 1.25rem; }
        .empty-icon { font-size:4rem; color:var(--indigo); margin-bottom:var(--space-lg); opacity:0.7; }
        .badge { display:inline-flex; align-items:center; padding:0.25rem 0.625rem; border-radius:9999px; font-size:var(--font-xs); font-weight:600; }
        .badge-success { background:rgba(0,255,136,0.15); color:var(--green); }
        .badge-info    { background:rgba(99,102,241,0.15); color:var(--indigo); }

        /* ── Responsive ── */
        @media (max-width:64rem) { .grid-3, .grid-4 { grid-template-columns:repeat(2,1fr); } }
        @media (max-width:48rem) {
            .cx-sidebar { transform:translateX(-100%); }
            .cx-sidebar.open { transform:translateX(0); }
            .cx-main { margin-left:0; padding:var(--space-lg) 0.9375rem; max-width:100vw; }
            .sidebar-toggle { display:flex; }
            .grid-2, .grid-3, .grid-4 { grid-template-columns:1fr; }
            .stat-value { font-size:2rem; }
        }
        @media (max-width:30rem) {
            .cx-main { padding:0.9375rem 0.625rem; }
            .btn:not(.btn-sm) { width:100%; justify-content:center; }
            .form-actions { flex-direction:column; width:100%; }
            .form-actions .btn { width:100%; }
        }
    </style>
</head>
<body>
    <?php
    \Core\Timezone::init(\Core\Auth::id());
    include BASE_PATH . '/views/layouts/navbar.php';
    $currentUri = $_SERVER['REQUEST_URI'];
    ?>

    <div class="cx-layout">
        <!-- Sidebar -->
        <aside class="cx-sidebar" id="cxSidebar">
            <div class="sidebar-section">
                <div class="sidebar-title">CardX</div>
                <nav class="sidebar-nav">
                    <a href="/projects/idcard"
                       class="<?= (trim(parse_url($currentUri, PHP_URL_PATH), '/') === 'projects/idcard' || strpos($currentUri, '/projects/idcard/dashboard') !== false) ? 'active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                            <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
                        </svg>
                        Dashboard
                    </a>
                    <a href="/projects/idcard/generate"
                       class="<?= strpos($currentUri, '/projects/idcard/generate') !== false ? 'active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="2" y="5" width="20" height="14" rx="2"/>
                            <circle cx="8" cy="12" r="3"/>
                            <path d="M13 9h6M13 12h4M13 15h6"/>
                        </svg>
                        Generate ID Card
                    </a>
                    <a href="/projects/idcard/history"
                       class="<?= strpos($currentUri, '/projects/idcard/history') !== false ? 'active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                        </svg>
                        My Cards
                    </a>
                </nav>
            </div>

            <div class="sidebar-section">
                <div class="sidebar-title" style="font-size:0.65rem;letter-spacing:0.08em;">BULK GENERATION</div>
                <nav class="sidebar-nav">
                    <a href="/projects/idcard/generate"
                       class="<?= (strpos($currentUri, '/projects/idcard/generate') !== false || (strpos($currentUri, '/projects/idcard/bulk') !== false && strpos($currentUri, '/projects/idcard/bulk/cards') === false)) ? 'active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="18" height="4" rx="1"/>
                            <rect x="3" y="10" width="18" height="4" rx="1"/>
                            <rect x="3" y="17" width="18" height="4" rx="1"/>
                        </svg>
                        Bulk Generate
                    </a>
                    <a href="/projects/idcard/bulk/cards"
                       class="<?= strpos($currentUri, '/projects/idcard/bulk/cards') !== false ? 'active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="2" y="5" width="20" height="14" rx="2"/>
                            <path d="M2 10h20"/>
                        </svg>
                        View Bulk ID Cards
                    </a>
                </nav>
            </div>

        </aside>

        <!-- Overlay (mobile) -->
        <div class="sidebar-overlay" id="cxOverlay"></div>

        <!-- Main content -->
        <main class="cx-main" id="cxMain">
            <?= $content ?? '' ?>
        </main>
    </div>

    <!-- Mobile toggle -->
    <button class="sidebar-toggle" id="cxToggle" aria-label="Toggle sidebar">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="3" y1="12" x2="21" y2="12"/>
            <line x1="3" y1="6"  x2="21" y2="6"/>
            <line x1="3" y1="18" x2="21" y2="18"/>
        </svg>
    </button>

    <script>
    (function(){
        var sidebar = document.getElementById('cxSidebar');
        var overlay = document.getElementById('cxOverlay');
        var toggle  = document.getElementById('cxToggle');
        function open()  { sidebar.classList.add('open');    overlay.classList.add('active'); }
        function close() { sidebar.classList.remove('open'); overlay.classList.remove('active'); }
        if(toggle)  toggle.addEventListener('click', function(){ sidebar.classList.contains('open') ? close() : open(); });
        if(overlay) overlay.addEventListener('click', close);
        sidebar.querySelectorAll('.sidebar-nav a').forEach(function(a){
            a.addEventListener('click', function(){ if(window.innerWidth<=768) close(); });
        });
    })();
    </script>
</body>
</html>
