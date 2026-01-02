<?php 
use Core\View; 
use Core\Auth;
$user = Auth::user();

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
<!DOCTYPE html>
<html lang="en" data-theme="<?= htmlspecialchars($defaultTheme) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= View::e($title ?? 'CodeXPro') ?> - <?= APP_NAME ?></title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Universal Theme CSS -->
    <link rel="stylesheet" href="/css/universal-theme.css">
    
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
                radial-gradient(ellipse at 80% 100%, rgba(255, 46, 196, 0.1) 0%, transparent 50%);
            pointer-events: none;
            z-index: -1;
        }
        
        /* Layout */
        .project-container {
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
            background: var(--cyan);
            border-radius: 3px;
        }
        
        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .sidebar-logo {
            font-size: 1.4rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--cyan), var(--magenta));
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
            background: rgba(0, 240, 255, 0.05);
            color: var(--cyan);
        }
        
        .menu-link.active {
            background: rgba(0, 240, 255, 0.1);
            color: var(--cyan);
            border-left: 3px solid var(--cyan);
        }
        
        .menu-link i {
            width: 20px;
            font-size: 16px;
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
            padding: 0 30px;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 999;
        }
        
        .topbar-left h1 {
            font-size: 1.5rem;
            font-weight: 600;
            background: linear-gradient(135deg, var(--cyan), var(--magenta));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .topbar-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        /* Mobile Menu Toggle */
        .mobile-menu-toggle {
            display: none;
            background: none;
            border: none;
            color: var(--cyan);
            font-size: 1.5rem;
            cursor: pointer;
            padding: 8px;
            z-index: 1001;
        }
        
        .mobile-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 999;
        }
        
        .user-menu {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 16px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50px;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .user-menu:hover {
            background: rgba(0, 240, 255, 0.1);
        }
        
        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--cyan), var(--purple));
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
        }
        
        .user-name {
            font-size: 14px;
            font-weight: 500;
        }
        
        /* Content Area */
        .content-area {
            flex: 1;
            padding: 30px;
        }
        
        /* Cards */
        .card {
            background: rgba(15, 15, 24, 0.8);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .card-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--text-primary);
        }
        
        /* Buttons */
        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            border: none;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--cyan), var(--purple));
            color: var(--bg-primary);
        }
        
        .btn-primary:hover {
            box-shadow: var(--shadow-glow);
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: rgba(255, 255, 255, 0.05);
            color: var(--text-primary);
        }
        
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: rgba(15, 15, 24, 0.8);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 16px;
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
        
        .stat-icon.cyan { background: rgba(0, 240, 255, 0.1); color: var(--cyan); }
        .stat-icon.magenta { background: rgba(255, 46, 196, 0.1); color: var(--magenta); }
        .stat-icon.green { background: rgba(0, 255, 136, 0.1); color: var(--green); }
        .stat-icon.orange { background: rgba(255, 170, 0, 0.1); color: var(--orange); }
        
        .stat-info h3 {
            font-size: 28px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 4px;
        }
        
        .stat-info p {
            font-size: 13px;
            color: var(--text-secondary);
        }
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5);
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 24px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .modal-header h2 {
            font-size: 1.5rem;
            color: var(--cyan);
        }
        
        .modal-close {
            font-size: 28px;
            color: var(--text-secondary);
            cursor: pointer;
            transition: var(--transition);
        }
        
        .modal-close:hover {
            color: var(--text-primary);
        }
        
        .modal-content form {
            padding: 24px;
        }
        
        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            padding-top: 16px;
            margin-top: 16px;
            border-top: 1px solid var(--border-color);
        }
        
        /* Badges */
        .badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-success { background: rgba(0, 255, 136, 0.2); color: var(--green); }
        .badge-secondary { background: rgba(136, 146, 166, 0.2); color: var(--text-secondary); }
        .badge-javascript { background: rgba(247, 223, 30, 0.2); color: #f7df1e; }
        .badge-python { background: rgba(55, 118, 171, 0.2); color: #3776ab; }
        .badge-php { background: rgba(119, 123, 180, 0.2); color: #777bb4; }
        .badge-html { background: rgba(227, 76, 38, 0.2); color: #e34c26; }
        .badge-css { background: rgba(38, 77, 228, 0.2); color: #264de4; }
        
        /* Content Grid */
        .content-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 24px;
        }
        
        .card-body {
            margin-bottom: 16px;
        }
        
        .card-body p {
            color: var(--text-secondary);
            line-height: 1.6;
        }
        
        .card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid var(--border-color);
        }
        
        .card-meta {
            display: flex;
            gap: 12px;
            align-items: center;
            font-size: 13px;
            color: var(--text-secondary);
        }
        
        .card-actions {
            display: flex;
            gap: 8px;
        }
        
        .btn-sm {
            padding: 6px 14px;
            font-size: 14px;
        }
        
        .btn-block {
            width: 100%;
            justify-content: center;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            grid-column: 1 / -1;
        }
        
        .empty-state h3 {
            font-size: 1.5rem;
            color: var(--text-primary);
            margin-bottom: 12px;
        }
        
        .empty-state p {
            color: var(--text-secondary);
            margin-bottom: 24px;
        }
        
        /* Page Header */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .page-header h1 {
            font-size: 2rem;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .page-header h1 i {
            color: var(--cyan);
        }
        
        /* Delete Modal */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .modal-overlay.show {
            opacity: 1;
        }
        
        .modal-dialog {
            background: var(--bg-card);
            border-radius: 12px;
            border: 1px solid var(--border-color);
            max-width: 500px;
            width: 90%;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
            transform: scale(0.9);
            transition: transform 0.3s ease;
        }
        
        .modal-overlay.show .modal-dialog {
            transform: scale(1);
        }
        
        .modal-dialog.delete-modal .modal-header {
            background: linear-gradient(135deg, rgba(255, 107, 107, 0.1), rgba(255, 46, 196, 0.1));
            padding: 24px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 16px;
        }
        
        .modal-dialog.delete-modal .modal-header i {
            font-size: 32px;
            color: var(--red);
        }
        
        .modal-dialog.delete-modal .modal-header h3 {
            font-size: 1.5rem;
            color: var(--text-primary);
            margin: 0;
        }
        
        .modal-dialog .modal-body {
            padding: 24px;
        }
        
        .modal-dialog .modal-body p {
            color: var(--text-secondary);
            line-height: 1.6;
            margin: 0;
        }
        
        .modal-dialog .modal-footer {
            padding: 16px 24px;
            border-top: 1px solid var(--border-color);
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }
        
        /* Quick Edit Panel */
        .quick-edit-panel {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            opacity: 0;
            max-height: 0;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .quick-edit-panel.show {
            opacity: 1;
            max-height: 600px;
        }
        
        .quick-edit-content h3 {
            color: var(--cyan);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .quick-edit-form .form-group {
            margin-bottom: 20px;
        }
        
        .quick-edit-form label {
            display: block;
            color: var(--text-primary);
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .quick-edit-form .form-control {
            width: 100%;
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            padding: 12px;
            border-radius: 8px;
            font-family: inherit;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        .quick-edit-form .form-control:focus {
            outline: none;
            border-color: var(--cyan);
        }
        
        .quick-edit-form textarea.form-control {
            resize: vertical;
            min-height: 80px;
        }
        
        /* Toggle Switch */
        .toggle-label {
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            user-select: none;
        }
        
        .toggle-label input[type="checkbox"] {
            display: none;
        }
        
        .toggle-slider {
            position: relative;
            width: 50px;
            height: 26px;
            background: #333;
            border-radius: 26px;
            transition: background 0.3s;
        }
        
        .toggle-slider:before {
            content: '';
            position: absolute;
            top: 3px;
            left: 3px;
            width: 20px;
            height: 20px;
            background: #fff;
            border-radius: 50%;
            transition: transform 0.3s;
        }
        
        .toggle-label input[type="checkbox"]:checked + .toggle-slider {
            background: var(--cyan);
        }
        
        .toggle-label input[type="checkbox"]:checked + .toggle-slider:before {
            transform: translateX(24px);
        }
        
        .toggle-text {
            color: var(--text-primary);
            font-weight: 500;
        }
        
        .form-actions {
            display: flex;
            gap: 12px;
            margin-top: 24px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .mobile-menu-toggle {
                display: block;
            }
            
            .sidebar {
                transform: translateX(-100%);
                z-index: 1000;
            }
            
            .sidebar.mobile-open {
                transform: translateX(0);
            }
            
            .mobile-overlay.active {
                display: block;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .content-grid {
                grid-template-columns: 1fr;
            }
            
            .topbar {
                padding: 0 15px;
            }
            
            .page-content {
                padding: 20px 15px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .btn {
                padding: 8px 12px;
                font-size: 0.9rem;
            }
        }
        
        @media (max-width: 480px) {
            .topbar-left h1 {
                font-size: 1.2rem;
            }
            
            .sidebar-logo {
                font-size: 1.2rem;
            }
        }
    </style>
</head>
<body>
    <?php include BASE_PATH . '/views/layouts/navbar.php'; ?>
    
    <div class="project-container">
        <!-- Mobile Overlay -->
        <div class="mobile-overlay" id="mobileOverlay"></div>
        
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <i class="fas fa-code"></i>
                    <span>CodeXPro</span>
                </div>
            </div>
            
            <nav class="sidebar-menu">
                <div class="menu-section">
                    <div class="menu-section-title">Main</div>
                    <a href="/projects/codexpro" class="menu-link <?= ($currentPage ?? '') === 'dashboard' ? 'active' : '' ?>">
                        <i class="fas fa-th-large"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="/projects/codexpro/editor" class="menu-link <?= ($currentPage ?? '') === 'editor' ? 'active' : '' ?>">
                        <i class="fas fa-edit"></i>
                        <span>Code Editor</span>
                    </a>
                </div>
                
                <div class="menu-section">
                    <div class="menu-section-title">Management</div>
                    <a href="/projects/codexpro/projects" class="menu-link <?= ($currentPage ?? '') === 'projects' ? 'active' : '' ?>">
                        <i class="fas fa-folder"></i>
                        <span>Projects</span>
                    </a>
                    <a href="/projects/codexpro/snippets" class="menu-link <?= ($currentPage ?? '') === 'snippets' ? 'active' : '' ?>">
                        <i class="fas fa-code"></i>
                        <span>Snippets</span>
                    </a>
                    <a href="/projects/codexpro/templates" class="menu-link <?= ($currentPage ?? '') === 'templates' ? 'active' : '' ?>">
                        <i class="fas fa-file-code"></i>
                        <span>Templates</span>
                    </a>
                </div>
                
                <div class="menu-section">
                    <div class="menu-section-title">Settings</div>
                    <a href="/projects/codexpro/settings" class="menu-link <?= ($currentPage ?? '') === 'settings' ? 'active' : '' ?>">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                    <a href="/dashboard" class="menu-link">
                        <i class="fas fa-arrow-left"></i>
                        <span>Back to Main</span>
                    </a>
                </div>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Bar -->
            <div class="topbar">
                <div class="topbar-left">
                    <button class="mobile-menu-toggle" id="mobileMenuToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1><?= View::e($pageTitle ?? 'CodeXPro') ?></h1>
                </div>
                <div class="topbar-right">
                    <div class="user-menu">
                        <div class="user-avatar"><?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?></div>
                        <span class="user-name"><?= View::e($user['name'] ?? 'User') ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Content -->
            <div class="content-area">
                <?= $content ?? '' ?>
            </div>
        </main>
    </div>
    
    <!-- Toast Notifications -->
    <div id="toastContainer" style="position: fixed; top: 20px; right: 20px; z-index: 10000;"></div>
    
    <script>
    // Toast Notification System
    function showNotification(message, type = 'success') {
        const container = document.getElementById('toastContainer');
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.style.cssText = `
            background: ${type === 'success' ? '#10b981' : '#ef4444'};
            color: white;
            padding: 16px 24px;
            border-radius: 8px;
            margin-bottom: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            animation: slideIn 0.3s ease-out;
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 300px;
        `;
        
        const icon = type === 'success' ? '✓' : '✗';
        toast.innerHTML = `<span style="font-size: 20px; font-weight: bold;">${icon}</span><span>${message}</span>`;
        
        container.appendChild(toast);
        
        setTimeout(() => {
            toast.style.animation = 'slideOut 0.3s ease-out';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
    
    // Delete Modal System
    function showDeleteModal(title, message, onConfirm) {
        // Create modal overlay
        const overlay = document.createElement('div');
        overlay.id = 'deleteModalOverlay';
        overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            animation: fadeIn 0.3s ease-out;
        `;
        
        // Create modal
        const modal = document.createElement('div');
        modal.style.cssText = `
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            border: 2px solid #00f0ff;
            border-radius: 16px;
            padding: 30px;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 8px 32px rgba(0, 240, 255, 0.2);
            animation: scaleIn 0.3s ease-out;
        `;
        
        modal.innerHTML = `
            <div style="text-align: center;">
                <i class="fas fa-exclamation-triangle" style="font-size: 48px; color: #ff4757; margin-bottom: 20px;"></i>
                <h3 style="color: #fff; font-size: 24px; margin-bottom: 12px;">${title}</h3>
                <p style="color: #8892a6; margin-bottom: 30px;">${message}</p>
                <div style="display: flex; gap: 12px; justify-content: center;">
                    <button onclick="closeDeleteModal()" style="background: #333; color: #fff; border: none; padding: 12px 32px; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 16px; transition: all 0.3s;">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button id="confirmDeleteBtn" style="background: linear-gradient(135deg, #ff4757 0%, #ff3838 100%); color: #fff; border: none; padding: 12px 32px; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 16px; transition: all 0.3s;">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </div>
            </div>
        `;
        
        overlay.appendChild(modal);
        document.body.appendChild(overlay);
        
        // Add event listeners
        document.getElementById('confirmDeleteBtn').onclick = () => {
            closeDeleteModal();
            onConfirm();
        };
        
        overlay.onclick = (e) => {
            if (e.target === overlay) closeDeleteModal();
        };
        
        // ESC key to close
        document.addEventListener('keydown', function escHandler(e) {
            if (e.key === 'Escape') {
                closeDeleteModal();
                document.removeEventListener('keydown', escHandler);
            }
        });
    }
    
    function closeDeleteModal() {
        const overlay = document.getElementById('deleteModalOverlay');
        if (overlay) {
            overlay.style.animation = 'fadeOut 0.3s ease-out';
            setTimeout(() => overlay.remove(), 300);
        }
    }
    
    // Relative Time System
    function getRelativeTime(timestamp) {
        const now = Math.floor(Date.now() / 1000);
        const diff = now - timestamp;
        
        if (diff < 60) return 'Just now';
        if (diff < 3600) {
            const mins = Math.floor(diff / 60);
            return `${mins} minute${mins > 1 ? 's' : ''} ago`;
        }
        if (diff < 86400) {
            const hours = Math.floor(diff / 3600);
            return `${hours} hour${hours > 1 ? 's' : ''} ago`;
        }
        if (diff < 604800) {
            const days = Math.floor(diff / 86400);
            return `${days} day${days > 1 ? 's' : ''} ago`;
        }
        if (diff < 2592000) {
            const weeks = Math.floor(diff / 604800);
            return `${weeks} week${weeks > 1 ? 's' : ''} ago`;
        }
        if (diff < 31536000) {
            const months = Math.floor(diff / 2592000);
            return `${months} month${months > 1 ? 's' : ''} ago`;
        }
        const years = Math.floor(diff / 31536000);
        return `${years} year${years > 1 ? 's' : ''} ago`;
    }
    
    function updateRelativeTimes() {
        document.querySelectorAll('.relative-time').forEach(el => {
            const timestamp = parseInt(el.getAttribute('data-time'));
            if (timestamp) {
                el.textContent = getRelativeTime(timestamp);
            }
        });
    }
    
    // Initialize relative times
    if (document.querySelector('.relative-time')) {
        updateRelativeTimes();
        // Update every minute
        setInterval(updateRelativeTimes, 60000);
    }
    
    // Add CSS animations
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
        @keyframes scaleIn {
            from { transform: scale(0.9); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes slideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
    `;
    document.head.appendChild(style);
    
    // Snippet-specific functions
    function toggleQuickEdit() {
        const panel = document.getElementById('quickEditPanel');
        if (panel) {
            if (panel.style.display === 'none' || !panel.style.display) {
                panel.style.display = 'block';
                setTimeout(() => panel.classList.add('show'), 10);
            } else {
                panel.classList.remove('show');
                setTimeout(() => panel.style.display = 'none', 300);
            }
        }
    }
    
    function saveQuickEdit() {
        const snippetId = window.snippetId || document.querySelector('[data-snippet-id]')?.dataset.snippetId;
        if (!snippetId) {
            showNotification('Snippet ID not found', 'error');
            return;
        }
        
        const title = document.getElementById('quickTitle')?.value;
        const description = document.getElementById('quickDescription')?.value;
        const isPublic = document.getElementById('quickPublic')?.checked;
        
        if (!title || !title.trim()) {
            showNotification('Title is required', 'error');
            return;
        }
        
        const saveBtn = event.target;
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        
        fetch(`/projects/codexpro/snippets/${snippetId}/quick-update`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-HTTP-Method-Override': 'PATCH'
            },
            body: JSON.stringify({
                _method: 'PATCH',
                title: title,
                description: description,
                is_public: isPublic
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update UI
                const titleEl = document.getElementById('snippetTitle');
                if (titleEl) titleEl.textContent = title;
                
                const descEl = document.getElementById('snippetDescription');
                if (descEl) descEl.textContent = description;
                
                const badge = document.getElementById('visibilityBadge');
                const badgeIcon = badge?.querySelector('i');
                const badgeText = document.getElementById('visibilityText');
                
                if (badge && badgeIcon && badgeText) {
                    if (isPublic) {
                        badge.className = 'badge badge-success';
                        badgeIcon.className = 'fas fa-globe';
                        badgeText.textContent = 'Public';
                    } else {
                        badge.className = 'badge badge-secondary';
                        badgeIcon.className = 'fas fa-lock';
                        badgeText.textContent = 'Private';
                    }
                }
                
                showNotification('Snippet updated successfully!', 'success');
                toggleQuickEdit();
            } else {
                showNotification(data.error || 'Failed to update snippet', 'error');
            }
        })
        .catch((error) => {
            console.error('Save error:', error);
            showNotification('An error occurred while updating', 'error');
        })
        .finally(() => {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fas fa-save"></i> Save Changes';
        });
    }
    
    function deleteSnippet(id) {
        showDeleteModal(
            'Delete Snippet',
            'Are you sure you want to delete this snippet? This action cannot be undone.',
            () => {
                fetch(`/projects/codexpro/snippets/${id}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-HTTP-Method-Override': 'DELETE'
                    },
                    body: JSON.stringify({
                        _method: 'DELETE'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('Snippet deleted successfully!', 'success');
                        setTimeout(() => {
                            window.location.href = '/projects/codexpro/snippets';
                        }, 1000);
                    } else {
                        showNotification(data.error || 'Failed to delete snippet', 'error');
                    }
                })
                .catch((error) => {
                    console.error('Delete error:', error);
                    showNotification('An error occurred while deleting', 'error');
                });
            }
        );
    }
    
    function shareSnippet() {
        const url = window.location.href;
        if (navigator.clipboard) {
            navigator.clipboard.writeText(url).then(() => {
                showNotification('Share link copied to clipboard!', 'success');
            }).catch(() => {
                showNotification('Failed to copy share link', 'error');
            });
        } else {
            showNotification('Clipboard not supported', 'error');
        }
    }
    
    function copyCode() {
        const codeEl = document.querySelector('.code-container code') || document.querySelector('pre code');
        if (!codeEl) {
            showNotification('Code not found', 'error');
            return;
        }
        
        const code = codeEl.textContent;
        if (navigator.clipboard) {
            navigator.clipboard.writeText(code).then(() => {
                showNotification('Code copied to clipboard!', 'success');
            }).catch(() => {
                showNotification('Failed to copy code', 'error');
            });
        } else {
            showNotification('Clipboard not supported', 'error');
        }
    }
    
    // Project-specific functions
    function toggleProjectQuickEdit(id) {
        const panel = document.getElementById('projectQuickEditPanel' + id);
        if (panel) {
            if (panel.style.display === 'none' || !panel.style.display) {
                panel.style.display = 'block';
                setTimeout(() => panel.classList.add('show'), 10);
            } else {
                panel.classList.remove('show');
                setTimeout(() => panel.style.display = 'none', 300);
            }
        }
    }
    
    function saveProjectQuickEdit(id) {
        const name = document.getElementById('quickName' + id)?.value;
        const description = document.getElementById('quickDesc' + id)?.value;
        const isPublic = document.getElementById('quickPublicProject' + id)?.checked;
        
        if (!name || !name.trim()) {
            showNotification('Project name is required', 'error');
            return;
        }
        
        const saveBtn = event.target;
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        
        fetch(`/projects/codexpro/projects/${id}/quick-update`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-HTTP-Method-Override': 'PATCH'
            },
            body: JSON.stringify({
                _method: 'PATCH',
                name: name,
                description: description,
                visibility: isPublic ? 'public' : 'private'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Project updated successfully!', 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showNotification(data.error || 'Failed to update project', 'error');
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="fas fa-save"></i> Save Changes';
            }
        })
        .catch((error) => {
            console.error('Save error:', error);
            showNotification('An error occurred while updating', 'error');
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fas fa-save"></i> Save Changes';
        });
    }
    
    function deleteProject(id) {
        showDeleteModal(
            'Delete Project',
            'Are you sure you want to delete this project? This action cannot be undone.',
            () => {
                fetch(`/projects/codexpro/projects/${id}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-HTTP-Method-Override': 'DELETE'
                    },
                    body: JSON.stringify({
                        _method: 'DELETE'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('Project deleted successfully!', 'success');
                        setTimeout(() => {
                            window.location.href = '/projects/codexpro/projects';
                        }, 1000);
                    } else {
                        showNotification(data.error || 'Failed to delete project', 'error');
                    }
                })
                .catch((error) => {
                    console.error('Delete error:', error);
                    showNotification('An error occurred while deleting', 'error');
                });
            }
        );
    }
    
    // Mobile Menu Toggle
    (function() {
        const mobileMenuToggle = document.getElementById('mobileMenuToggle');
        const sidebar = document.getElementById('sidebar');
        const mobileOverlay = document.getElementById('mobileOverlay');
        
        if (mobileMenuToggle && sidebar && mobileOverlay) {
            // Toggle menu
            mobileMenuToggle.addEventListener('click', function() {
                sidebar.classList.toggle('mobile-open');
                mobileOverlay.classList.toggle('active');
            });
            
            // Close menu when overlay is clicked
            mobileOverlay.addEventListener('click', function() {
                sidebar.classList.remove('mobile-open');
                mobileOverlay.classList.remove('active');
            });
            
            // Close menu when a link is clicked
            const menuLinks = sidebar.querySelectorAll('.menu-link');
            menuLinks.forEach(link => {
                link.addEventListener('click', function() {
                    if (window.innerWidth <= 768) {
                        sidebar.classList.remove('mobile-open');
                        mobileOverlay.classList.remove('active');
                    }
                });
            });
        }
    })();
    </script>
</body>
</html>
