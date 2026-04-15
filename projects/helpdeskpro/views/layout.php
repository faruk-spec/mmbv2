<?php
$defaultTheme = 'dark';
try {
    $db = \Core\Database::getInstance();
    $ns = $db->fetch("SELECT default_theme FROM navbar_settings WHERE id = 1");
    if (!empty($ns['default_theme']) && in_array($ns['default_theme'], ['dark', 'light'], true)) {
        $defaultTheme = $ns['default_theme'];
    }
} catch (\Exception $e) {
}

$uri = $_SERVER['REQUEST_URI'] ?? '';
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?= htmlspecialchars($defaultTheme) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Helpdesk Pro') ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
    <style>
        :root {
            --hp-primary:#3b82f6; --hp-accent:#06b6d4; --hp-success:#10b981; --hp-warning:#f59e0b; --hp-danger:#ef4444;
            --bg-primary:#06070b; --bg-card:#0d1119; --text-primary:#e5e7eb; --text-secondary:#94a3b8; --border:rgba(255,255,255,.08);
            --nav-h:3.75rem; --sidebar-w:15rem;
        }
        [data-theme="light"] {
            --bg-primary:#f7f9fc; --bg-card:#fff; --text-primary:#1f2937; --text-secondary:#6b7280; --border:rgba(15,23,42,.1);
        }
        * { box-sizing:border-box; }
        body { margin:0; font-family:Inter,system-ui,-apple-system,sans-serif; background:var(--bg-primary); color:var(--text-primary); }
        .sidebar { position:fixed; top:var(--nav-h); left:0; width:var(--sidebar-w); height:calc(100vh - var(--nav-h)); background:var(--bg-card); border-right:1px solid var(--border); padding:.75rem; overflow-y:auto; }
        .logo { display:flex; align-items:center; gap:.5rem; padding:.75rem; color:var(--text-primary); text-decoration:none; font-weight:700; }
        .logo i { color:var(--hp-accent); }
        .nav a { display:flex; gap:.5rem; align-items:center; padding:.55rem .7rem; border-radius:.5rem; color:var(--text-secondary); text-decoration:none; font-size:.9rem; }
        .nav a:hover,.nav a.active { background:rgba(59,130,246,.12); color:var(--hp-primary); }
        .nav-group-label { font-size:.68rem; color:var(--text-secondary); text-transform:uppercase; padding:.4rem .7rem .2rem; letter-spacing:.05em; }
        .main { margin-top:var(--nav-h); margin-left:var(--sidebar-w); padding:1.3rem; min-height:calc(100vh - var(--nav-h)); }
        .card { background:var(--bg-card); border:1px solid var(--border); border-radius:.8rem; padding:1rem; margin-bottom:1rem; }
        .grid { display:grid; gap:1rem; }
        .g3 { grid-template-columns:repeat(3,minmax(0,1fr)); }
        .g2 { grid-template-columns:repeat(2,minmax(0,1fr)); }
        .btn { display:inline-flex; gap:.4rem; align-items:center; padding:.55rem .9rem; border-radius:.5rem; border:1px solid transparent; text-decoration:none; cursor:pointer; font-weight:600; }
        .btn-primary { background:linear-gradient(135deg,var(--hp-primary),var(--hp-accent)); color:#fff; }
        .btn-secondary { background:transparent; border-color:var(--border); color:var(--text-primary); }
        .badge { display:inline-block; padding:.2rem .5rem; border-radius:999px; font-size:.72rem; font-weight:600; }
        .badge-open { background:rgba(59,130,246,.16); color:#60a5fa; }
        .badge-in_progress,.badge-waiting_customer { background:rgba(245,158,11,.16); color:#fbbf24; }
        .badge-resolved,.badge-closed { background:rgba(16,185,129,.16); color:#34d399; }
        table { width:100%; border-collapse:collapse; }
        th,td { padding:.7rem .55rem; border-bottom:1px solid var(--border); text-align:left; font-size:.88rem; }
        input,select,textarea { width:100%; background:transparent; border:1px solid var(--border); color:var(--text-primary); border-radius:.5rem; padding:.6rem .65rem; }
        textarea { min-height:7rem; resize:vertical; }
        .flash { padding:.7rem .8rem; border-radius:.55rem; margin-bottom:1rem; font-size:.88rem; }
        .flash.success { background:rgba(16,185,129,.14); border:1px solid rgba(16,185,129,.3); color:#34d399; }
        .flash.error { background:rgba(239,68,68,.14); border:1px solid rgba(239,68,68,.3); color:#f87171; }
        @media(max-width:900px) {
            .sidebar { position:static; width:auto; height:auto; }
            .main { margin-left:0; }
            .g3,.g2 { grid-template-columns:1fr; }
        }
    </style>
</head>
<body>
<?php \Core\Timezone::init(\Core\Auth::id()); include BASE_PATH . '/views/layouts/navbar.php'; ?>
<aside class="sidebar">
    <a class="logo" href="/projects/helpdeskpro"><i class="fas fa-headset"></i> Helpdesk Pro</a>
    <nav class="nav">
        <a href="/projects/helpdeskpro" class="<?= (rtrim(parse_url($uri, PHP_URL_PATH), '/') === '/projects/helpdeskpro' || strpos($uri, '/projects/helpdeskpro/dashboard') !== false) ? 'active' : '' ?>"><i class="fas fa-gauge-high"></i> Dashboard</a>
        <a href="/projects/helpdeskpro/live-support" class="<?= strpos($uri, '/projects/helpdeskpro/live-support') !== false && strpos($uri, '/agent/live-support') === false ? 'active' : '' ?>"><i class="fas fa-comments"></i> Live Support</a>
        <a href="/projects/helpdeskpro/tickets" class="<?= strpos($uri, '/projects/helpdeskpro/tickets') !== false ? 'active' : '' ?>"><i class="fas fa-ticket"></i> Tickets</a>
        <a href="/projects/helpdeskpro/customers" class="<?= strpos($uri, '/projects/helpdeskpro/customers') !== false ? 'active' : '' ?>"><i class="fas fa-users"></i> Customers</a>
        <?php if (\Core\Auth::hasRole('admin') || \Core\Auth::hasRole('super_admin') || \Core\Auth::hasRole('support')): ?>
        <a href="/projects/helpdeskpro/agent/live-support" class="<?= strpos($uri, '/projects/helpdeskpro/agent/live-support') !== false ? 'active' : '' ?>"><i class="fas fa-user-tie"></i> Agent Console</a>
        <div style="margin:.4rem 0 .1rem;border-top:1px solid var(--border);"></div>
        <div class="nav-group-label">Admin</div>
        <a href="/projects/helpdeskpro/templates" class="<?= strpos($uri, '/projects/helpdeskpro/templates') !== false ? 'active' : '' ?>"><i class="fas fa-folder-tree"></i> Templates</a>
        <a href="/projects/helpdeskpro/agents" class="<?= strpos($uri, '/projects/helpdeskpro/agents') !== false ? 'active' : '' ?>"><i class="fas fa-user-group"></i> Agents &amp; Roles</a>
        <a href="/projects/helpdeskpro/analytics" class="<?= strpos($uri, '/projects/helpdeskpro/analytics') !== false ? 'active' : '' ?>"><i class="fas fa-chart-bar"></i> Analytics</a>
        <a href="/projects/helpdeskpro/workflows" class="<?= strpos($uri, '/projects/helpdeskpro/workflows') !== false ? 'active' : '' ?>"><i class="fas fa-diagram-project"></i> Workflows</a>
        <a href="/projects/helpdeskpro/integrations" class="<?= strpos($uri, '/projects/helpdeskpro/integrations') !== false ? 'active' : '' ?>"><i class="fas fa-plug"></i> Integrations</a>
        <a href="/projects/helpdeskpro/settings" class="<?= strpos($uri, '/projects/helpdeskpro/settings') !== false ? 'active' : '' ?>"><i class="fas fa-gear"></i> Settings</a>
        <?php endif; ?>
    </nav>
</aside>
<main class="main">
    <?php $flash = $_SESSION['_flash'] ?? []; unset($_SESSION['_flash']); ?>
    <?php if (!empty($flash['success'])): ?><div class="flash success"><?= htmlspecialchars($flash['success']) ?></div><?php endif; ?>
    <?php if (!empty($flash['error'])): ?><div class="flash error"><?= htmlspecialchars($flash['error']) ?></div><?php endif; ?>
    <?= $content ?? '' ?>
</main>
</body>
</html>
