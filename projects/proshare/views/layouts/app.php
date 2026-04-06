<?php
use Core\View;
use Core\Auth;
use Core\Security;

// Dynamic theme from DB (same as ConvertX/BillX pattern)
$defaultTheme = 'dark';
$allowedThemes = ['dark', 'light'];
try {
    $db = \Core\Database::getInstance();
    $navbarSettings = $db->fetch("SELECT default_theme FROM navbar_settings WHERE id = 1");
    if ($navbarSettings && !empty($navbarSettings['default_theme'])
        && in_array($navbarSettings['default_theme'], $allowedThemes, true)) {
        $defaultTheme = $navbarSettings['default_theme'];
    }
} catch (\Exception $e) { /* fall through */ }

$uiVersion = '20260406000000';
$_uri = $_SERVER['REQUEST_URI'] ?? '';
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?= htmlspecialchars($defaultTheme) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="csrf-token" content="<?= Security::generateCsrfToken() ?>">
    <title><?= View::e($title ?? 'ProShare') ?> – ProShare</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
          integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw=="
          crossorigin="anonymous" referrerpolicy="no-referrer">

    <!-- Universal theme (loaded first) -->
    <link rel="stylesheet" href="/css/universal-theme.css?v=<?= $uiVersion ?>">

    <style>
        /* ══════════════════════════════════════════════════════
           ProShare CSS Variables & Brand
        ══════════════════════════════════════════════════════ */
        :root {
            --ps-primary:   #ffaa00;
            --ps-secondary: #ff2ec4;
            --ps-accent:    #00f0ff;
            --ps-success:   #00ff88;
            --ps-warning:   #ffaa00;
            --ps-danger:    #ff6b6b;

            /* Map onto platform vars */
            --orange:  #ffaa00;
            --magenta: #ff2ec4;
            --cyan:    #00f0ff;
            --green:   #00ff88;

            /* Dark mode structural */
            --bg-primary:    #06060a;
            --bg-secondary:  #0c0c12;
            --bg-card:       #0f0f18;
            --bg-tertiary:   #13131f;
            --text-primary:  #e8eefc;
            --text-secondary: #8892a6;
            --text-muted:    #8892a6;
            --border-color:  rgba(255,255,255,0.08);

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
            --bg-primary:    #f4f6fb;
            --bg-secondary:  #ffffff;
            --bg-card:       #ffffff;
            --bg-tertiary:   #eef1f8;
            --text-primary:  #1a2035;
            --text-secondary: #5a6379;
            --text-muted:    #8892a6;
            --border-color:  rgba(0,0,0,0.1);
            --orange:  #e07b00;
            --magenta: #b0107a;
            --cyan:    #0369a1;
            --green:   #16a34a;
        }

        /* ── Reset ── */
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            line-height: 1.6;
        }

        /* ── Dashboard wrapper ── */
        .ps-dashboard {
            display: flex;
            min-height: 100vh;
            flex-direction: column;
        }

        /* ── Sidebar ── */
        .ps-sidebar {
            position: fixed;
            left: 0;
            top: var(--navbar-height);
            width: var(--sidebar-width);
            height: calc(100vh - var(--navbar-height));
            background: var(--bg-card);
            border-right: 1px solid var(--border-color);
            overflow-y: auto;
            z-index: 200;
            display: flex;
            flex-direction: column;
            transition: transform 0.25s ease;
        }
        .ps-sidebar::-webkit-scrollbar { width: 0.25rem; }
        .ps-sidebar::-webkit-scrollbar-track { background: transparent; }
        .ps-sidebar::-webkit-scrollbar-thumb { background: var(--border-color); border-radius: 0.125rem; }

        .ps-sidebar-logo {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            padding: 1rem 1.125rem 1rem;
            border-bottom: 1px solid var(--border-color);
            text-decoration: none;
        }
        .ps-sidebar-logo .logo-icon {
            width: 2rem; height: 2rem;
            border-radius: 0.5rem;
            background: linear-gradient(135deg, var(--ps-primary), var(--ps-secondary));
            display: flex; align-items: center; justify-content: center;
            font-weight: 800; color: #06060a; font-size: 0.875rem; flex-shrink: 0;
        }
        .ps-sidebar-logo .logo-text {
            font-size: 1rem; font-weight: 800;
            background: linear-gradient(135deg, var(--ps-primary), var(--ps-secondary));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
        }

        /* Sidebar sections & nav */
        .sidebar-section { padding: 0.625rem 0; }
        .sidebar-title {
            padding: 0.375rem 1rem 0.25rem;
            font-size: 0.7rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.08em; color: var(--text-secondary); opacity: 0.6;
        }
        .sidebar-nav a {
            display: flex; align-items: center; gap: 0.625rem;
            padding: 0.5rem 1rem;
            color: var(--text-secondary); text-decoration: none;
            font-size: 0.875rem; font-weight: 500;
            border-radius: 0; transition: background 0.15s, color 0.15s;
            position: relative;
        }
        .sidebar-nav a:hover {
            background: rgba(255,170,0,0.06);
            color: var(--text-primary);
            text-decoration: none;
        }
        .sidebar-nav a.active {
            background: rgba(255,170,0,0.1);
            color: var(--ps-primary);
        }
        .sidebar-nav a.active::after {
            content: '';
            position: absolute;
            right: 0; top: 50%;
            transform: translateY(-50%);
            width: 0.1875rem; height: 60%;
            background: var(--ps-primary);
            border-radius: 0.1875rem 0 0 0.1875rem;
        }
        .sidebar-nav a i { width: 1rem; text-align: center; flex-shrink: 0; font-size: 0.875rem; }
        .sidebar-nav a .badge {
            margin-left: auto;
            background: rgba(255,170,0,0.15); color: var(--ps-primary);
            border-radius: 1rem; padding: 0.0625rem 0.5rem;
            font-size: 0.7rem; font-weight: 700;
        }

        /* Sidebar overlay (mobile) */
        .ps-sidebar-overlay {
            display: none;
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 190;
        }
        .ps-sidebar-overlay.active { display: block; }

        /* Floating toggle (mobile) */
        .ps-sidebar-toggle {
            display: none;
            position: fixed; bottom: 1.5rem; right: 1.25rem;
            z-index: 210;
            width: 3rem; height: 3rem;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--ps-primary), var(--ps-secondary));
            border: none; cursor: pointer;
            align-items: center; justify-content: center;
            box-shadow: 0 0.25rem 1.125rem rgba(255,170,0,0.4);
            color: #06060a; font-size: 1.1rem;
        }

        /* ── Main content ── */
        .ps-main {
            margin-left: var(--sidebar-width);
            margin-top: var(--navbar-height);
            flex: 1;
            min-height: calc(100vh - var(--navbar-height));
            padding: 1.75rem;
        }

        /* ── Alerts ── */
        .alert {
            display: flex; align-items: center; gap: 0.625rem;
            padding: 0.875rem 1rem;
            border-radius: 0.625rem;
            font-size: 0.875rem;
            margin-bottom: 1.25rem;
        }
        .alert-success { background: rgba(0,255,136,0.08); border: 1px solid rgba(0,255,136,0.3); color: var(--green); }
        .alert-error   { background: rgba(255,107,107,0.08); border: 1px solid rgba(255,107,107,0.3); color: var(--ps-danger); }
        .alert-warning { background: rgba(255,170,0,0.08); border: 1px solid rgba(255,170,0,0.3); color: var(--ps-warning); }

        /* ── Shared component classes ── */
        .ps-page-header { margin-bottom: 1.5rem; }
        .ps-page-title { font-size: 1.5rem; font-weight: 800; margin-bottom: 0.25rem; }
        .ps-page-subtitle { font-size: 0.875rem; color: var(--text-secondary); }

        .ps-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 0.875rem;
            padding: 1.25rem;
            margin-bottom: 1.25rem;
        }
        .ps-card-title {
            font-size: 0.875rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.06em;
            color: var(--text-secondary);
            margin-bottom: 1rem;
            display: flex; align-items: center; gap: 0.5rem;
        }

        .ps-btn {
            display: inline-flex; align-items: center; gap: 0.5rem;
            padding: 0.5625rem 1.125rem;
            border-radius: 0.5rem; font-size: 0.875rem; font-weight: 600;
            cursor: pointer; text-decoration: none;
            border: 1px solid transparent; transition: all 0.15s; line-height: 1;
        }
        .ps-btn-primary {
            background: linear-gradient(135deg, var(--ps-primary), var(--ps-secondary));
            color: #06060a; border: none;
            box-shadow: 0 0.1875rem 0.875rem rgba(255,170,0,0.3);
        }
        .ps-btn-primary:hover { transform: translateY(-1px); box-shadow: 0 0.375rem 1.375rem rgba(255,170,0,0.45); color: #06060a; text-decoration: none; }
        .ps-btn-secondary {
            background: var(--bg-secondary); border-color: var(--border-color); color: var(--text-secondary);
        }
        .ps-btn-secondary:hover { background: rgba(255,170,0,0.07); border-color: rgba(255,170,0,0.25); color: var(--text-primary); text-decoration: none; }
        .ps-btn-danger { background: rgba(255,107,107,0.08); border-color: rgba(255,107,107,0.2); color: var(--ps-danger); }
        .ps-btn-danger:hover { background: rgba(255,107,107,0.18); text-decoration: none; color: var(--ps-danger); }

        /* Grid helpers */
        .ps-grid { display: grid; gap: 1rem; }
        .ps-grid-2 { grid-template-columns: repeat(2, 1fr); }
        .ps-grid-3 { grid-template-columns: repeat(3, 1fr); }
        .ps-grid-4 { grid-template-columns: repeat(4, 1fr); }
        @media (max-width: 1024px) { .ps-grid-4 { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 768px)  { .ps-grid-2, .ps-grid-3, .ps-grid-4 { grid-template-columns: 1fr; } }

        /* Legacy class aliases for existing views */
        .card       { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 0.875rem; padding: 1.25rem; margin-bottom: 1.25rem; }
        .card-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem; padding-bottom: 0.75rem; border-bottom: 1px solid var(--border-color); }
        .card-title { font-size: 0.875rem; font-weight: 700; display: flex; align-items: center; gap: 0.5rem; }
        .btn        { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; border-radius: 0.5rem; font-size: 0.875rem; font-weight: 600; cursor: pointer; text-decoration: none; border: 1px solid transparent; transition: all 0.15s; line-height: 1; font-family: inherit; }
        .btn-primary { background: linear-gradient(135deg, var(--ps-primary), var(--ps-secondary)); color: #06060a; border: none; }
        .btn-primary:hover { transform: translateY(-1px); color: #06060a; text-decoration: none; }
        .btn-secondary { background: var(--bg-secondary); border-color: var(--border-color); color: var(--text-secondary); }
        .btn-secondary:hover { color: var(--text-primary); text-decoration: none; }
        .btn-danger { background: rgba(255,107,107,0.08); border-color: rgba(255,107,107,0.2); color: var(--ps-danger); }
        .btn-success { background: rgba(0,255,136,0.1); border-color: rgba(0,255,136,0.3); color: var(--green); }
        .btn-sm { padding: 0.375rem 0.75rem; font-size: 0.8rem; }
        .grid { display: grid; gap: 1rem; }
        .grid-2 { grid-template-columns: repeat(2, 1fr); }
        .grid-4 { grid-template-columns: repeat(4, 1fr); }
        .mb-3 { margin-bottom: 1rem; }
        .form-group { margin-bottom: 1.25rem; }
        .form-label { display: block; margin-bottom: 0.375rem; color: var(--text-secondary); font-size: 0.875rem; font-weight: 500; }
        .form-input { width: 100%; padding: 0.625rem 0.875rem; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 0.5rem; color: var(--text-primary); font-family: inherit; font-size: 0.875rem; transition: border-color 0.15s; }
        .form-input:focus { outline: none; border-color: var(--ps-primary); }
        .form-textarea { width: 100%; padding: 0.625rem 0.875rem; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 0.5rem; color: var(--text-primary); font-family: inherit; font-size: 0.875rem; resize: vertical; min-height: 6rem; }
        .table-container { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; font-size: 0.875rem; }
        table th { padding: 0.75rem 1rem; text-align: left; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-secondary); border-bottom: 1px solid var(--border-color); background: var(--bg-secondary); }
        table td { padding: 0.875rem 1rem; border-bottom: 1px solid var(--border-color); vertical-align: middle; }
        table tr:last-child td { border-bottom: none; }
        table tr:hover td { background: rgba(255,170,0,0.02); }
        .badge { display: inline-flex; align-items: center; padding: 0.125rem 0.5rem; border-radius: 1rem; font-size: 0.7rem; font-weight: 700; }
        .badge-success { background: rgba(0,255,136,0.12); color: var(--green); }
        .badge-warning { background: rgba(255,170,0,0.12); color: var(--ps-warning); }
        .badge-danger  { background: rgba(255,107,107,0.12); color: var(--ps-danger); }
        .badge-secondary { background: rgba(136,146,166,0.12); color: var(--text-secondary); }
        .stat-card { text-align: center; padding: 1.5rem; }
        .stat-value { font-size: 2rem; font-weight: 700; color: var(--ps-primary); }
        .stat-label { color: var(--text-secondary); margin-top: 0.25rem; font-size: 0.85rem; }
        .upload-zone { border: 2px dashed var(--border-color); border-radius: 0.75rem; padding: 3.5rem 2rem; text-align: center; cursor: pointer; transition: all 0.2s; }
        .upload-zone:hover { border-color: var(--ps-primary); background: rgba(255,170,0,0.04); }
        .file-item { display: flex; align-items: center; gap: 0.875rem; padding: 0.875rem; background: var(--bg-secondary); border-radius: 0.5rem; margin-bottom: 0.5rem; }
        .file-icon { width: 2.75rem; height: 2.75rem; background: rgba(255,170,0,0.1); border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .file-info { flex: 1; min-width: 0; }
        .file-name { font-weight: 500; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .file-meta { font-size: 0.78rem; color: var(--text-secondary); }
        .back-link { display: inline-flex; align-items: center; gap: 0.5rem; color: var(--text-secondary); text-decoration: none; margin-bottom: 1rem; font-size: 0.875rem; }
        .back-link:hover { color: var(--ps-primary); }
        .empty-state { text-align: center; padding: 3.5rem 1.25rem; color: var(--text-secondary); }
        .empty-state i { font-size: 3rem; opacity: 0.2; display: block; margin-bottom: 1rem; }
        .text-center { text-align: center; }
        .text-muted   { color: var(--text-secondary); }
        .text-success { color: var(--green); }
        .text-danger  { color: var(--ps-danger); }
        .text-warning { color: var(--ps-warning); }
        .d-flex { display: flex; }
        .align-items-center { align-items: center; }
        .justify-content-between { justify-content: space-between; }
        .gap-2 { gap: 0.5rem; }
        .gap-3 { gap: 0.75rem; }
        .mt-2 { margin-top: 0.5rem; }
        .mt-3 { margin-top: 0.75rem; }
        .ms-auto { margin-left: auto; }

        /* Responsive */
        @media (max-width: 1024px) {
            .grid-4 { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 900px) {
            .ps-sidebar {
                transform: translateX(calc(-1 * var(--sidebar-width)));
            }
            .ps-sidebar.open {
                transform: translateX(0);
            }
            .ps-sidebar-toggle { display: flex; }
            .ps-main { margin-left: 0; }
        }
        @media (max-width: 768px) {
            .grid-2, .grid-3, .grid-4 { grid-template-columns: 1fr; }
            .ps-main { padding: 1rem; }
        }
    </style>

    <?php View::yield('styles'); ?>
</head>
<body>
<?php
// Include platform navbar
\Core\Timezone::init(\Core\Auth::id());
include BASE_PATH . '/views/layouts/navbar.php';
?>

<div class="ps-dashboard">
    <!-- ── Sidebar ── -->
    <aside class="ps-sidebar" id="psSidebar">
        <a href="/projects/proshare" class="ps-sidebar-logo" style="text-decoration:none;">
            <div class="logo-icon"><i class="fas fa-share-alt" style="-webkit-text-fill-color:#06060a;"></i></div>
            <div class="logo-text">ProShare</div>
        </a>

        <div class="sidebar-section">
            <div class="sidebar-title">Main</div>
            <nav class="sidebar-nav">
                <a href="/projects/proshare/dashboard"
                   class="<?= (trim(parse_url($_uri, PHP_URL_PATH), '/') === 'projects/proshare' || trim(parse_url($_uri, PHP_URL_PATH), '/') === 'projects/proshare/dashboard') ? 'active' : '' ?>">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </nav>
        </div>

        <div class="sidebar-section">
            <div class="sidebar-title">Sharing</div>
            <nav class="sidebar-nav">
                <a href="/projects/proshare/upload"
                   class="<?= strpos($_uri, '/projects/proshare/upload') === 0 ? 'active' : '' ?>">
                    <i class="fas fa-cloud-upload-alt"></i> Upload Files
                </a>
                <a href="/projects/proshare/text"
                   class="<?= strpos($_uri, '/projects/proshare/text') === 0 ? 'active' : '' ?>">
                    <i class="fas fa-file-alt"></i> Share Text
                </a>
                <a href="/projects/proshare/files"
                   class="<?= strpos($_uri, '/projects/proshare/files') === 0 ? 'active' : '' ?>">
                    <i class="fas fa-folder"></i> My Files
                </a>
            </nav>
        </div>

        <div class="sidebar-section">
            <div class="sidebar-title">Account</div>
            <nav class="sidebar-nav">
                <a href="/projects/proshare/notifications"
                   class="<?= strpos($_uri, '/projects/proshare/notifications') === 0 ? 'active' : '' ?>">
                    <i class="fas fa-bell"></i> Notifications
                    <?php if (!empty($unreadNotifications) && $unreadNotifications > 0): ?>
                    <span class="badge"><?= (int)$unreadNotifications ?></span>
                    <?php endif; ?>
                </a>
                <a href="/projects/proshare/settings"
                   class="<?= strpos($_uri, '/projects/proshare/settings') === 0 ? 'active' : '' ?>">
                    <i class="fas fa-cog"></i> Settings
                </a>
            </nav>
        </div>
    </aside>

    <!-- Overlay for mobile -->
    <div class="ps-sidebar-overlay" id="psOverlay"></div>

    <!-- ── Main content ── -->
    <main class="ps-main" id="psMain">
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

        <?php View::yield('content'); ?>
    </main>

    <!-- Floating mobile sidebar toggle -->
    <button class="ps-sidebar-toggle" id="psToggle" aria-label="Toggle navigation">
        <i class="fa-solid fa-bars"></i>
    </button>
</div>

<script>
(function () {
    var sidebar = document.getElementById('psSidebar');
    var overlay = document.getElementById('psOverlay');
    var toggle  = document.getElementById('psToggle');

    function openSidebar()  { sidebar.classList.add('open'); overlay.classList.add('active'); toggle.innerHTML = '<i class="fa-solid fa-times"></i>'; }
    function closeSidebar() { sidebar.classList.remove('open'); overlay.classList.remove('active'); toggle.innerHTML = '<i class="fa-solid fa-bars"></i>'; }

    if (toggle)  toggle.addEventListener('click', function () { sidebar.classList.contains('open') ? closeSidebar() : openSidebar(); });
    if (overlay) overlay.addEventListener('click', closeSidebar);

    // Close on link click (mobile)
    sidebar && sidebar.querySelectorAll('.sidebar-nav a').forEach(function (a) {
        a.addEventListener('click', function () { if (window.innerWidth <= 900) closeSidebar(); });
    });
}());
</script>
<?php View::yield('scripts'); ?>
</body>
</html>
