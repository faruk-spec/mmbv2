<?php
/**
 * DevZone – Layout (modern design matching ConvertX/BillX)
 */

// Dynamic theme from DB
$defaultTheme  = 'dark';
$allowedThemes = ['dark', 'light'];
try {
    $db = \Core\Database::getInstance();
    $ns = $db->fetch("SELECT default_theme FROM navbar_settings WHERE id = 1");
    if ($ns && !empty($ns['default_theme']) && in_array($ns['default_theme'], $allowedThemes, true)) {
        $defaultTheme = $ns['default_theme'];
    }
} catch (\Exception $e) { /* fallthrough */ }

$_uri      = $_SERVER['REQUEST_URI'] ?? '';
$uiVersion = '20260406000000';
$csrfToken = \Core\Security::generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?= htmlspecialchars($defaultTheme) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="csrf-token" content="<?= $csrfToken ?>">
    <title><?= htmlspecialchars($title ?? 'DevZone') ?> – DevZone</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
          crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="/css/universal-theme.css?v=<?= $uiVersion ?>">

    <style>
        /* ── DevZone CSS Variables ── */
        :root {
            --dz-primary:  #ff2ec4;
            --dz-accent:   #00f0ff;
            --dz-success:  #00ff88;
            --dz-warning:  #ffaa00;
            --dz-danger:   #ff6b6b;
            --dz-purple:   #9945ff;

            --bg-primary:    #06060a;
            --bg-secondary:  #0c0c12;
            --bg-card:       #0f0f18;
            --text-primary:  #e8eefc;
            --text-secondary: #8892a6;
            --text-muted:    #8892a6;
            --border-color:  rgba(255,255,255,0.08);

            --sidebar-width: 15rem;
            --navbar-height: 3.75rem;

            --font-xs:  0.75rem;
            --font-sm:  0.875rem;
            --font-md:  1rem;
            --font-lg:  1.125rem;

            --orange:  #ffaa00;
            --cyan:    #00f0ff;
            --magenta: #ff2ec4;
            --green:   #00ff88;
        }

        [data-theme="light"] {
            --bg-primary:    #f4f6fb;
            --bg-secondary:  #ffffff;
            --bg-card:       #ffffff;
            --text-primary:  #1a2035;
            --text-secondary: #5a6379;
            --border-color:  rgba(0,0,0,0.1);
            --cyan:    #0369a1;
            --magenta: #b0107a;
            --green:   #16a34a;
            --orange:  #e07b00;
            --dz-primary: #b0107a;
            --dz-accent:  #0369a1;
        }

        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: var(--bg-primary); color: var(--text-primary); min-height: 100vh; line-height: 1.6; }

        /* ── Sidebar ── */
        .dz-sidebar {
            position: fixed; left: 0; top: var(--navbar-height);
            width: var(--sidebar-width); height: calc(100vh - var(--navbar-height));
            background: var(--bg-card); border-right: 1px solid var(--border-color);
            overflow-y: auto; z-index: 200; display: flex; flex-direction: column;
            transition: transform 0.25s ease;
        }
        .dz-sidebar::-webkit-scrollbar { width: 0.25rem; }
        .dz-sidebar::-webkit-scrollbar-thumb { background: var(--border-color); border-radius: 0.125rem; }

        .dz-sidebar-logo { display: flex; align-items: center; gap: 0.625rem; padding: 1rem 1.125rem; border-bottom: 1px solid var(--border-color); text-decoration: none; }
        .dz-sidebar-logo .logo-icon { width: 2rem; height: 2rem; border-radius: 0.5rem; background: linear-gradient(135deg, var(--dz-primary), var(--dz-accent)); display: flex; align-items: center; justify-content: center; font-size: 0.875rem; color: #06060a; flex-shrink: 0; }
        .dz-sidebar-logo .logo-text { font-size: 1rem; font-weight: 800; background: linear-gradient(135deg, var(--dz-primary), var(--dz-accent)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }

        .sidebar-section { padding: 0.625rem 0; }
        .sidebar-title { padding: 0.375rem 1rem 0.25rem; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: var(--text-secondary); opacity: 0.6; }
        .sidebar-nav a { display: flex; align-items: center; gap: 0.625rem; padding: 0.5rem 1rem; color: var(--text-secondary); text-decoration: none; font-size: 0.875rem; font-weight: 500; position: relative; transition: background 0.15s, color 0.15s; }
        .sidebar-nav a:hover { background: rgba(255,46,196,0.06); color: var(--text-primary); text-decoration: none; }
        .sidebar-nav a.active { background: rgba(255,46,196,0.1); color: var(--dz-primary); }
        .sidebar-nav a.active::after { content: ''; position: absolute; right: 0; top: 50%; transform: translateY(-50%); width: 0.1875rem; height: 60%; background: var(--dz-primary); border-radius: 0.1875rem 0 0 0.1875rem; }
        .sidebar-nav a i { width: 1rem; text-align: center; flex-shrink: 0; font-size: 0.875rem; }
        .sidebar-nav a .badge-count { margin-left: auto; background: rgba(255,46,196,0.15); color: var(--dz-primary); border-radius: 1rem; padding: 0.0625rem 0.5rem; font-size: 0.7rem; font-weight: 700; }

        .dz-sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 190; }
        .dz-sidebar-overlay.active { display: block; }
        .dz-sidebar-toggle { display: none; position: fixed; bottom: 1.5rem; right: 1.25rem; z-index: 210; width: 3rem; height: 3rem; border-radius: 50%; background: linear-gradient(135deg, var(--dz-primary), var(--dz-accent)); border: none; cursor: pointer; align-items: center; justify-content: center; box-shadow: 0 0.25rem 1.125rem rgba(255,46,196,0.4); color: #06060a; font-size: 1.1rem; }

        /* ── Main content ── */
        .dz-main { margin-left: var(--sidebar-width); margin-top: var(--navbar-height); flex: 1; min-height: calc(100vh - var(--navbar-height)); padding: 1.75rem; }

        /* ── Alert ── */
        .alert { display: flex; align-items: center; gap: 0.625rem; padding: 0.875rem 1rem; border-radius: 0.625rem; font-size: 0.875rem; margin-bottom: 1.25rem; }
        .alert-success { background: rgba(0,255,136,0.08); border: 1px solid rgba(0,255,136,0.3); color: var(--dz-success); }
        .alert-error   { background: rgba(255,107,107,0.08); border: 1px solid rgba(255,107,107,0.3); color: var(--dz-danger); }

        /* ── Shared ── */
        .dz-page-title { font-size: 1.5rem; font-weight: 800; margin-bottom: 0.25rem; }
        .dz-page-subtitle { font-size: 0.875rem; color: var(--text-secondary); margin-bottom: 1.5rem; }
        .dz-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 0.875rem; padding: 1.25rem; margin-bottom: 1.25rem; }
        .dz-btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5625rem 1.125rem; border-radius: 0.5rem; font-size: 0.875rem; font-weight: 600; cursor: pointer; text-decoration: none; border: 1px solid transparent; transition: all 0.15s; line-height: 1; }
        .dz-btn-primary { background: linear-gradient(135deg, var(--dz-primary), var(--dz-accent)); color: #06060a; border: none; }
        .dz-btn-primary:hover { transform: translateY(-1px); color: #06060a; text-decoration: none; }
        .dz-btn-secondary { background: var(--bg-secondary); border-color: var(--border-color); color: var(--text-secondary); }
        .dz-btn-secondary:hover { color: var(--text-primary); text-decoration: none; }
        .dz-grid { display: grid; gap: 1rem; }
        .dz-grid-2 { grid-template-columns: repeat(2, 1fr); }
        .dz-grid-3 { grid-template-columns: repeat(3, 1fr); }
        .dz-grid-4 { grid-template-columns: repeat(4, 1fr); }
        @media (max-width: 1024px) { .dz-grid-4 { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 768px)  { .dz-grid-2, .dz-grid-3, .dz-grid-4 { grid-template-columns: 1fr; } }

        /* Responsive sidebar */
        @media (max-width: 900px) {
            .dz-sidebar { transform: translateX(calc(-1 * var(--sidebar-width))); }
            .dz-sidebar.open { transform: translateX(0); }
            .dz-sidebar-toggle { display: flex; }
            .dz-main { margin-left: 0; }
        }
        @media (max-width: 768px) { .dz-main { padding: 1rem; } }
    </style>
</head>
<body>
<?php
\Core\Timezone::init(\Core\Auth::id());
include BASE_PATH . '/views/layouts/navbar.php';
?>

<div style="display:flex;min-height:100vh;flex-direction:column;">
    <!-- Sidebar -->
    <aside class="dz-sidebar" id="dzSidebar">
        <a href="/projects/devzone" class="dz-sidebar-logo" style="text-decoration:none;">
            <div class="logo-icon"><i class="fas fa-terminal" style="-webkit-text-fill-color:#06060a;"></i></div>
            <div class="logo-text">DevZone</div>
        </a>

        <div class="sidebar-section">
            <div class="sidebar-title">Workspace</div>
            <nav class="sidebar-nav">
                <a href="/projects/devzone"
                   class="<?= (rtrim(parse_url($_uri, PHP_URL_PATH), '/') === '/projects/devzone' || rtrim(parse_url($_uri, PHP_URL_PATH), '/') === '/projects/devzone/dashboard') ? 'active' : '' ?>">
                    <i class="fas fa-th-large"></i> Dashboard
                </a>
                <a href="/projects/devzone/boards"
                   class="<?= strpos($_uri, '/projects/devzone/boards') === 0 ? 'active' : '' ?>">
                    <i class="fas fa-columns"></i> Boards
                </a>
                <a href="/projects/devzone/tasks"
                   class="<?= strpos($_uri, '/projects/devzone/tasks') === 0 ? 'active' : '' ?>">
                    <i class="fas fa-tasks"></i> My Tasks
                </a>
            </nav>
        </div>

        <div class="sidebar-section">
            <div class="sidebar-title">Team</div>
            <nav class="sidebar-nav">
                <a href="/projects/devzone/members"
                   class="<?= strpos($_uri, '/projects/devzone/members') === 0 ? 'active' : '' ?>">
                    <i class="fas fa-users"></i> Members
                </a>
                <a href="/projects/devzone/settings"
                   class="<?= strpos($_uri, '/projects/devzone/settings') === 0 ? 'active' : '' ?>">
                    <i class="fas fa-cog"></i> Settings
                </a>
            </nav>
        </div>
    </aside>

    <div class="dz-sidebar-overlay" id="dzOverlay"></div>

    <!-- Main -->
    <main class="dz-main" id="dzMain">
        <?php
        $flash = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
        if (!empty($flash['success'])): ?>
            <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($flash['success']) ?></div>
        <?php endif;
        if (!empty($flash['error'])): ?>
            <div class="alert alert-error"><i class="fa-solid fa-circle-xmark"></i> <?= htmlspecialchars($flash['error']) ?></div>
        <?php endif; ?>

        <?= $content ?? '' ?>
    </main>

    <button class="dz-sidebar-toggle" id="dzToggle" aria-label="Toggle navigation">
        <i class="fa-solid fa-bars"></i>
    </button>
</div>

<script>
(function () {
    var sidebar = document.getElementById('dzSidebar');
    var overlay = document.getElementById('dzOverlay');
    var toggle  = document.getElementById('dzToggle');
    function open()  { sidebar.classList.add('open'); overlay.classList.add('active'); toggle.innerHTML = '<i class="fa-solid fa-times"></i>'; }
    function close() { sidebar.classList.remove('open'); overlay.classList.remove('active'); toggle.innerHTML = '<i class="fa-solid fa-bars"></i>'; }
    if (toggle)  toggle.addEventListener('click', function () { sidebar.classList.contains('open') ? close() : open(); });
    if (overlay) overlay.addEventListener('click', close);
    sidebar && sidebar.querySelectorAll('.sidebar-nav a').forEach(function (a) {
        a.addEventListener('click', function () { if (window.innerWidth <= 900) close(); });
    });
}());
</script>
</body>
</html>
