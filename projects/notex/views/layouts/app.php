<?php use Core\View; use Core\Auth; ?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= View::e($title ?? 'NoteX') ?> - MyMultiBranch</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-primary: #06060a;
            --bg-secondary: #0c0c12;
            --bg-card: #0f0f18;
            --accent: #ffd700;
            --accent2: #ff2ec4;
            --green: #00ff88;
            --cyan: #00d4ff;
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
            background: radial-gradient(ellipse at 20% 0%, rgba(255,215,0,0.08) 0%, transparent 50%),
                        radial-gradient(ellipse at 80% 100%, rgba(255,46,196,0.06) 0%, transparent 50%);
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
        .menu-link:hover { background: rgba(255,215,0,0.06); color: var(--accent); }
        .menu-link.active { background: rgba(255,215,0,0.1); color: var(--accent); border-left: 3px solid var(--accent); }
        .menu-link i { width: 18px; font-size: 14px; }
        .sidebar-footer { padding: 18px; border-top: 1px solid var(--border-color); }

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

        /* Note card grid */
        .notes-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 16px; }
        .note-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 18px; transition: all 0.2s; position: relative; cursor: pointer; }
        .note-card:hover { border-color: rgba(255,215,0,0.3); transform: translateY(-2px); }
        .note-card-accent { position: absolute; top: 0; left: 0; width: 4px; height: 100%; border-radius: 12px 0 0 12px; }
        .note-card-title { font-weight: 600; margin-bottom: 8px; font-size: 15px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .note-card-preview { color: var(--text-secondary); font-size: 13px; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; margin-bottom: 12px; }
        .note-card-meta { display: flex; align-items: center; justify-content: space-between; font-size: 12px; color: var(--text-secondary); }
        .note-card-actions { display: flex; gap: 8px; }

        /* Stat cards */
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px; }
        .stat-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 20px; }
        .stat-value { font-size: 2rem; font-weight: 700; line-height: 1; margin-bottom: 6px; }
        .stat-label { color: var(--text-secondary); font-size: 13px; }

        /* Grid */
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .mb-3 { margin-bottom: 20px; }
        .mb-4 { margin-bottom: 28px; }

        /* Table */
        .table-container { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        th { text-align: left; padding: 12px 14px; color: var(--text-secondary); font-size: 12px; text-transform: uppercase; font-weight: 600; border-bottom: 1px solid var(--border-color); }
        td { padding: 12px 14px; border-bottom: 1px solid rgba(255,255,255,0.04); vertical-align: middle; }
        tr:last-child td { border-bottom: none; }

        /* Buttons */
        .btn { display: inline-flex; align-items: center; gap: 7px; padding: 10px 20px; border: none; border-radius: 8px; font-family: inherit; font-size: 14px; font-weight: 500; cursor: pointer; transition: all 0.2s; text-decoration: none; }
        .btn-primary { background: linear-gradient(135deg, var(--accent), #e6c200); color: #1a1a00; }
        .btn-primary:hover { opacity: 0.88; }
        .btn-secondary { background: var(--bg-secondary); color: var(--text-primary); border: 1px solid var(--border-color); }
        .btn-danger { background: rgba(255,107,107,0.15); color: var(--red); border: 1px solid var(--red); }
        .btn-sm { padding: 6px 12px; font-size: 12px; }
        .btn-icon { padding: 7px; border-radius: 7px; width: 32px; height: 32px; justify-content: center; }

        /* Badge */
        .badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
        .badge-pin { background: rgba(255,215,0,0.15); color: var(--accent); }

        /* Form */
        .form-group { margin-bottom: 18px; }
        .form-label { display: block; margin-bottom: 7px; color: var(--text-secondary); font-size: 13px; font-weight: 500; }
        .form-input { width: 100%; padding: 11px 14px; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 8px; color: var(--text-primary); font-family: inherit; font-size: 14px; transition: all 0.2s; }
        .form-input:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 3px rgba(255,215,0,0.1); }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        textarea.form-input { resize: vertical; min-height: 300px; line-height: 1.7; }

        /* Alert */
        .alert { padding: 13px 18px; border-radius: 8px; margin-bottom: 18px; font-size: 14px; }
        .alert-success { background: rgba(0,255,136,0.1); border: 1px solid rgba(0,255,136,0.3); color: var(--green); }
        .alert-error { background: rgba(255,107,107,0.1); border: 1px solid rgba(255,107,107,0.3); color: var(--red); }
        .alert-info { background: rgba(0,212,255,0.1); border: 1px solid rgba(0,212,255,0.3); color: var(--cyan); }

        /* Pin icon */
        .pin-icon { color: var(--accent); font-size: 12px; }

        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .stats-grid { grid-template-columns: 1fr 1fr; }
            .notes-grid { grid-template-columns: 1fr; }
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
            <a href="/projects/notex" class="sidebar-logo">
                <i class="fas fa-sticky-note"></i>
                <div>
                    NoteX
                    <span>Private Cloud Notes</span>
                </div>
            </a>
        </div>
        <nav class="sidebar-menu">
            <div class="menu-section">
                <div class="menu-section-title">Main</div>
                <a href="/projects/notex" class="menu-link <?= ($_SERVER['REQUEST_URI'] ?? '') === '/projects/notex' || ($_SERVER['REQUEST_URI'] ?? '') === '/projects/notex/dashboard' ? 'active' : '' ?>">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="/projects/notex/notes" class="menu-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/projects/notex/notes') === 0 ? 'active' : '' ?>">
                    <i class="fas fa-list"></i> All Notes
                </a>
                <a href="/projects/notex/create" class="menu-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/projects/notex/create') === 0 ? 'active' : '' ?>">
                    <i class="fas fa-plus-circle"></i> New Note
                </a>
            </div>
            <div class="menu-section">
                <div class="menu-section-title">Organise</div>
                <a href="/projects/notex/folders" class="menu-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/projects/notex/folders') === 0 ? 'active' : '' ?>">
                    <i class="fas fa-folder"></i> Folders
                </a>
                <a href="/projects/notex/notes?pinned=1" class="menu-link">
                    <i class="fas fa-thumbtack"></i> Pinned
                </a>
            </div>
            <div class="menu-section">
                <div class="menu-section-title">Account</div>
                <a href="/projects/notex/settings" class="menu-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/projects/notex/settings') === 0 ? 'active' : '' ?>">
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
                <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--accent),var(--accent2));display:flex;align-items:center;justify-content:center;font-weight:600;font-size:14px;color:#1a1a00;">
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
                <h1><?= View::e($title ?? 'NoteX') ?></h1>
                <?php if (!empty($subtitle)): ?>
                    <p><?= View::e($subtitle) ?></p>
                <?php endif; ?>
            </div>
            <div class="topbar-actions">
                <a href="/projects/notex/create" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> New Note
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
            <?php if (!empty($_SESSION['share_url'])): ?>
                <div class="alert alert-info">
                    Share link: <a href="<?= View::e($_SESSION['share_url']) ?>" target="_blank" style="color:var(--cyan);"><?= View::e($_SESSION['share_url']) ?></a>
                </div>
                <?php unset($_SESSION['share_url']); ?>
            <?php endif; ?>

            <?php View::yield('content'); ?>
        </div>
    </div>
</div>
</body>
</html>
