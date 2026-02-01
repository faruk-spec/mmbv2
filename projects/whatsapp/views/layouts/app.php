<?php use Core\View; use Core\Security; use Core\Auth; ?>
<!DOCTYPE html>
<html lang="en" data-theme="dark" id="html-theme">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="WhatsApp API Automation - MyMultiBranch">
    <title><?= View::e($title ?? 'WhatsApp') ?> - MyMultiBranch</title>
    
    <!-- Include Universal Navbar Styles -->
    <link rel="stylesheet" href="/assets/css/navbar.css">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --whatsapp-green: #25D366;
            --whatsapp-dark: #075E54;
            --whatsapp-light: #128C7E;
            --transition: all 0.3s ease;
            --sidebar-width: 280px;
        }
        
        /* Dark theme */
        [data-theme="dark"] {
            --bg-primary: #0a0e0a;
            --bg-secondary: #0f140f;
            --bg-card: #141914;
            --text-primary: #e8eefc;
            --text-secondary: #8892a6;
            --border-color: rgba(37, 211, 102, 0.2);
            --shadow-glow: 0 0 20px rgba(37, 211, 102, 0.2);
            --hover-bg: rgba(37, 211, 102, 0.05);
        }
        
        /* Light theme */
        [data-theme="light"] {
            --bg-primary: #f5f7fa;
            --bg-secondary: #ffffff;
            --bg-card: #ffffff;
            --text-primary: #2c3e50;
            --text-secondary: #7f8c8d;
            --border-color: rgba(37, 211, 102, 0.3);
            --shadow-glow: 0 0 20px rgba(37, 211, 102, 0.1);
            --hover-bg: rgba(37, 211, 102, 0.08);
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
                radial-gradient(ellipse at 20% 0%, rgba(37, 211, 102, 0.1) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 100%, rgba(7, 94, 84, 0.1) 0%, transparent 50%);
            pointer-events: none;
            z-index: -1;
        }
        
        /* Layout */
        .whatsapp-container {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            background: rgba(15, 20, 15, 0.95);
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
            background: var(--whatsapp-green);
            border-radius: 3px;
        }
        
        .sidebar-header {
            padding: 25px 20px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .whatsapp-mobile-toggle {
            display: none;
            background: none;
            border: none;
            color: var(--whatsapp-green);
            font-size: 1.5rem;
            cursor: pointer;
            padding: 8px;
            transition: var(--transition);
        }
        
        .whatsapp-mobile-toggle:hover {
            color: #20BA58;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--whatsapp-green);
            text-decoration: none;
        }
        
        .logo i {
            font-size: 1.8rem;
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
            background: rgba(37, 211, 102, 0.1);
            color: var(--whatsapp-green);
        }
        
        .menu-link.active {
            background: rgba(37, 211, 102, 0.15);
            color: var(--whatsapp-green);
            border-left: 3px solid var(--whatsapp-green);
        }
        
        .menu-link i {
            width: 20px;
            font-size: 16px;
        }
        
        .menu-link .badge {
            margin-left: auto;
            background: #ff6b6b;
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 600;
        }
        
        /* Sidebar Overlay for Mobile */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 999;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .sidebar-overlay.active {
            display: block;
            opacity: 1;
        }
        
        /* Ensure sidebar is above universal navbar on mobile */
        @media (max-width: 768px) {
            .sidebar {
                z-index: 10000;
            }
        }
        
        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        
        /* Top Bar */
        .topbar {
            background: rgba(15, 20, 15, 0.95);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border-color);
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
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
            font-size: 1.5rem;
            cursor: pointer;
            padding: 5px;
        }
        
        .topbar-title h1 {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .topbar-title p {
            font-size: 0.9rem;
            color: var(--text-secondary);
        }
        
        .topbar-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .topbar-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: rgba(37, 211, 102, 0.1);
            border: 1px solid var(--whatsapp-green);
            border-radius: 8px;
            color: var(--whatsapp-green);
            text-decoration: none;
            font-size: 0.9rem;
            transition: var(--transition);
        }
        
        .topbar-btn:hover {
            background: rgba(37, 211, 102, 0.2);
        }
        
        .user-menu {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--whatsapp-green), var(--whatsapp-dark));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1rem;
        }
        
        /* Content */
        .content {
            flex: 1;
            padding: 30px;
        }
        
        /* Cards */
        .card {
            background: rgba(20, 25, 20, 0.8);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 20px;
        }
        
        .card-header {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .card-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--text-primary);
        }
        
        /* Grid System */
        .grid {
            display: grid;
            gap: 20px;
        }
        
        .grid-2 { grid-template-columns: repeat(2, 1fr); }
        .grid-3 { grid-template-columns: repeat(3, 1fr); }
        .grid-4 { grid-template-columns: repeat(4, 1fr); }
        
        /* Stat Cards */
        .stat-card {
            background: linear-gradient(135deg, rgba(20, 25, 20, 0.8), rgba(15, 20, 15, 0.8));
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 24px;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: radial-gradient(circle, var(--whatsapp-green) 0%, transparent 70%);
            opacity: 0.1;
        }
        
        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 5px;
            color: var(--whatsapp-green);
        }
        
        .stat-label {
            color: var(--text-secondary);
            font-size: 14px;
        }
        
        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            justify-content: center;
        }
        
        .btn-primary {
            background: var(--whatsapp-green);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(37, 211, 102, 0.3);
        }
        
        .btn-secondary {
            background: rgba(136, 146, 166, 0.1);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
        }
        
        .btn-secondary:hover {
            background: rgba(136, 146, 166, 0.2);
        }
        
        .btn-danger {
            background: #ff6b6b;
            color: white;
        }
        
        .btn-success {
            background: var(--whatsapp-green);
            color: white;
        }
        
        /* Forms */
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-primary);
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 16px;
            background: rgba(15, 20, 15, 0.5);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            color: var(--text-primary);
            font-family: inherit;
            font-size: 0.95rem;
            transition: var(--transition);
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--whatsapp-green);
            box-shadow: 0 0 0 3px rgba(37, 211, 102, 0.1);
        }
        
        textarea.form-control {
            resize: vertical;
            min-height: 120px;
        }
        
        select.form-control {
            cursor: pointer;
        }
        
        /* Tables */
        .table-container {
            overflow-x: auto;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .table th,
        .table td {
            padding: 12px 16px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }
        
        .table th {
            background: rgba(37, 211, 102, 0.1);
            color: var(--whatsapp-green);
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .table tr:hover {
            background: rgba(37, 211, 102, 0.05);
        }
        
        /* Badges */
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .badge-success {
            background: var(--whatsapp-green);
            color: white;
        }
        
        .badge-danger {
            background: #ff6b6b;
            color: white;
        }
        
        .badge-warning {
            background: #ffaa00;
            color: #000;
        }
        
        .badge-info {
            background: var(--whatsapp-light);
            color: white;
        }
        
        /* Alerts */
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid;
        }
        
        .alert-success {
            background: rgba(37, 211, 102, 0.1);
            border-color: var(--whatsapp-green);
            color: var(--whatsapp-green);
        }
        
        .alert-danger {
            background: rgba(255, 107, 107, 0.1);
            border-color: #ff6b6b;
            color: #ff6b6b;
        }
        
        .alert-warning {
            background: rgba(255, 170, 0, 0.1);
            border-color: #ffaa00;
            color: #ffaa00;
        }
        
        .alert-info {
            background: rgba(18, 140, 126, 0.1);
            border-color: var(--whatsapp-light);
            color: var(--whatsapp-light);
        }
        
        /* Utility Classes */
        .mb-1 { margin-bottom: 10px; }
        .mb-2 { margin-bottom: 20px; }
        .mb-3 { margin-bottom: 30px; }
        .mt-1 { margin-top: 10px; }
        .mt-2 { margin-top: 20px; }
        .mt-3 { margin-top: 30px; }
        
        .text-center { text-align: center; }
        .text-muted { color: var(--text-secondary); }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-secondary);
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.3;
        }
        
        .empty-state h3 {
            font-size: 1.3rem;
            margin-bottom: 10px;
        }
        
        /* Responsive Design */
        @media (max-width: 1024px) {
            .grid-4 {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            :root {
                --sidebar-width: 280px;
            }
            
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .whatsapp-mobile-toggle {
                display: block;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .mobile-menu-btn {
                display: block;
            }
            
            .topbar {
                padding: 15px 20px;
            }
            
            .content {
                padding: 20px 15px;
            }
            
            .grid-2,
            .grid-3,
            .grid-4 {
                grid-template-columns: 1fr;
            }
            
            .topbar-btn span {
                display: none;
            }
        }
        
        @media (max-width: 480px) {
            .topbar-title h1 {
                font-size: 1.2rem;
            }
            
            .card {
                padding: 16px;
            }
            
            .user-menu span {
                display: none;
            }
            
            .table th,
            .table td {
                padding: 8px 12px;
                font-size: 0.9rem;
            }
        }
    </style>
    
    <?php View::yield('styles'); ?>
</head>
<body>
    <!-- Universal Navbar -->
    <?php require_once BASE_PATH . '/views/layouts/navbar.php'; ?>
    
    <div class="whatsapp-container">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <a href="/projects/whatsapp" class="logo">
                    <i class="fab fa-whatsapp"></i>
                    <span>WhatsApp</span>
                </a>
                <button class="whatsapp-mobile-toggle" id="whatsappMobileToggle" aria-label="Toggle WhatsApp Menu">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            
            <nav class="sidebar-menu">
                <!-- Main -->
                <div class="menu-section">
                    <div class="menu-item">
                        <a href="/projects/whatsapp/dashboard" class="menu-link <?= ($_SERVER['REQUEST_URI'] ?? '') == '/projects/whatsapp/dashboard' || ($_SERVER['REQUEST_URI'] ?? '') == '/projects/whatsapp' ? 'active' : '' ?>">
                            <i class="fas fa-home"></i>
                            <span>Dashboard</span>
                        </a>
                    </div>
                </div>
                
                <!-- WhatsApp -->
                <div class="menu-section">
                    <div class="menu-section-title">WhatsApp</div>
                    
                    <div class="menu-item">
                        <a href="/projects/whatsapp/sessions" class="menu-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/projects/whatsapp/sessions') === 0 ? 'active' : '' ?>">
                            <i class="fas fa-mobile-alt"></i>
                            <span>Sessions</span>
                        </a>
                    </div>
                    
                    <div class="menu-item">
                        <a href="/projects/whatsapp/messages" class="menu-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/projects/whatsapp/messages') === 0 ? 'active' : '' ?>">
                            <i class="fas fa-comment-dots"></i>
                            <span>Messages</span>
                        </a>
                    </div>
                    
                    <div class="menu-item">
                        <a href="/projects/whatsapp/contacts" class="menu-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/projects/whatsapp/contacts') === 0 ? 'active' : '' ?>">
                            <i class="fas fa-address-book"></i>
                            <span>Contacts</span>
                        </a>
                    </div>
                </div>
                
                <!-- Configuration -->
                <div class="menu-section">
                    <div class="menu-section-title">Configuration</div>
                    
                    <div class="menu-item">
                        <a href="/projects/whatsapp/settings" class="menu-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/projects/whatsapp/settings') === 0 ? 'active' : '' ?>">
                            <i class="fas fa-cog"></i>
                            <span>Settings</span>
                        </a>
                    </div>
                    
                    <div class="menu-item">
                        <a href="/projects/whatsapp/api-docs" class="menu-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/projects/whatsapp/api-docs') === 0 ? 'active' : '' ?>">
                            <i class="fas fa-book"></i>
                            <span>API Docs</span>
                        </a>
                    </div>
                </div>
                
                <!-- Account -->
                <div class="menu-section">
                    <div class="menu-section-title">Account</div>
                    
                    <div class="menu-item">
                        <a href="/projects/whatsapp/subscription" class="menu-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/projects/whatsapp/subscription') === 0 ? 'active' : '' ?>">
                            <i class="fas fa-crown"></i>
                            <span>Subscription</span>
                        </a>
                    </div>
                </div>
                
                <!-- System -->
                <div class="menu-section">
                    <div class="menu-section-title">System</div>
                    
                    <div class="menu-item">
                        <a href="/dashboard" class="menu-link">
                            <i class="fas fa-th-large"></i>
                            <span>Main Dashboard</span>
                        </a>
                    </div>
                    
                    <div class="menu-item">
                        <a href="/logout" class="menu-link">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </a>
                    </div>
                </div>
            </nav>
        </aside>
        
        <!-- Sidebar Overlay for Mobile -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Content -->
            <div class="content">
                <?php View::yield('content'); ?>
            </div>
        </div>
    </div>
    
    <script>
        // Apply theme from localStorage
        (function() {
            const theme = localStorage.getItem('theme') || 'dark';
            document.getElementById('html-theme').setAttribute('data-theme', theme);
        })();
        
        // Mobile menu toggle for WhatsApp sidebar
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const whatsappMobileToggle = document.getElementById('whatsappMobileToggle');
        
        if (whatsappMobileToggle) {
            whatsappMobileToggle.addEventListener('click', (e) => {
                e.stopPropagation(); // Prevent event bubbling
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
    </script>
    
    <?php View::yield('scripts'); ?>
</body>
</html>
