<?php use Core\View; use Core\Auth; ?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= View::e($title ?? 'LinkShortner') ?> - MyMultiBranch</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-primary: #06060a;
            --bg-secondary: #0c0c12;
            --bg-card: #0f0f18;
            --accent: #00d4ff;
            --accent2: #ff2ec4;
            --green: #00ff88;
            --orange: #ffaa00;
            --red: #ff6b6b;
            --text-primary: #e8eefc;
            --text-secondary: #8892a6;
            --border-color: rgba(255,255,255,0.1);
            --sidebar-width: 270px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: var(--bg-primary); color: var(--text-primary); min-height: 100vh; }
        body::before {
            content: ''; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: radial-gradient(ellipse at 20% 0%, rgba(0,212,255,0.12) 0%, transparent 50%),
                        radial-gradient(ellipse at 80% 100%, rgba(255,46,196,0.08) 0%, transparent 50%);
            pointer-events: none; z-index: -1;
        }
        .app-container { display: flex; min-height: 100vh; }

        /* Sidebar */
        .sidebar { width: var(--sidebar-width); background: rgba(12,12,18,0.97); border-right: 1px solid var(--border-color); position: fixed; height: 100vh; overflow-y: auto; z-index: 1000; }
        .sidebar::-webkit-scrollbar { width: 4px; }
        .sidebar::-webkit-scrollbar-thumb { background: var(--accent); border-radius: 2px; }
        .sidebar-header { padding: 22px 20px; border-bottom: 1px solid var(--border-color); }
        .sidebar-logo { display: flex; align-items: center; gap: 10px; font-size: 1.3rem; font-weight: 700; color: var(--accent); text-decoration: none; }
        .sidebar-logo i { font-size: 1.5rem; }
        .sidebar-logo span { font-size: 0.75rem; color: var(--text-secondary); font-weight: 400; display: block; margin-top: 2px; }
        .sidebar-menu { padding: 16px 0; }
        .menu-section { margin-bottom: 20px; }
        .menu-section-title { padding: 0 18px 8px; font-size: 10px; text-transform: uppercase; color: var(--text-secondary); font-weight: 600; letter-spacing: 1px; }
        .menu-link { display: flex; align-items: center; gap: 10px; padding: 11px 18px; color: var(--text-secondary); text-decoration: none; transition: all 0.2s; font-size: 14px; }
        .menu-link:hover { background: rgba(0,212,255,0.06); color: var(--accent); }
        .menu-link.active { background: rgba(0,212,255,0.1); color: var(--accent); border-left: 3px solid var(--accent); }
        .menu-link i { width: 18px; font-size: 14px; }
        .menu-link .badge { margin-left: auto; background: var(--red); color: #fff; padding: 2px 7px; border-radius: 10px; font-size: 11px; }
        .sidebar-footer { padding: 18px 18px; border-top: 1px solid var(--border-color); margin-top: auto; }

        /* Main content */
        .main-content { margin-left: var(--sidebar-width); flex: 1; display: flex; flex-direction: column; }
        .topbar { background: rgba(12,12,18,0.9); border-bottom: 1px solid var(--border-color); padding: 16px 28px; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 100; }
        .topbar-title h1 { font-size: 1.4rem; font-weight: 600; }
        .topbar-title p { color: var(--text-secondary); font-size: 13px; margin-top: 2px; }
        .topbar-actions { display: flex; align-items: center; gap: 12px; }
        .page-content { flex: 1; padding: 28px; }

        /* Cards */
        .card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 22px; }
        .card-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px; }
        .card-title { font-size: 1rem; font-weight: 600; display: flex; align-items: center; gap: 8px; }

        /* Stat cards */
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px; }
        .stat-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 20px; }
        .stat-value { font-size: 2rem; font-weight: 700; line-height: 1; margin-bottom: 6px; }
        .stat-label { color: var(--text-secondary); font-size: 13px; }

        /* Grid */
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
        .mb-3 { margin-bottom: 20px; }
        .mb-4 { margin-bottom: 28px; }

        /* Table */
        .table-container { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        th { text-align: left; padding: 12px 14px; color: var(--text-secondary); font-size: 12px; text-transform: uppercase; font-weight: 600; border-bottom: 1px solid var(--border-color); }
        td { padding: 12px 14px; border-bottom: 1px solid rgba(255,255,255,0.04); vertical-align: middle; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: rgba(255,255,255,0.02); }

        /* Buttons */
        .btn { display: inline-flex; align-items: center; gap: 7px; padding: 10px 20px; border: none; border-radius: 8px; font-family: inherit; font-size: 14px; font-weight: 500; cursor: pointer; transition: all 0.2s; text-decoration: none; }
        .btn-primary { background: linear-gradient(135deg, var(--accent), #0080cc); color: #fff; }
        .btn-primary:hover { opacity: 0.88; }
        .btn-secondary { background: var(--bg-secondary); color: var(--text-primary); border: 1px solid var(--border-color); }
        .btn-danger { background: rgba(255,107,107,0.15); color: var(--red); border: 1px solid var(--red); }
        .btn-sm { padding: 6px 12px; font-size: 12px; }

        /* Badges */
        .badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
        .badge-success { background: rgba(0,255,136,0.15); color: var(--green); }
        .badge-danger { background: rgba(255,107,107,0.15); color: var(--red); }
        .badge-warning { background: rgba(255,170,0,0.15); color: var(--orange); }

        /* Form */
        .form-group { margin-bottom: 18px; }
        .form-label { display: block; margin-bottom: 7px; color: var(--text-secondary); font-size: 13px; font-weight: 500; }
        .form-input { width: 100%; padding: 11px 14px; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 8px; color: var(--text-primary); font-family: inherit; font-size: 14px; transition: all 0.2s; }
        .form-input:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 3px rgba(0,212,255,0.1); }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

        /* Alert */
        .alert { padding: 13px 18px; border-radius: 8px; margin-bottom: 18px; font-size: 14px; }
        .alert-success { background: rgba(0,255,136,0.1); border: 1px solid rgba(0,255,136,0.3); color: var(--green); }
        .alert-error { background: rgba(255,107,107,0.1); border: 1px solid rgba(255,107,107,0.3); color: var(--red); }

        /* Copy button */
        .copy-btn { cursor: pointer; color: var(--accent); background: none; border: none; font-family: inherit; font-size: 13px; padding: 4px 8px; border-radius: 4px; transition: all 0.2s; }
        .copy-btn:hover { background: rgba(0,212,255,0.1); }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .stats-grid { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 480px) {
            .stats-grid { grid-template-columns: 1fr; }
            .form-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="app-container">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="/projects/linkshortner" class="sidebar-logo">
                <i class="fas fa-link"></i>
                <div>
                    LinkShortner
                    <span>URL Shortener & Analytics</span>
                </div>
            </a>
        </div>
        <nav class="sidebar-menu">
            <div class="menu-section">
                <div class="menu-section-title">Main</div>
                <a href="/projects/linkshortner" class="menu-link <?= ($_SERVER['REQUEST_URI'] ?? '') === '/projects/linkshortner' || ($_SERVER['REQUEST_URI'] ?? '') === '/projects/linkshortner/dashboard' ? 'active' : '' ?>">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="/projects/linkshortner/links" class="menu-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/projects/linkshortner/links') === 0 ? 'active' : '' ?>">
                    <i class="fas fa-list"></i> My Links
                </a>
                <a href="/projects/linkshortner/create" class="menu-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/projects/linkshortner/create') === 0 ? 'active' : '' ?>">
                    <i class="fas fa-plus-circle"></i> Create Link
                </a>
            </div>
            <div class="menu-section">
                <div class="menu-section-title">Analytics</div>
                <a href="/projects/linkshortner/analytics" class="menu-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/projects/linkshortner/analytics') === 0 ? 'active' : '' ?>">
                    <i class="fas fa-chart-bar"></i> Analytics
                </a>
            </div>
            <div class="menu-section">
                <div class="menu-section-title">Account</div>
                <a href="/projects/linkshortner/settings" class="menu-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/projects/linkshortner/settings') === 0 ? 'active' : '' ?>">
                    <i class="fas fa-cog"></i> Settings
                </a>
                <a href="/dashboard" class="menu-link">
                    <i class="fas fa-th-large"></i> All Apps
                </a>
            </div>
        </nav>
        <div class="sidebar-footer">
            <?php $user = Auth::user(); ?>
            <div style="display:flex;align-items:center;gap:10px;font-size:13px;">
                <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--accent),var(--accent2));display:flex;align-items:center;justify-content:center;font-weight:600;font-size:14px;">
                    <?= strtoupper(substr($user['username'] ?? $user['email'] ?? 'U', 0, 1)) ?>
                </div>
                <div>
                    <div style="font-weight:500;"><?= View::e($user['username'] ?? explode('@', $user['email'] ?? 'User')[0]) ?></div>
                    <a href="/logout" style="color:var(--text-secondary);font-size:12px;text-decoration:none;">Logout</a>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="main-content">
        <div class="topbar">
            <div class="topbar-title">
                <h1><?= View::e($title ?? 'LinkShortner') ?></h1>
                <?php if (!empty($subtitle)): ?>
                    <p><?= View::e($subtitle) ?></p>
                <?php endif; ?>
            </div>
            <div class="topbar-actions">
                <a href="/projects/linkshortner/create" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> New Link
                </a>
                <a href="/dashboard" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Dashboard
                </a>
            </div>
        </div>

        <div class="page-content">
            <?php if (!empty($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= View::e($_SESSION['success']) ?></div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            <?php if (!empty($_SESSION['error'])): ?>
                <div class="alert alert-error"><?= View::e($_SESSION['error']) ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <?php View::yield('content'); ?>
        </div>
    </div>
</div>

<script>
// Copy to clipboard helper
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        const el = document.querySelector('[data-copy="' + CSS.escape(text) + '"]');
        if (el) { el.textContent = 'Copied!'; setTimeout(() => el.textContent = 'Copy', 1500); }
    });
}
</script>
</body>
</html>
