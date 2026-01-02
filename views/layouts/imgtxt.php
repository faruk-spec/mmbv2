<?php use Core\View; use Core\Security; ?>
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
    <meta name="description" content="ImgTxt - OCR Tool">
    <title><?= View::e($title ?? 'ImgTxt') ?> - <?= APP_NAME ?></title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Universal Theme CSS -->
    <link rel="stylesheet" href="/css/universal-theme.css">
    
    <!-- Mobile Responsive CSS -->
    <link rel="stylesheet" href="/css/imgtxt-mobile.css">
    
    <?php View::yield('styles'); ?>
    
    <style>
        :root {
            --bg-primary: #06060a;
            --bg-secondary: #0c0c12;
            --bg-card: #0f0f18;
            --cyan: #00f0ff;
            --magenta: #ff2ec4;
            --green: #00ff88;
            --orange: #ffaa00;
            --purple: #9945ff;
            --red: #ff6b6b;
            --text-primary: #e8eefc;
            --text-secondary: #8892a6;
            --border-color: rgba(255, 255, 255, 0.1);
            --shadow-glow: 0 0 20px rgba(0, 240, 255, 0.2);
            --transition: all 0.3s ease;
            --sidebar-width: 280px;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            line-height: 1.6;
            font-size: 14px;
        }
        
        h1 { font-size: 1.75rem; }
        h2 { font-size: 1.4rem; }
        h3 { font-size: 1.15rem; }
        h4 { font-size: 1rem; }
            color: var(--text-primary);
            min-height: 100vh;
            line-height: 1.6;
        }
        
        /* Background Effects */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(ellipse at 20% 0%, rgba(0, 240, 255, 0.1) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 100%, rgba(0, 255, 136, 0.1) 0%, transparent 50%);
            pointer-events: none;
            z-index: -1;
        }
        
        /* Admin Layout */
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            background: rgba(12, 12, 18, 0.95);
            backdrop-filter: blur(20px);
            border-right: 1px solid var(--border-color);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            overflow-x: hidden;
            z-index: 1000;
            transition: var(--transition);
        }
        
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }
        
        .sidebar::-webkit-scrollbar-track {
            background: var(--bg-secondary);
        }
        
        .sidebar::-webkit-scrollbar-thumb {
            background: var(--green);
            border-radius: 3px;
        }
        
        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .sidebar-logo {
            font-size: 1.4rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--green), var(--cyan));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .sidebar-menu {
            padding: 20px 0;
        }
        
        .menu-section {
            margin-bottom: 25px;
        }
        
        .menu-section-title {
            padding: 0 20px 10px;
            font-size: 11px;
            text-transform: uppercase;
            color: var(--text-secondary);
            font-weight: 600;
            letter-spacing: 1px;
        }
        
        .menu-item {
            position: relative;
        }
        
        .menu-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            color: var(--text-secondary);
            text-decoration: none;
            transition: var(--transition);
            position: relative;
        }
        
        .menu-link:hover {
            background: rgba(0, 255, 136, 0.05);
            color: var(--green);
        }
        
        .menu-link.active {
            background: rgba(0, 255, 136, 0.1);
            color: var(--green);
            border-left: 3px solid var(--green);
        }
        
        .menu-link i {
            width: 20px;
            font-size: 16px;
        }
        
        .menu-link .badge {
            margin-left: auto;
            background: var(--green);
            color: var(--bg-primary);
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 600;
        }
        
        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        /* Top Bar */
        .topbar {
            background: rgba(12, 12, 18, 0.95);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border-color);
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 999;
        }
        
        .topbar-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            color: var(--text-primary);
            font-size: 24px;
            cursor: pointer;
        }
        
        .topbar-title h1 {
            font-size: 1.5rem;
            font-weight: 600;
        }
        
        .topbar-title p {
            font-size: 13px;
            color: var(--text-secondary);
        }
        
        .topbar-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .topbar-btn {
            background: none;
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }
        
        .topbar-btn:hover {
            background: rgba(0, 255, 136, 0.1);
            border-color: var(--green);
            color: var(--green);
        }
        
        .user-menu {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 12px;
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            cursor: pointer;
        }
        
        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--green), var(--cyan));
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
        }
        
        /* Content Area */
        .content {
            flex: 1;
            padding: 30px;
        }
        
        /* Cards */
        .card {
            background: rgba(15, 15, 24, 0.95);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            transition: var(--transition);
        }
        
        .card:hover {
            border-color: rgba(0, 255, 136, 0.3);
            box-shadow: 0 4px 20px rgba(0, 255, 136, 0.1);
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .card-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--text-primary);
        }
        
        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border: 1px solid var(--border-color);
            background: var(--bg-card);
            color: var(--text-primary);
            border-radius: 8px;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }
        
        .btn:hover {
            background: rgba(0, 255, 136, 0.1);
            border-color: var(--green);
            color: var(--green);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--green), var(--cyan));
            border-color: transparent;
            color: var(--bg-primary);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-glow);
        }
        
        .btn-secondary {
            background: var(--bg-secondary);
            border-color: var(--border-color);
        }
        
        .btn-sm {
            padding: 6px 12px;
            font-size: 13px;
        }
        
        /* Tables */
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .table thead {
            background: var(--bg-secondary);
        }
        
        .table th,
        .table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }
        
        .table th {
            color: var(--green);
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .table tr:hover {
            background: rgba(0, 255, 136, 0.05);
        }
        
        /* Badges */
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-success {
            background: rgba(0, 255, 136, 0.2);
            color: var(--green);
        }
        
        .badge-warning {
            background: rgba(255, 170, 0, 0.2);
            color: var(--orange);
        }
        
        .badge-danger {
            background: rgba(255, 107, 107, 0.2);
            color: var(--red);
        }
        
        .badge-info {
            background: rgba(0, 240, 255, 0.2);
            color: var(--cyan);
        }
        
        /* Grid */
        .grid {
            display: grid;
            gap: 20px;
        }
        
        .grid-2 { grid-template-columns: repeat(2, 1fr); }
        .grid-3 { grid-template-columns: repeat(3, 1fr); }
        .grid-4 { grid-template-columns: repeat(4, 1fr); }
        
        /* Utility Classes */
        .mb-1 { margin-bottom: 10px; }
        .mb-2 { margin-bottom: 15px; }
        .mb-3 { margin-bottom: 20px; }
        .mt-1 { margin-top: 10px; }
        .mt-2 { margin-top: 15px; }
        .mt-3 { margin-top: 20px; }
        
        /* Forms */
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-primary);
            font-weight: 500;
            font-size: 14px;
        }
        
        .form-control {
            width: 100%;
            padding: 10px 15px;
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            color: var(--text-primary);
            font-size: 14px;
            transition: var(--transition);
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--green);
            box-shadow: 0 0 0 3px rgba(0, 255, 136, 0.1);
        }
        
        /* Dropdown Menu Styles */
        .dropdown {
            position: relative;
        }
        
        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            min-width: 200px;
            margin-top: 8px;
            padding: 8px 0;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            display: none;
            z-index: 1000;
        }
        
        .dropdown.active .dropdown-menu {
            display: block;
        }
        
        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 16px;
            color: var(--text-primary);
            text-decoration: none;
            transition: var(--transition);
            font-size: 14px;
        }
        
        .dropdown-item:hover {
            background: rgba(0, 255, 136, 0.1);
            color: var(--green);
        }
        
        /* Sidebar Overlay for Mobile */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .sidebar-overlay.active {
            display: block;
            opacity: 1;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .mobile-menu-btn {
                display: block;
            }
            
            .grid-2,
            .grid-3,
            .grid-4 {
                grid-template-columns: 1fr;
            }
            
            .content {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <?php include BASE_PATH . '/views/layouts/navbar.php'; ?>
    
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <i class="fas fa-image"></i>
                    <span>ImgTxt OCR</span>
                </div>
            </div>
            
            <nav class="sidebar-menu">
                <div class="menu-section">
                    <div class="menu-section-title">Main</div>
                    
                    <div class="menu-item">
                        <a href="/projects/imgtxt/dashboard" class="menu-link <?= ($currentPage ?? '') === 'dashboard' ? 'active' : '' ?>">
                            <i class="fas fa-home"></i>
                            <span>Dashboard</span>
                        </a>
                    </div>
                    
                    <div class="menu-item">
                        <a href="/projects/imgtxt/upload" class="menu-link <?= ($currentPage ?? '') === 'upload' ? 'active' : '' ?>">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <span>Upload & OCR</span>
                        </a>
                    </div>
                </div>
                
                <div class="menu-section">
                    <div class="menu-section-title">Processing</div>
                    
                    <div class="menu-item">
                        <a href="/projects/imgtxt/batch" class="menu-link <?= ($currentPage ?? '') === 'batch' ? 'active' : '' ?>">
                            <i class="fas fa-layer-group"></i>
                            <span>Batch Processing</span>
                        </a>
                    </div>
                    
                    <div class="menu-item">
                        <a href="/projects/imgtxt/history" class="menu-link <?= ($currentPage ?? '') === 'history' ? 'active' : '' ?>">
                            <i class="fas fa-history"></i>
                            <span>History</span>
                        </a>
                    </div>
                </div>
                
                <div class="menu-section">
                    <div class="menu-section-title">Configuration</div>
                    
                    <div class="menu-item">
                        <a href="/projects/imgtxt/settings" class="menu-link <?= ($currentPage ?? '') === 'settings' ? 'active' : '' ?>">
                            <i class="fas fa-cog"></i>
                            <span>Settings</span>
                        </a>
                    </div>
                </div>
                
                <div class="menu-section">
                    <div class="menu-section-title">Navigation</div>
                    
                    <div class="menu-item">
                        <a href="/dashboard" class="menu-link">
                            <i class="fas fa-arrow-left"></i>
                            <span>Main Dashboard</span>
                        </a>
                    </div>
                    
                    <?php if (isset($user) && ($user['role'] ?? '') === 'admin'): ?>
                    <div class="menu-item">
                        <a href="/admin" class="menu-link">
                            <i class="fas fa-user-shield"></i>
                            <span>Admin Panel</span>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </nav>
        </aside>
        
        <!-- Sidebar Overlay for Mobile -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Bar -->
            <div class="topbar">
                <div class="topbar-left">
                    <button class="mobile-menu-btn" id="mobileMenuBtn">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="topbar-title">
                        <h1><?= $title ?? 'ImgTxt' ?></h1>
                        <?php if (isset($subtitle)): ?>
                            <p><?= $subtitle ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="topbar-right">
                    <a href="/projects/imgtxt/upload" class="topbar-btn btn-primary">
                        <i class="fas fa-plus"></i>
                        <span>New OCR</span>
                    </a>
                    
                    <!-- Projects Dropdown -->
                    <div class="dropdown nav-item" style="display: inline-block; position: relative;">
                        <button class="topbar-btn dropdown-toggle">
                            <i class="fas fa-th"></i>
                            <span>Projects</span>
                        </button>
                        <div class="dropdown-menu">
                            <?php 
                            $projects = require BASE_PATH . '/config/projects.php';
                            foreach ($projects as $key => $project): 
                            ?>
                                <a href="<?= $project['url'] ?>" class="dropdown-item">
                                    <div class="project-icon" style="background: <?= $project['color'] ?>20; color: <?= $project['color'] ?>; width: 24px; height: 24px; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 600;">
                                        <?= strtoupper(substr($project['name'], 0, 1)) ?>
                                    </div>
                                    <?= $project['name'] ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <a href="/" class="topbar-btn">
                        <i class="fas fa-home"></i>
                        <span>Home</span>
                    </a>
                    
                    <!-- Theme Toggle -->
                    <button class="topbar-btn" id="themeToggle" aria-label="Toggle theme">
                        <i id="themeIcon" class="fas fa-moon"></i>
                        <span id="themeText">Light</span>
                    </button>
                    
                    <div class="user-menu">
                        <div class="user-avatar">
                            <?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?>
                        </div>
                        <span><?= $user['name'] ?? 'User' ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Content -->
            <div class="content">
                <?php View::yield('content'); ?>
            </div>
        </div>
    </div>
    
    <script>
        // Mobile menu toggle
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        
        if (mobileMenuBtn) {
            mobileMenuBtn.addEventListener('click', () => {
                sidebar.classList.toggle('active');
                sidebarOverlay.classList.toggle('active');
            });
        }
        
        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', () => {
                sidebar.classList.remove('active');
                sidebarOverlay.classList.remove('active');
            });
        }
        
        // Theme Toggle
        const themeToggle = document.getElementById('themeToggle');
        const themeIcon = document.getElementById('themeIcon');
        const themeText = document.getElementById('themeText');
        const html = document.documentElement;
        
        // Load saved theme
        const savedTheme = localStorage.getItem('theme') || 'dark';
        html.setAttribute('data-theme', savedTheme);
        updateThemeUI(savedTheme);
        
        if (themeToggle) {
            themeToggle.addEventListener('click', () => {
                const currentTheme = html.getAttribute('data-theme');
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                html.setAttribute('data-theme', newTheme);
                localStorage.setItem('theme', newTheme);
                updateThemeUI(newTheme);
            });
        }
        
        function updateThemeUI(theme) {
            if (!themeIcon || !themeText) return;
            if (theme === 'light') {
                themeIcon.className = 'fas fa-sun';
                themeText.textContent = 'Dark';
            } else {
                themeIcon.className = 'fas fa-moon';
                themeText.textContent = 'Light';
            }
        }
        
        // Dropdown functionality
        const dropdowns = document.querySelectorAll('.dropdown');
        dropdowns.forEach(dropdown => {
            const toggle = dropdown.querySelector('.dropdown-toggle');
            if (toggle) {
                toggle.addEventListener('click', (e) => {
                    e.stopPropagation();
                    // Close other dropdowns
                    dropdowns.forEach(d => {
                        if (d !== dropdown) d.classList.remove('active');
                    });
                    dropdown.classList.toggle('active');
                });
            }
        });
        
        // Close dropdowns when clicking outside
        document.addEventListener('click', () => {
            dropdowns.forEach(d => d.classList.remove('active'));
        });
    </script>
    
    <?php View::yield('scripts'); ?>
</body>
</html>
