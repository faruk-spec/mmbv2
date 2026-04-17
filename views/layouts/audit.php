<?php use Core\View; use Core\Security; use Core\Auth; ?>
<?php
$_auditUiTheme = 'default';
$_auditDefaultMode = 'dark';
try {
    $themeConfig = \Controllers\Admin\ThemeController::loadThemeForLayout();
    $_auditUiTheme = $themeConfig['theme'] ?? 'default';
    $_auditDefaultMode = $themeConfig['mode'] ?? 'dark';
} catch (\Exception $e) {}
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?= htmlspecialchars($_auditDefaultMode) ?>" data-ui-theme="<?= htmlspecialchars($_auditUiTheme) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= Security::generateCsrfToken() ?>">
    <title><?= View::e($title ?? 'Audit Explorer') ?> — <?= defined('APP_NAME') ? APP_NAME : 'MMB' ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
    /* ── CSS Variables ─────────────────────────────────────────── */
    :root[data-theme="dark"] {
        --bg:            #09090b;
        --bg-s:          #111113;
        --bg-card:       #18181b;
        --cyan:          #3b82f6;
        --magenta:       #8b5cf6;
        --green:         #22c55e;
        --orange:        #f59e0b;
        --red:           #ef4444;
        --text:          #fafafa;
        --text-m:        #a1a1aa;
        --border:        rgba(255,255,255,.08);
        --topbar-h:      52px;
    }
    :root[data-theme="light"] {
        --bg:            #fafafa;
        --bg-s:          #ffffff;
        --bg-card:       #ffffff;
        --cyan:          #2563eb;
        --magenta:       #7c3aed;
        --green:         #16a34a;
        --orange:        #d97706;
        --red:           #dc2626;
        --text:          #18181b;
        --text-m:        #52525b;
        --border:        rgba(0,0,0,.08);
        --topbar-h:      52px;
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    html, body {
        height: 100%;
        font-family: 'Poppins', sans-serif;
        background: var(--bg);
        color: var(--text);
        overflow: hidden;          /* prevent double scrollbars — inner panels scroll */
    }

    /* ── Top bar ────────────────────────────────────────────────── */
    .ae-topbar {
        position: fixed;
        top: 0; left: 0; right: 0;
        height: var(--topbar-h);
        background: var(--bg-card);
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        padding: 0 16px;
        gap: 12px;
        z-index: 100;
    }
    .ae-topbar-brand {
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 700;
        font-size: 14px;
        color: var(--text);
        text-decoration: none;
        flex-shrink: 0;
    }
    .ae-topbar-brand .logo-dot {
        width: 28px; height: 28px;
        border-radius: 8px;
        background: var(--cyan);
        display: flex; align-items: center; justify-content: center;
        font-size: 13px; font-weight: 700; color: #fff;
    }
    .ae-topbar-sep  { width: 1px; height: 22px; background: var(--border); flex-shrink: 0; }
    .ae-topbar-title { font-size: 13px; font-weight: 600; color: var(--text-m); white-space: nowrap; flex-shrink: 0; }

    .ae-topbar-breadcrumb {
        display: flex; align-items: center; gap: 5px;
        font-size: 12px; color: var(--text-m);
    }
    .ae-topbar-breadcrumb a { color: var(--text-m); text-decoration: none; }
    .ae-topbar-breadcrumb a:hover { color: var(--cyan); }
    .ae-topbar-breadcrumb .sep { opacity: .4; }

    .ae-topbar-actions { margin-left: auto; display: flex; align-items: center; gap: 8px; }
    .ae-topbar-btn {
        display: flex; align-items: center; gap: 6px;
        padding: 5px 12px;
        border-radius: 8px;
        border: 1px solid var(--border);
        background: transparent;
        color: var(--text-m);
        font-size: 12px;
        cursor: pointer;
        text-decoration: none;
        transition: .15s;
        font-family: inherit;
    }
    .ae-topbar-btn:hover { border-color: var(--cyan); color: var(--cyan); }
    .ae-topbar-btn.primary { background: var(--cyan); color: #fff; border-color: var(--cyan); font-weight: 700; }
    .ae-topbar-btn.primary:hover { opacity: .85; }

    /* ── Page body (below topbar) ───────────────────────────────── */
    .ae-page {
        position: fixed;
        top: var(--topbar-h);
        left: 0; right: 0; bottom: 0;
        display: flex;
        overflow: hidden;
    }

    /* ── Content section ─────────────────────────────────────────── */
    .ae-page-content {
        flex: 1;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    /* buttons/links used in view */
    a { color: var(--cyan); }
    :focus-visible { outline: 2px solid var(--cyan); outline-offset: 2px; }
    </style>
</head>
<body>

<!-- ── Top bar ─────────────────────────────────────────────────── -->
<header class="ae-topbar">
    <a href="/admin/audit" class="ae-topbar-brand">
        <div class="logo-dot">AE</div>
        <span>Audit Explorer</span>
    </a>

    <div class="ae-topbar-sep"></div>

    <nav class="ae-topbar-breadcrumb">
        <a href="/admin">Admin</a>
        <span class="sep">›</span>
        <a href="/admin/logs">Logs</a>
        <span class="sep">›</span>
        <span>Audit Explorer</span>
    </nav>

    <div class="ae-topbar-actions">
        <!-- Theme toggle -->
        <button class="ae-topbar-btn" id="themeToggle" title="Toggle light/dark">
            <i class="fas fa-moon" id="themeIcon"></i>
        </button>
        <a href="/admin/logs/activity" class="ae-topbar-btn">
            <i class="fas fa-history"></i> Activity Timeline
        </a>
        <a href="/admin" class="ae-topbar-btn">
            <i class="fas fa-arrow-left"></i> Back to Admin
        </a>
        <?php $user = Auth::user(); ?>
        <?php if ($user): ?>
        <div style="display:flex;align-items:center;gap:6px;padding:4px 10px;border-radius:8px;background:var(--bg-s);font-size:12px;color:var(--text-m);">
            <div style="width:24px;height:24px;border-radius:50%;background:var(--cyan);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#fff;">
                <?= strtoupper(substr($user['name'] ?? 'A', 0, 1)) ?>
            </div>
            <?= View::e($user['name'] ?? 'Admin') ?>
        </div>
        <?php endif; ?>
    </div>
</header>

<!-- ── Page body ───────────────────────────────────────────────── -->
<main class="ae-page">
    <div class="ae-page-content">
        <?php View::yield('content'); ?>
    </div>
</main>

<script>
// ── Theme: init + toggle (persisted in localStorage) ─────────────
(function initTheme() {
    const stored = localStorage.getItem('ae_theme');
    if (stored === 'light') {
        document.documentElement.setAttribute('data-theme', 'light');
        document.getElementById('themeIcon').className = 'fas fa-sun';
    }
})();

document.getElementById('themeToggle').addEventListener('click', function() {
    const html   = document.documentElement;
    const isDark = html.getAttribute('data-theme') !== 'light';
    const next   = isDark ? 'light' : 'dark';
    html.setAttribute('data-theme', next);
    localStorage.setItem('ae_theme', next);
    document.getElementById('themeIcon').className = isDark ? 'fas fa-sun' : 'fas fa-moon';
});
</script>

<?php View::yield('scripts'); ?>
</body>
</html>
