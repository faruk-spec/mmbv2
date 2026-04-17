<?php use Core\View; use Core\Security; ?>
<?php
$defaultTheme = 'dark';
try {
    $db = \Core\Database::getInstance();
    $navbarSettings = $db->fetch("SELECT default_theme FROM navbar_settings WHERE id = 1");
    if ($navbarSettings && !empty($navbarSettings['default_theme'])) {
        $defaultTheme = $navbarSettings['default_theme'];
    }
} catch (\Exception $e) {}
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?= htmlspecialchars($defaultTheme) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= View::e($page['meta_description'] ?? '') ?>">
    <meta name="csrf-token" content="<?= Security::generateCsrfToken() ?>">
    <title><?= View::e($title ?? 'Page') ?> - <?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --bg-primary:#06060a;--bg-secondary:#0c0c12;--bg-card:#0f0f18;--cyan:#3b82f6;--magenta:#8b5cf6;--green:#22c55e;--orange:#f59e0b;--purple:#8b5cf6;--red:#ef4444;--text-primary:#e8eefc;--text-secondary:#8892a6;--border-color:rgba(255,255,255,0.1);--shadow:0 4px 20px rgba(0,0,0,0.3);--transition:all 0.3s ease; }
        [data-theme="light"]{--bg-primary:#f0f4ff;--bg-secondary:#ffffff;--bg-card:#ffffff;--cyan:#0369a1;--magenta:#c026d3;--green:#059669;--orange:#d97706;--purple:#7c3aed;--red:#dc2626;--text-primary:#1a1a1a;--text-secondary:#555555;--border-color:rgba(0,0,0,0.1);--shadow:0 4px 20px rgba(0,0,0,0.12);}
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:'Poppins',sans-serif;background:var(--bg-primary);color:var(--text-primary);min-height:100vh;line-height:1.6;font-size:14px;overflow-x:hidden;}
        a{color:var(--cyan);text-decoration:none;transition:var(--transition);}
        a:hover{color:var(--magenta);}
        .container{max-width:1200px;margin:0 auto;padding:0 20px;}
        .page-content{padding:40px 0;min-height:60vh;}
        .page-navbar{background:var(--bg-secondary);border-bottom:1px solid var(--border-color);padding:15px 0;}
        .page-navbar .nav-inner{display:flex;align-items:center;justify-content:space-between;}
        .page-navbar .brand{font-size:1.2rem;font-weight:600;color:var(--text-primary);}
        .page-footer{background:var(--bg-secondary);border-top:1px solid var(--border-color);padding:30px 0;text-align:center;color:var(--text-secondary);margin-top:auto;}
    </style>
</head>
<body>

<?php if ($show_navbar ?? true): ?>
<nav class="page-navbar">
    <div class="container">
        <div class="nav-inner">
            <a href="/" class="brand"><i class="fas fa-bolt"></i> <?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?></a>
            <div>
                <?php if (\Core\Auth::check()): ?>
                    <a href="/dashboard" style="color:var(--text-secondary);margin-right:15px;"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <?php else: ?>
                    <a href="/login" style="color:var(--text-secondary);margin-right:15px;">Login</a>
                    <a href="/register" style="color:var(--cyan);">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
<?php endif; ?>

<main class="page-content">
    <div class="container">
        <?php View::yield('content'); ?>
    </div>
</main>

<?php if ($show_footer ?? true): ?>
<footer class="page-footer">
    <div class="container">
        <p>&copy; <?= date('Y') ?> <?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?>. All rights reserved.</p>
    </div>
</footer>
<?php endif; ?>

</body>
</html>
