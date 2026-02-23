<!DOCTYPE html>
<?php
// Cache busting
$uiVersion = '20260223000000';
header("Cache-Control: no-cache, no-store, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// Current view (set by the controller via extract())
$currentView = $view ?? 'dashboard';
?>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
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
        :root {
            --cx-primary:   #6366f1;
            --cx-secondary: #8b5cf6;
            --cx-accent:    #06b6d4;
            --cx-success:   #10b981;
            --cx-warning:   #f59e0b;
            --cx-danger:    #ef4444;
            --bg-primary:   #06060a;
            --bg-card:      #0f0f18;
            --text-primary: #e8eefc;
            --text-muted:   #8892a6;
            --border:       rgba(255,255,255,0.1);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
        }

        /* ── Sidebar ── */
        .cx-sidebar {
            width: 240px;
            min-height: 100vh;
            background: var(--bg-card);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            padding: 1.5rem 0;
            flex-shrink: 0;
        }
        .cx-logo {
            padding: 0 1.5rem 1.5rem;
            display: flex;
            align-items: center;
            gap: .75rem;
            border-bottom: 1px solid var(--border);
            margin-bottom: 1rem;
        }
        .cx-logo .icon {
            width: 36px; height: 36px;
            background: linear-gradient(135deg, var(--cx-primary), var(--cx-secondary));
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem;
        }
        .cx-logo .brand { font-weight: 700; font-size: 1.2rem; }
        .cx-logo .brand span { color: var(--cx-primary); }

        .cx-nav a {
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: .6rem 1.5rem;
            color: var(--text-muted);
            text-decoration: none;
            font-size: .9rem;
            transition: background .15s, color .15s;
        }
        .cx-nav a:hover,
        .cx-nav a.active {
            background: rgba(99,102,241,.12);
            color: var(--text-primary);
        }
        .cx-nav a i { width: 18px; text-align: center; }

        /* ── Main ── */
        .cx-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }
        .cx-topbar {
            padding: 1rem 2rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: var(--bg-card);
        }
        .cx-topbar h1 { font-size: 1.2rem; font-weight: 600; }
        .cx-topbar .user-info { display: flex; align-items: center; gap: .75rem; font-size: .875rem; color: var(--text-muted); }

        .cx-content { padding: 2rem; flex: 1; }

        /* ── Cards ── */
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .card-header {
            font-weight: 600;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: .5rem;
        }
        .card-header i { color: var(--cx-primary); }

        /* ── Stats grid ── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 1.25rem;
            text-align: center;
        }
        .stat-card .value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--cx-primary);
        }
        .stat-card .label {
            font-size: .8rem;
            color: var(--text-muted);
            margin-top: .25rem;
        }

        /* ── Buttons ── */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .55rem 1.25rem;
            border-radius: 8px;
            font-size: .875rem;
            font-weight: 500;
            cursor: pointer;
            border: none;
            transition: opacity .15s, transform .1s;
            text-decoration: none;
        }
        .btn:hover { opacity: .85; transform: translateY(-1px); }
        .btn-primary { background: var(--cx-primary); color: #fff; }
        .btn-secondary { background: rgba(255,255,255,.08); color: var(--text-primary); border: 1px solid var(--border); }
        .btn-success { background: var(--cx-success); color: #fff; }
        .btn-danger  { background: var(--cx-danger);  color: #fff; }

        /* ── Form elements ── */
        .form-group { margin-bottom: 1.25rem; }
        .form-group label { display: block; font-size: .875rem; color: var(--text-muted); margin-bottom: .4rem; }
        .form-control {
            width: 100%;
            background: rgba(255,255,255,.05);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: .65rem 1rem;
            color: var(--text-primary);
            font-size: .9rem;
            outline: none;
            transition: border-color .15s;
        }
        .form-control:focus { border-color: var(--cx-primary); }

        /* ── Upload zone ── */
        .upload-zone {
            border: 2px dashed var(--border);
            border-radius: 12px;
            padding: 3rem 2rem;
            text-align: center;
            cursor: pointer;
            transition: border-color .2s, background .2s;
        }
        .upload-zone:hover,
        .upload-zone.drag-over {
            border-color: var(--cx-primary);
            background: rgba(99,102,241,.06);
        }
        .upload-zone i { font-size: 2.5rem; color: var(--cx-primary); margin-bottom: .75rem; }
        .upload-zone p  { color: var(--text-muted); font-size: .9rem; }

        /* ── Status badges ── */
        .badge {
            display: inline-block;
            padding: .2rem .65rem;
            border-radius: 20px;
            font-size: .75rem;
            font-weight: 600;
        }
        .badge-pending    { background: rgba(245,158,11,.15);  color: var(--cx-warning); }
        .badge-processing { background: rgba(99,102,241,.15);  color: var(--cx-primary); }
        .badge-completed  { background: rgba(16,185,129,.15);  color: var(--cx-success); }
        .badge-failed     { background: rgba(239,68,68,.15);   color: var(--cx-danger); }
        .badge-cancelled  { background: rgba(136,146,166,.15); color: var(--text-muted); }

        /* ── Table ── */
        .cx-table { width: 100%; border-collapse: collapse; }
        .cx-table th,
        .cx-table td {
            padding: .75rem 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border);
            font-size: .875rem;
        }
        .cx-table th { color: var(--text-muted); font-weight: 500; font-size: .8rem; text-transform: uppercase; }
        .cx-table tr:last-child td { border-bottom: none; }

        /* ── Responsive ── */
        @media (max-width: 768px) {
            .cx-sidebar { display: none; }
            .cx-content { padding: 1rem; }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="cx-sidebar">
        <div class="cx-logo">
            <div class="icon"><i class="fa-solid fa-shuffle"></i></div>
            <div class="brand">Convert<span>X</span></div>
        </div>
        <div class="cx-nav">
            <a href="/projects/convertx/dashboard" class="<?= $currentView === 'dashboard' ? 'active' : '' ?>">
                <i class="fa-solid fa-gauge-high"></i> Dashboard
            </a>
            <a href="/projects/convertx/convert" class="<?= $currentView === 'convert' ? 'active' : '' ?>">
                <i class="fa-solid fa-arrow-right-arrow-left"></i> Convert File
            </a>
            <a href="/projects/convertx/batch" class="<?= $currentView === 'batch' ? 'active' : '' ?>">
                <i class="fa-solid fa-layer-group"></i> Batch Convert
            </a>
            <a href="/projects/convertx/history" class="<?= $currentView === 'history' ? 'active' : '' ?>">
                <i class="fa-solid fa-clock-rotate-left"></i> History
            </a>
            <a href="/projects/convertx/docs" class="<?= $currentView === 'docs' ? 'active' : '' ?>">
                <i class="fa-solid fa-book-open"></i> API Docs
            </a>
            <a href="/projects/convertx/plan" class="<?= $currentView === 'plan' ? 'active' : '' ?>">
                <i class="fa-solid fa-star"></i> Plans
            </a>
            <a href="/projects/convertx/settings" class="<?= $currentView === 'settings' ? 'active' : '' ?>">
                <i class="fa-solid fa-gear"></i> Settings
            </a>
        </div>
    </nav>

    <!-- Main content -->
    <div class="cx-main">
        <div class="cx-topbar">
            <h1><?= htmlspecialchars($title ?? 'ConvertX') ?></h1>
            <div class="user-info">
                <i class="fa-solid fa-user-circle"></i>
                <?= htmlspecialchars($user['name'] ?? $user['email'] ?? 'Guest') ?>
            </div>
        </div>

        <div class="cx-content">
<?php
// Render the current view partial
$viewFile = PROJECT_PATH . '/views/' . $currentView . '.php';
if (file_exists($viewFile)) {
    include $viewFile;
} else {
    echo '<p style="color:var(--cx-danger)">View not found: ' . htmlspecialchars($currentView) . '</p>';
}
?>
        </div>
    </div>
</body>
</html>
