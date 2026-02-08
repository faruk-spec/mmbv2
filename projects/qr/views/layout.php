<!DOCTYPE html>
<?php
// Set theme from navbar settings
$defaultTheme = 'dark';
try {
    $db = \Core\Database::getInstance();
    $navbarSettings = $db->fetch("SELECT default_theme FROM navbar_settings WHERE id = 1");
    if ($navbarSettings && !empty($navbarSettings['default_theme'])) {
        $defaultTheme = $navbarSettings['default_theme'];
    }
} catch (\Exception $e) {
    // Use default if query fails
}
?>
<html lang="en" data-theme="<?= htmlspecialchars($defaultTheme) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'QR Generator') ?> - MyMultiBranch</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Universal Theme CSS -->
    <link rel="stylesheet" href="/css/universal-theme.css">
    
    <style>
        :root {
            --bg-primary: #06060a;
            --bg-secondary: #0c0c12;
            --bg-card: #0f0f18;
            --purple: #9945ff;
            --cyan: #00f0ff;
            --magenta: #ff2ec4;
            --green: #00ff88;
            --text-primary: #e8eefc;
            --text-secondary: #8892a6;
            --border-color: rgba(255, 255, 255, 0.1);
            --sidebar-width: 260px;
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
        
        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
        }
        
        /* Layout Structure */
        .qr-dashboard {
            display: flex;
            min-height: calc(100vh - 60px);
            margin-top: 60px; /* Offset for navbar */
        }
        
        /* Sidebar */
        .qr-sidebar {
            width: var(--sidebar-width);
            background: var(--bg-card);
            border-right: 1px solid var(--border-color);
            padding: 20px 0;
            position: fixed;
            left: 0;
            top: 60px;
            bottom: 0;
            overflow-y: auto;
            transition: transform 0.3s ease;
            z-index: 100;
        }
        
        .qr-sidebar.closed {
            transform: translateX(-100%);
        }
        
        .sidebar-section {
            padding: 10px 20px;
            margin-bottom: 20px;
        }
        
        .sidebar-title {
            font-size: 11px;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
            padding: 0 10px;
        }
        
        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: var(--text-secondary);
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 4px;
            transition: all 0.2s;
        }
        
        .sidebar-nav a:hover {
            background: var(--bg-secondary);
            color: var(--text-primary);
        }
        
        .sidebar-nav a.active {
            background: linear-gradient(135deg, var(--purple), var(--magenta));
            color: white;
        }
        
        .sidebar-nav svg {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }
        
        /* Main Content */
        .qr-main {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 30px;
            transition: margin-left 0.3s ease;
        }
        
        .qr-main.expanded {
            margin-left: 0;
        }
        
        /* Mobile Toggle Button */
        .sidebar-toggle {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--purple), var(--magenta));
            border: none;
            color: white;
            cursor: pointer;
            display: none;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(153, 69, 255, 0.4);
            z-index: 101;
            transition: transform 0.2s;
        }
        
        .sidebar-toggle:active {
            transform: scale(0.95);
        }
        
        .sidebar-toggle svg {
            width: 24px;
            height: 24px;
        }
        
        /* Card Styles */
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 24px;
            transition: all 0.3s;
        }
        
        .card:hover {
            border-color: rgba(0, 240, 255, 0.3);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 24px;
            border: none;
            border-radius: 10px;
            font-family: inherit;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            white-space: nowrap;
        }
        
        /* Better button appearance on desktop */
        @media (min-width: 769px) {
            .btn {
                padding: 14px 28px;
                font-size: 16px;
                border-radius: 12px;
                box-shadow: 0 3px 12px rgba(0, 0, 0, 0.12);
            }
            
            .btn-sm {
                padding: 10px 18px;
                font-size: 14px;
            }
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--purple), var(--magenta));
            color: white;
            border: none;
        }
        
        .btn-primary:hover:not(:disabled) {
            box-shadow: 0 6px 24px rgba(153, 69, 255, 0.5);
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: var(--bg-secondary);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
        }
        
        .btn-secondary:hover:not(:disabled) {
            background: var(--bg-tertiary);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #ff4757, #ff6b6b);
            color: white;
            border: none;
        }
        
        .btn-danger:hover:not(:disabled) {
            box-shadow: 0 6px 24px rgba(255, 71, 87, 0.5);
            transform: translateY(-2px);
        }
        
        .btn-sm {
            padding: 8px 16px;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-secondary);
            font-weight: 500;
            font-size: 14px;
        }
        
        .form-input, .form-select, .form-textarea {
            width: 100%;
            padding: 12px 16px;
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            color: var(--text-primary);
            font-family: inherit;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .form-input:focus, .form-select:focus, .form-textarea:focus {
            outline: none;
            border-color: var(--purple);
            box-shadow: 0 0 0 3px rgba(153, 69, 255, 0.1);
        }
        
        .form-actions {
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }
        
        .empty-icon {
            font-size: 64px;
            color: var(--purple);
            margin-bottom: 20px;
            opacity: 0.7;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: rgba(0, 255, 136, 0.1);
            border: 1px solid var(--green);
            color: var(--green);
        }
        
        .alert-error {
            background: rgba(255, 107, 107, 0.1);
            border: 1px solid #ff6b6b;
            color: #ff6b6b;
        }
        
        .grid {
            display: grid;
            gap: 20px;
        }
        
        .grid-2 { grid-template-columns: repeat(2, 1fr); }
        .grid-3 { grid-template-columns: repeat(3, 1fr); }
        .grid-4 { grid-template-columns: repeat(4, 1fr); }
        
        .stat-card {
            text-align: center;
            padding: 30px;
        }
        
        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--purple), var(--cyan));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .stat-label {
            color: var(--text-secondary);
            margin-top: 5px;
            font-size: 14px;
        }
        
        .qr-preview {
            background: white;
            padding: 20px;
            border-radius: 12px;
            display: inline-block;
        }
        
        .qr-preview img {
            display: block;
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--text-secondary);
            text-decoration: none;
            margin-bottom: 20px;
            transition: color 0.2s;
        }
        
        .back-link:hover {
            color: var(--purple);
        }
        
        /* Responsive Design */
        @media (max-width: 1024px) {
            .grid-3, .grid-4 {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .qr-sidebar {
                transform: translateX(-100%);
            }
            
            .qr-sidebar.open {
                transform: translateX(0);
            }
            
            .qr-main {
                margin-left: 0;
                padding: 20px 15px;
            }
            
            .sidebar-toggle {
                display: flex;
            }
            
            .grid-2, .grid-3, .grid-4 {
                grid-template-columns: 1fr;
            }
            
            .stat-value {
                font-size: 2rem;
            }
            
            .card, .glass-card {
                padding: 20px;
            }
            
            .section-title {
                font-size: 20px;
            }
            
            .form-input, .form-select, .form-textarea {
                font-size: 14px;
            }
        }
        
        @media (max-width: 480px) {
            .qr-main {
                padding: 15px 10px;
            }
            
            .btn:not(.btn-sm) {
                width: 100%;
                justify-content: center;
            }
            
            .stat-value {
                font-size: 1.8rem;
            }
            
            .glass-card {
                padding: 15px;
            }
            
            .section-title {
                font-size: 18px;
            }
        }
        }
        
        /* Overlay for mobile sidebar */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 60px;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 99;
        }
        
        .sidebar-overlay.active {
            display: block;
        }
    </style>
</head>
<body>
    <?php include BASE_PATH . '/views/layouts/navbar.php'; ?>
    
    <div class="qr-dashboard">
        <!-- Sidebar -->
        <aside class="qr-sidebar" id="qrSidebar">
            <div class="sidebar-section">
                <div class="sidebar-title">Main</div>
                <nav class="sidebar-nav">
                    <a href="/projects/qr" class="<?= $_SERVER['REQUEST_URI'] == '/projects/qr' || $_SERVER['REQUEST_URI'] == '/projects/qr/' || strpos($_SERVER['REQUEST_URI'], '/projects/qr/dashboard') !== false ? 'active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="7" height="7"/>
                            <rect x="14" y="3" width="7" height="7"/>
                            <rect x="14" y="14" width="7" height="7"/>
                            <rect x="3" y="14" width="7" height="7"/>
                        </svg>
                        Dashboard
                    </a>
                    <a href="/projects/qr/generate" class="<?= strpos($_SERVER['REQUEST_URI'], '/generate') !== false ? 'active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="8" x2="12" y2="16"/>
                            <line x1="8" y1="12" x2="16" y2="12"/>
                        </svg>
                        Create QR
                    </a>
                    <a href="/projects/qr/history" class="<?= strpos($_SERVER['REQUEST_URI'], '/history') !== false ? 'active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="18" height="18" rx="2"/>
                            <line x1="9" y1="9" x2="15" y2="9"/>
                            <line x1="9" y1="15" x2="15" y2="15"/>
                        </svg>
                        My QR Codes
                    </a>
                </nav>
            </div>
            
            <div class="sidebar-section">
                <div class="sidebar-title">Advanced</div>
                <nav class="sidebar-nav">
                    <a href="/projects/qr/analytics" class="<?= strpos($_SERVER['REQUEST_URI'], '/analytics') !== false ? 'active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 3v18h18"/>
                            <path d="M18 17l-5-5-5 5-5-5"/>
                        </svg>
                        Analytics
                    </a>
                    <a href="/projects/qr/campaigns" class="<?= strpos($_SERVER['REQUEST_URI'], '/campaigns') !== false ? 'active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                            <polyline points="22,6 12,13 2,6"/>
                        </svg>
                        Campaigns
                    </a>
                    <a href="/projects/qr/bulk" class="<?= strpos($_SERVER['REQUEST_URI'], '/bulk') !== false ? 'active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <line x1="12" y1="18" x2="12" y2="12"/>
                            <line x1="9" y1="15" x2="15" y2="15"/>
                        </svg>
                        Bulk Generate
                    </a>
                </nav>
            </div>
            
            <div class="sidebar-section">
                <div class="sidebar-title">Settings</div>
                <nav class="sidebar-nav">
                    <a href="/projects/qr/templates" class="<?= strpos($_SERVER['REQUEST_URI'], '/templates') !== false ? 'active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                            <line x1="9" y1="3" x2="9" y2="21"/>
                        </svg>
                        Templates
                    </a>
                    <a href="/projects/qr/settings" class="<?= strpos($_SERVER['REQUEST_URI'], '/settings') !== false ? 'active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="3"/>
                            <path d="M12 1v6m0 6v6M5.64 5.64l4.24 4.24m4.24 4.24l4.24 4.24M1 12h6m6 0h6M5.64 18.36l4.24-4.24m4.24-4.24l4.24-4.24"/>
                        </svg>
                        Settings
                    </a>
                </nav>
            </div>
        </aside>
        
        <!-- Sidebar Overlay -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>
        
        <!-- Main Content -->
        <main class="qr-main" id="qrMain">
            <?php
            $flash = $_SESSION['_flash'] ?? [];
            unset($_SESSION['_flash']);
            
            if (!empty($flash['success'])): ?>
                <div class="alert alert-success"><?= htmlspecialchars($flash['success']) ?></div>
            <?php endif; ?>
            
            <?php if (!empty($flash['error'])): ?>
                <div class="alert alert-error"><?= htmlspecialchars($flash['error']) ?></div>
            <?php endif; ?>
            
            <?= $content ?>
        </main>
        
        <!-- Mobile Sidebar Toggle -->
        <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="3" y1="12" x2="21" y2="12"/>
                <line x1="3" y1="6" x2="21" y2="6"/>
                <line x1="3" y1="18" x2="21" y2="18"/>
            </svg>
        </button>
    </div>
    
    <!-- QR Code Styling Library -->
    <script src="https://unpkg.com/qr-code-styling@1.6.0-rc.1/lib/qr-code-styling.js"></script>
    
    <script>
    // Sidebar toggle for mobile
    (function() {
        const sidebar = document.getElementById('qrSidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const toggle = document.getElementById('sidebarToggle');
        const main = document.getElementById('qrMain');
        
        function toggleSidebar() {
            sidebar.classList.toggle('open');
            overlay.classList.toggle('active');
        }
        
        function closeSidebar() {
            sidebar.classList.remove('open');
            overlay.classList.remove('active');
        }
        
        if (toggle) {
            toggle.addEventListener('click', toggleSidebar);
        }
        
        if (overlay) {
            overlay.addEventListener('click', closeSidebar);
        }
        
        // Close sidebar when clicking nav link on mobile
        const navLinks = sidebar.querySelectorAll('.sidebar-nav a');
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 768) {
                    closeSidebar();
                }
            });
        });
        
        // Handle window resize
        let resizeTimer;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => {
                if (window.innerWidth > 768) {
                    closeSidebar();
                }
            }, 250);
        });
    })();
    </script>
</body>
</html>
