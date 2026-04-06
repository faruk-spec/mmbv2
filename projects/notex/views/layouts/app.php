<?php use Core\View; use Core\Auth; ?>
<?php
$defaultTheme = 'dark';
try {
    $db = \Core\Database::getInstance();
    $navbarSettings = $db->fetch("SELECT default_theme FROM navbar_settings WHERE id = 1");
    if ($navbarSettings && !empty($navbarSettings['default_theme'])) {
        $defaultTheme = $navbarSettings['default_theme'];
    }
} catch (\Exception $e) {}

$uiVersion = '20260406000000';
header("Cache-Control: no-cache, no-store, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?= htmlspecialchars($defaultTheme) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta name="csrf-token" content="<?= \Core\Security::generateCsrfToken() ?>">
    <title><?= View::e($title ?? 'NoteX') ?> - MyMultiBranch</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
          integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw=="
          crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/universal-theme.css?v=<?= $uiVersion ?>">

    <style>
        :root {
            --bg-primary: #06060a;
            --bg-secondary: #0c0c12;
            --bg-card: #0f0f18;
            --nx-accent: #f59e0b;
            --nx-accent-dark: #d97706;
            --accent: #f59e0b;
            --accent2: #ff2ec4;
            --cyan: #00f0ff;
            --magenta: #ff2ec4;
            --green: #00ff88;
            --red: #ff6b6b;
            --text-primary: #e8eefc;
            --text-secondary: #8892a6;
            --border-color: rgba(255, 255, 255, 0.1);
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
            --border-color: rgba(0, 0, 0, 0.1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; -webkit-font-smoothing: antialiased; max-width: 100vw; }
        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            font-size: var(--font-md);
            line-height: 1.5;
            overflow-x: hidden;
            max-width: 100vw;
        }

        .nx-dashboard { display: flex; min-height: calc(100vh - 3.75rem); }

        /* Sidebar */
        .nx-sidebar {
            width: var(--sidebar-width);
            background: var(--bg-card);
            border-right: 1px solid var(--border-color);
            padding: var(--space-lg) 0;
            position: fixed;
            left: 0; top: 3.75rem; bottom: 0;
            overflow-y: auto; overflow-x: hidden;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 100;
            will-change: transform;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
            scrollbar-color: var(--border-color) transparent;
        }
        .nx-sidebar::-webkit-scrollbar { width: 0.375rem; }
        .nx-sidebar::-webkit-scrollbar-track { background: transparent; }
        .nx-sidebar::-webkit-scrollbar-thumb { background: var(--border-color); border-radius: 0.1875rem; }

        .sidebar-section { padding: var(--space-sm) var(--space-lg); margin-bottom: var(--space-lg); }
        .sidebar-title {
            font-size: 0.6875rem; font-weight: 600; color: var(--text-secondary);
            text-transform: uppercase; letter-spacing: 0.0625rem;
            margin-bottom: var(--space-sm); padding: 0 var(--space-sm);
        }
        .sidebar-nav a {
            display: flex; align-items: center; gap: var(--space-md);
            padding: 0.75rem var(--space-md); color: var(--text-secondary);
            text-decoration: none; border-radius: 0.5rem;
            margin-bottom: var(--space-xs); transition: all 0.2s ease;
            font-size: var(--font-sm);
        }
        .sidebar-nav a:hover { background: var(--bg-secondary); color: var(--text-primary); transform: translateX(0.125rem); }
        .sidebar-nav a.active { background: linear-gradient(135deg, var(--nx-accent), var(--nx-accent-dark)); color: white; }
        .sidebar-nav svg { width: 1.25rem; height: 1.25rem; flex-shrink: 0; }

        /* Main Content */
        .nx-main {
            flex: 1; margin-left: var(--sidebar-width); padding: var(--space-lg);
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            min-height: calc(100vh - 3.75rem);
            overflow-x: auto;
            max-width: calc(100vw - var(--sidebar-width));
        }

        /* Mobile Toggle */
        .sidebar-toggle {
            position: fixed; bottom: var(--space-lg); right: var(--space-lg);
            width: 3.5rem; height: 3.5rem; border-radius: 50%;
            background: linear-gradient(135deg, var(--nx-accent), var(--nx-accent-dark));
            border: none; color: white; cursor: pointer; display: none;
            align-items: center; justify-content: center;
            box-shadow: 0 0.25rem 0.75rem rgba(245, 158, 11, 0.4);
            z-index: 101; transition: transform 0.2s ease; will-change: transform;
        }
        .sidebar-toggle:active { transform: scale(0.95); }
        .sidebar-toggle svg { width: 1.5rem; height: 1.5rem; }

        .sidebar-overlay {
            display: none; position: fixed; top: 3.75rem; left: 0; right: 0; bottom: 0;
            background: rgba(0, 0, 0, 0.5); z-index: 99;
        }
        .sidebar-overlay.active { display: block; }

        /* Cards */
        .card, .glass-card {
            background: var(--bg-card); border: 1px solid var(--border-color);
            border-radius: 0.625rem; padding: var(--space-md); transition: all 0.3s ease;
        }
        .card:hover, .glass-card:hover {
            border-color: rgba(245, 158, 11, 0.3);
            box-shadow: 0 0.25rem 1.25rem rgba(0, 0, 0, 0.2);
            transform: translateY(-0.125rem);
        }

        /* Note card grid */
        .notes-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(16rem, 1fr)); gap: var(--space-lg); }
        .note-card {
            background: var(--bg-card); border: 1px solid var(--border-color);
            border-radius: 0.625rem; padding: var(--space-lg); transition: all 0.2s;
            position: relative; cursor: pointer;
        }
        .note-card:hover { border-color: rgba(245, 158, 11, 0.3); transform: translateY(-0.125rem); }
        .note-card-accent { position: absolute; top: 0; left: 0; width: 0.25rem; height: 100%; border-radius: 0.625rem 0 0 0.625rem; }
        .note-card-title { font-weight: 600; margin-bottom: var(--space-sm); font-size: var(--font-md); display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .note-card-preview { color: var(--text-secondary); font-size: var(--font-sm); display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; margin-bottom: var(--space-md); }
        .note-card-meta { display: flex; align-items: center; justify-content: space-between; font-size: var(--font-xs); color: var(--text-secondary); }
        .note-card-actions { display: flex; gap: var(--space-sm); }

        /* Buttons */
        .btn {
            display: inline-flex; align-items: center; justify-content: center;
            gap: 0.375rem; padding: 0.5rem 1rem; border: none; border-radius: 0.5rem;
            font-family: inherit; font-size: var(--font-xs); font-weight: 600;
            cursor: pointer; transition: all 0.3s ease; text-decoration: none;
            white-space: nowrap; will-change: transform;
        }
        .btn:active { transform: translateY(0); }
        .btn:disabled { opacity: 0.6; cursor: not-allowed; }

        .btn-primary {
            background: linear-gradient(135deg, var(--nx-accent), var(--nx-accent-dark)); color: white;
            display: inline-flex; align-items: center; justify-content: center;
            gap: 0.375rem; padding: 0.5rem 1rem; border: none; border-radius: 0.5rem;
            font-family: inherit; font-size: var(--font-xs); font-weight: 600;
            cursor: pointer; transition: all 0.3s ease; text-decoration: none; white-space: nowrap;
        }
        .btn-primary:hover:not(:disabled) { box-shadow: 0 0.375rem 1.5rem rgba(245, 158, 11, 0.5); transform: translateY(-0.125rem); }

        .btn-secondary {
            background: var(--bg-secondary); color: var(--text-primary); border: 1px solid var(--border-color);
            display: inline-flex; align-items: center; justify-content: center;
            gap: 0.375rem; padding: 0.5rem 1rem; border-radius: 0.5rem;
            font-family: inherit; font-size: var(--font-xs); font-weight: 600;
            cursor: pointer; transition: all 0.3s ease; text-decoration: none; white-space: nowrap;
        }
        .btn-secondary:hover:not(:disabled) { transform: translateY(-0.125rem); box-shadow: 0 0.375rem 1.25rem rgba(0, 0, 0, 0.15); }

        .btn-danger {
            background: linear-gradient(135deg, #ff4757, #ff6b6b); color: white;
            display: inline-flex; align-items: center; justify-content: center;
            gap: 0.375rem; padding: 0.5rem 1rem; border: none; border-radius: 0.5rem;
            font-family: inherit; font-size: var(--font-xs); font-weight: 600;
            cursor: pointer; transition: all 0.3s ease; text-decoration: none; white-space: nowrap;
        }
        .btn-danger:hover:not(:disabled) { box-shadow: 0 0.375rem 1.5rem rgba(255, 71, 87, 0.5); transform: translateY(-0.125rem); }

        @media (min-width: 48rem) {
            .btn, .btn-primary, .btn-secondary, .btn-danger {
                padding: 0.625rem 1.25rem; font-size: var(--font-sm); border-radius: 0.625rem;
            }
        }
        .btn-sm { padding: 0.375rem 0.75rem !important; font-size: var(--font-xs) !important; }
        .btn-icon { padding: 0.4375rem !important; border-radius: 0.4375rem !important; width: 2rem; height: 2rem; justify-content: center; }

        /* Forms */
        .form-group { margin-bottom: var(--space-lg); }
        .form-label { display: block; margin-bottom: var(--space-sm); color: var(--text-secondary); font-weight: 500; font-size: var(--font-sm); }
        .form-input, .form-select, .form-textarea, .form-control {
            width: 100%; padding: 0.75rem 1rem; background: var(--bg-secondary);
            border: 1px solid var(--border-color); border-radius: 0.5rem;
            color: var(--text-primary); font-family: inherit; font-size: var(--font-sm);
            transition: all 0.3s ease; line-height: 1.5;
        }
        .form-input:focus, .form-select:focus, .form-textarea:focus, .form-control:focus {
            outline: none; border-color: var(--nx-accent); box-shadow: 0 0 0 0.1875rem rgba(245, 158, 11, 0.1);
        }
        textarea.form-input, textarea.form-control { resize: vertical; min-height: 18.75rem; line-height: 1.7; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-lg); }
        .form-actions { display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap; }

        /* Alerts */
        .alert { padding: 0.9375rem 1.25rem; border-radius: 0.5rem; margin-bottom: var(--space-lg); font-size: var(--font-sm); }
        .alert-success { background: rgba(0, 255, 136, 0.1); border: 1px solid var(--green); color: var(--green); }
        .alert-error { background: rgba(255, 107, 107, 0.1); border: 1px solid var(--red); color: var(--red); }
        .alert-info { background: rgba(0, 240, 255, 0.1); border: 1px solid var(--cyan); color: var(--cyan); }

        /* Grid */
        .grid { display: grid; gap: var(--space-lg); }
        .grid-2 { grid-template-columns: repeat(2, 1fr); }
        .grid-3 { grid-template-columns: repeat(3, 1fr); }
        .grid-4 { grid-template-columns: repeat(4, 1fr); }

        /* Stats */
        .stat-card { text-align: center; padding: var(--space-xl); }
        .stat-value {
            font-size: 2.5rem; font-weight: 700;
            background: linear-gradient(135deg, var(--nx-accent), var(--cyan));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
        }
        .stat-label { color: var(--text-secondary); margin-top: var(--space-xs); font-size: var(--font-sm); }

        /* Badges */
        .badge { display: inline-block; padding: 0.1875rem 0.625rem; border-radius: 1.25rem; font-size: var(--font-xs); font-weight: 600; }
        .badge-success { background: rgba(0, 255, 136, 0.15); color: var(--green); }
        .badge-danger { background: rgba(255, 107, 107, 0.15); color: var(--red); }
        .badge-warning { background: rgba(245, 158, 11, 0.15); color: var(--nx-accent); }
        .badge-info { background: rgba(0, 240, 255, 0.15); color: var(--cyan); }
        .badge-pin { background: rgba(245, 158, 11, 0.15); color: var(--nx-accent); }

        /* Section header */
        .section-title { font-size: var(--font-xl); margin-bottom: var(--space-lg); display: flex; align-items: center; gap: var(--space-sm); }
        .back-link {
            display: inline-flex; align-items: center; gap: var(--space-sm);
            color: var(--text-secondary); text-decoration: none; margin-bottom: var(--space-lg);
            transition: color 0.2s; font-size: var(--font-sm);
        }
        .back-link:hover { color: var(--nx-accent); }
        .empty-state { text-align: center; padding: 3.75rem 1.25rem; }
        .empty-icon { font-size: 4rem; color: var(--nx-accent); margin-bottom: var(--space-lg); opacity: 0.7; }

        /* Table */
        .table-container { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; font-size: var(--font-sm); }
        th { text-align: left; padding: 0.75rem 0.875rem; color: var(--text-secondary); font-size: var(--font-xs); text-transform: uppercase; font-weight: 600; border-bottom: 1px solid var(--border-color); }
        td { padding: 0.75rem 0.875rem; border-bottom: 1px solid rgba(255, 255, 255, 0.04); vertical-align: middle; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: rgba(255, 255, 255, 0.02); }

        /* Responsive */
        @media (max-width: 64rem) { .grid-3, .grid-4 { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 48rem) {
            .nx-sidebar { transform: translateX(-100%); }
            .nx-sidebar.open { transform: translateX(0); }
            .nx-main { margin-left: 0; padding: var(--space-lg) 0.9375rem; overflow-x: auto !important; max-width: 100vw; }
            .sidebar-toggle { display: flex; }
            .grid-2, .grid-3, .grid-4 { grid-template-columns: 1fr; }
            .notes-grid { grid-template-columns: 1fr; }
            .stat-value { font-size: 2rem; }
            .card, .glass-card { padding: var(--space-lg); }
            .section-title { font-size: var(--font-lg); }
            .form-row { grid-template-columns: 1fr; }
        }
        @media (max-width: 30rem) {
            .nx-main { padding: 0.9375rem 0.625rem; }
            .btn:not(.btn-sm):not(.btn-icon) { width: 100%; justify-content: center; padding: 0.75rem 1rem; }
            .stat-value { font-size: 1.8rem; }
            .form-actions { flex-direction: column; width: 100%; }
            .form-actions .btn { width: 100%; }
        }
    </style>
</head>
<body>
    <?php
    \Core\Timezone::init(\Core\Auth::id());
    include BASE_PATH . '/views/layouts/navbar.php';
    $currentUri = $_SERVER['REQUEST_URI'];
    ?>

    <div class="nx-dashboard">
        <!-- Sidebar -->
        <aside class="nx-sidebar" id="nxSidebar">
            <div class="sidebar-section">
                <div class="sidebar-title">Main</div>
                <nav class="sidebar-nav">
                    <a href="/projects/notex" class="<?= ($currentUri === '/projects/notex' || $currentUri === '/projects/notex/' || strpos($currentUri, '/projects/notex/dashboard') !== false) ? 'active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                            <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
                        </svg>
                        Dashboard
                    </a>
                    <a href="/projects/notex/notes" class="<?= strpos($currentUri, '/projects/notex/notes') !== false ? 'active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>
                            <polyline points="10 9 9 9 8 9"/>
                        </svg>
                        All Notes
                    </a>
                    <a href="/projects/notex/create" class="<?= strpos($currentUri, '/projects/notex/create') !== false ? 'active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="8" x2="12" y2="16"/>
                            <line x1="8" y1="12" x2="16" y2="12"/>
                        </svg>
                        New Note
                    </a>
                </nav>
            </div>

            <div class="sidebar-section">
                <div class="sidebar-title">Organise</div>
                <nav class="sidebar-nav">
                    <a href="/projects/notex/folders" class="<?= strpos($currentUri, '/projects/notex/folders') !== false ? 'active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
                        </svg>
                        Folders
                    </a>
                    <a href="/projects/notex/notes?pinned=1">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="12" y1="17" x2="12" y2="22"/>
                            <path d="M5 17h14v-1.76a2 2 0 0 0-1.11-1.79l-1.78-.9A2 2 0 0 1 15 10.76V6h1a2 2 0 0 0 0-4H8a2 2 0 0 0 0 4h1v4.76a2 2 0 0 1-1.11 1.79l-1.78.9A2 2 0 0 0 5 15.24z"/>
                        </svg>
                        Pinned
                    </a>
                </nav>
            </div>

            <div class="sidebar-section">
                <div class="sidebar-title">Settings</div>
                <nav class="sidebar-nav">
                    <a href="/projects/notex/settings" class="<?= strpos($currentUri, '/projects/notex/settings') !== false ? 'active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="3"/>
                            <path d="M12 1v6m0 6v6M5.64 5.64l4.24 4.24m4.24 4.24l4.24 4.24M1 12h6m6 0h6M5.64 18.36l4.24-4.24m4.24-4.24l4.24-4.24"/>
                        </svg>
                        Settings
                    </a>
                </nav>
            </div>
        </aside>

        <!-- Sidebar overlay (mobile) -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <!-- Main Content -->
        <main class="nx-main" id="nxMain">
            <?php if (!empty($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= View::e($_SESSION['success']) ?></div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            <?php if (!empty($_SESSION['error'])): ?>
                <div class="alert alert-error"><?= View::e($_SESSION['error']) ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            <?php if (!empty($_SESSION['share_url'])): ?>
                <div class="alert alert-info">
                    Share link: <a href="<?= View::e($_SESSION['share_url']) ?>" target="_blank" style="color:var(--cyan);"><?= View::e($_SESSION['share_url']) ?></a>
                </div>
                <?php unset($_SESSION['share_url']); ?>
            <?php endif; ?>

            <?php View::yield('content'); ?>
        </main>
    </div>

    <!-- Mobile sidebar toggle -->
    <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="3" y1="12" x2="21" y2="12"/>
            <line x1="3" y1="6" x2="21" y2="6"/>
            <line x1="3" y1="18" x2="21" y2="18"/>
        </svg>
    </button>

    <script>
    (function() {
        const sidebar  = document.getElementById('nxSidebar');
        const overlay  = document.getElementById('sidebarOverlay');
        const toggle   = document.getElementById('sidebarToggle');

        function openSidebar()  { sidebar.classList.add('open');    overlay.classList.add('active'); }
        function closeSidebar() { sidebar.classList.remove('open'); overlay.classList.remove('active'); }
        function toggleSidebar(){ sidebar.classList.contains('open') ? closeSidebar() : openSidebar(); }

        if (toggle)  toggle.addEventListener('click', toggleSidebar);
        if (overlay) overlay.addEventListener('click', closeSidebar);

        sidebar.querySelectorAll('.sidebar-nav a').forEach(link => {
            link.addEventListener('click', () => { if (window.innerWidth <= 768) closeSidebar(); });
        });

        let resizeTimer;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => { if (window.innerWidth > 768) closeSidebar(); }, 250);
        });
    })();
    </script>
</body>
</html>
