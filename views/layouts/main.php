<?php use Core\View; use Core\Security; use Core\Auth; ?>
<?php
// Set theme from localStorage via cookie or use default
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
    <meta name="description" content="MyMultiBranch - Multi-Project Platform">
    <title><?= View::e($title ?? 'MyMultiBranch') ?> - <?= APP_NAME ?></title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://unpkg.com/lucide@latest/dist/lucide.min.css">
    
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
            --hover-bg: rgba(0, 240, 255, 0.1);
            --shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }
        
        /* Light Theme */
        [data-theme="light"] {
            --bg-primary: #f8f9fa;
            --bg-secondary: #ffffff;
            --bg-card: #ffffff;
            --text-primary: #1a1a1a;
            --text-secondary: #666666;
            --border-color: rgba(0, 0, 0, 0.1);
            --shadow-glow: 0 0 20px rgba(0, 0, 0, 0.1);
            --hover-bg: rgba(0, 153, 204, 0.1);
            --shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        }
        
        [data-theme="light"] body::before {
            background: 
                radial-gradient(ellipse at 20% 0%, rgba(0, 240, 255, 0.05) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 100%, rgba(255, 46, 196, 0.05) 0%, transparent 50%);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html, body {
            width: 100%;
            overflow-x: hidden;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            line-height: 1.6;
            font-size: 14px;
        }
        
        h1 { font-size: 2rem; }
        h2 { font-size: 1.5rem; }
        h3 { font-size: 1.25rem; }
        h4 { font-size: 1.1rem; }
        p { font-size: 14px; }
        
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
        
        a {
            color: var(--cyan);
            text-decoration: none;
            transition: var(--transition);
        }
        
        a:hover {
            color: var(--magenta);
        }
        
        /* Container */
        .container {
            max-width: 1400px;
            min-width: 320px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        /* Content wrapper with constraints */
        .content-wrapper {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        /* Header */
        .header {
            background: rgba(12, 12, 18, 0.95);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border-color);
            position: sticky;
            top: 0;
            z-index: 100;
            width: 100%;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
        }
        
        .logo {
            font-size: 1.3rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--cyan), var(--magenta));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: flex;
            align-items: center;
        }
        
        .logo img {
            display: block;
        }
        
        .nav {
            display: flex;
            gap: 20px;
            align-items: center;
        }
        
        .nav-item {
            position: relative;
        }
        
        .nav a, .nav-link {
            color: var(--text-secondary);
            font-weight: 500;
            padding: 8px 12px;
            position: relative;
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 14px;
            cursor: pointer;
            border-radius: 6px;
            transition: var(--transition);
        }
        
        .nav a:hover,
        .nav a.active,
        .nav-link:hover {
            color: var(--text-primary);
            background: rgba(0, 240, 255, 0.1);
        }
        
        /* Dropdown Menu */
        .dropdown {
            position: relative;
        }
        
        .dropdown-toggle {
            background: none;
            border: none;
            cursor: pointer;
            font-family: inherit;
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
            background: rgba(0, 240, 255, 0.1);
            color: var(--cyan);
        }
        
        .dropdown-divider {
            height: 1px;
            background: var(--border-color);
            margin: 8px 0;
        }
        
        .project-icon {
            width: 24px;
            height: 24px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }
        
        /* Theme Toggle */
        .theme-toggle {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 14px;
            transition: var(--transition);
        }
        
        .theme-toggle:hover {
            background: rgba(0, 240, 255, 0.1);
            border-color: var(--cyan);
        }
        
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            color: var(--text-primary);
            font-size: 24px;
            cursor: pointer;
            padding: 8px;
        }
        
        /* Mobile Menu Toggle */
        .mobile-menu-toggle {
            display: none;
            flex-direction: column;
            gap: 5px;
            cursor: pointer;
            padding: 8px;
            z-index: 200;
            position: relative;
        }
        
        .mobile-menu-toggle span {
            width: 25px;
            height: 3px;
            background: var(--text-primary);
            transition: var(--transition);
            border-radius: 3px;
        }
        
        .mobile-menu-toggle.active span:nth-child(1) {
            transform: rotate(45deg) translateY(8px);
        }
        
        .mobile-menu-toggle.active span:nth-child(2) {
            opacity: 0;
        }
        
        .mobile-menu-toggle.active span:nth-child(3) {
            transform: rotate(-45deg) translateY(-8px);
        }
        
        /* User Menu Dropdown */
        .user-menu {
            position: relative;
        }
        
        .user-menu-trigger {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 12px;
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .user-menu-trigger:hover {
            border-color: var(--cyan);
        }
        
        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--cyan), var(--magenta));
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
            color: var(--bg-primary);
        }
        
        .user-menu-dropdown {
            position: absolute;
            top: calc(100% + 10px);
            right: 0;
            width: 220px;
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            z-index: 200;
            overflow: hidden;
        }
        
        .user-menu.active .user-menu-dropdown {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .user-menu-header {
            padding: 16px;
            border-bottom: 1px solid var(--border-color);
            background: linear-gradient(135deg, rgba(0, 240, 255, 0.05), rgba(255, 46, 196, 0.05));
        }
        
        .user-menu-name {
            font-weight: 600;
            margin-bottom: 4px;
        }
        
        .user-menu-email {
            font-size: 12px;
            color: var(--text-secondary);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .user-menu-items {
            padding: 8px 0;
        }
        
        .user-menu-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: var(--text-primary);
            transition: var(--transition);
        }
        
        .user-menu-item:hover {
            background: var(--bg-secondary);
            color: var(--cyan);
        }
        
        .user-menu-item svg {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
        }
        
        .user-menu-item.danger {
            color: var(--red);
            border-top: 1px solid var(--border-color);
        }
        
        .user-menu-item.danger:hover {
            background: rgba(255, 107, 107, 0.1);
        }
        
        /* Responsive Navbar */
        @media (max-width: 768px) {
            .header-content {
                justify-content: space-between;
            }
            
            .mobile-menu-toggle {
                display: flex;
            }
            
            .nav {
                position: fixed;
                top: 0;
                right: -100%;
                width: 280px;
                height: 100vh;
                background: rgba(12, 12, 18, 0.98);
                backdrop-filter: blur(20px);
                flex-direction: column;
                align-items: flex-start;
                padding: 80px 30px 30px 30px;
                gap: 20px;
                border-left: 1px solid var(--border-color);
                transition: right 0.3s ease;
                box-shadow: -5px 0 20px rgba(0, 0, 0, 0.5);
                z-index: 150;
                overflow-y: auto;
                visibility: hidden;
                pointer-events: none;
            }
            
            .nav.active {
                right: 0;
                visibility: visible;
                pointer-events: auto;
            }
            
            .nav a {
                width: 100%;
                padding: 12px 0;
                font-size: 1.1rem;
                color: var(--text-primary);
                display: block;
            }
            
            .nav a::after {
                bottom: 5px;
            }
            
            .user-menu-trigger {
                width: 100%;
                justify-content: flex-start;
            }
            
            .user-menu-dropdown {
                position: static;
                width: 100%;
                margin-top: 10px;
                box-shadow: none;
                border: 1px solid var(--border-color);
                max-height: 0;
                overflow: hidden;
                opacity: 0;
                visibility: hidden;
                transition: max-height 0.3s ease, opacity 0.3s ease, visibility 0.3s ease;
            }
            
            .user-menu.active .user-menu-dropdown {
                max-height: 500px;
                opacity: 1;
                visibility: visible;
            }
        }
        
        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-family: inherit;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--cyan), var(--magenta));
            color: var(--bg-primary);
        }
        
        .btn-primary:hover {
            box-shadow: var(--shadow-glow);
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: var(--bg-card);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
        }
        
        .btn-secondary:hover {
            border-color: var(--cyan);
            box-shadow: 0 0 15px rgba(0, 240, 255, 0.2);
        }
        
        .btn-danger {
            background: rgba(255, 107, 107, 0.2);
            color: var(--red);
            border: 1px solid var(--red);
        }
        
        .btn-danger:hover {
            background: var(--red);
            color: var(--bg-primary);
        }
        
        .btn-sm {
            padding: 6px 14px;
            font-size: 12px;
        }
        
        /* Cards */
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 20px;
            transition: var(--transition);
        }
        
        .card:hover {
            border-color: rgba(0, 240, 255, 0.3);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
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
            font-size: 1rem;
            font-weight: 600;
        }
        
        /* Forms */
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-secondary);
        }
        
        .form-input {
            width: 100%;
            padding: 10px 14px;
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 6px;
            color: var(--text-primary);
            font-family: inherit;
            font-size: 13px;
            transition: var(--transition);
        }
        
        .form-input:focus {
            outline: none;
            border-color: var(--cyan);
            box-shadow: 0 0 0 3px rgba(0, 240, 255, 0.1);
        }
        
        .form-input::placeholder {
            color: var(--text-secondary);
        }
        
        .form-error {
            color: var(--red);
            font-size: 13px;
            margin-top: 5px;
        }
        
        .form-checkbox {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
        }
        
        .form-checkbox input {
            width: 18px;
            height: 18px;
            accent-color: var(--cyan);
        }
        
        /* Alerts */
        .alert {
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 16px;
            border: 1px solid;
            font-size: 13px;
        }
        
        .alert-success {
            background: rgba(0, 255, 136, 0.1);
            border-color: var(--green);
            color: var(--green);
        }
        
        .alert-error {
            background: rgba(255, 107, 107, 0.1);
            border-color: var(--red);
            color: var(--red);
        }
        
        .alert-warning {
            background: rgba(255, 170, 0, 0.1);
            border-color: var(--orange);
            color: var(--orange);
        }
        
        /* Tables */
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .table th,
        .table td {
            padding: 10px 12px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
            font-size: 13px;
        }
        
        .table th {
            color: var(--text-secondary);
            font-weight: 500;
            font-size: 12px;
            text-transform: uppercase;
        }
        
        .table tr:hover td {
            background: rgba(255, 255, 255, 0.02);
        }
        
        /* Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
        }
        
        .badge-success {
            background: rgba(0, 255, 136, 0.15);
            color: var(--green);
        }
        
        .badge-danger {
            background: rgba(255, 107, 107, 0.15);
            color: var(--red);
        }
        
        .badge-warning {
            background: rgba(255, 170, 0, 0.15);
            color: var(--orange);
        }
        
        .badge-info {
            background: rgba(0, 240, 255, 0.15);
            color: var(--cyan);
        }
        
        /* Main Content */
        .main {
            padding: 30px 0;
            min-height: calc(100vh - 120px);
        }
        
        /* Footer */
        .footer {
            background: var(--bg-secondary);
            border-top: 1px solid var(--border-color);
            padding: 16px 0;
            text-align: center;
            color: var(--text-secondary);
            font-size: 13px;
        }
        
        /* Grid */
        .grid {
            display: grid;
            gap: 16px;
        }
        
        .grid-2 { grid-template-columns: repeat(2, 1fr); }
        .grid-3 { grid-template-columns: repeat(3, 1fr); }
        .grid-4 { grid-template-columns: repeat(4, 1fr); }
        
        @media (max-width: 1024px) {
            .grid-4 {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .grid-2, .grid-3, .grid-4 {
                grid-template-columns: 1fr;
            }
            
            .nav {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: var(--bg-secondary);
                border-bottom: 1px solid var(--border-color);
                padding: 20px;
                flex-direction: column;
                gap: 0;
            }
            
            .nav.active {
                display: flex;
            }
            
            .nav-item, .nav a, .nav-link {
                width: 100%;
                justify-content: flex-start;
            }
            
            .dropdown-menu {
                position: static;
                margin-top: 8px;
                box-shadow: none;
                border: none;
                border-left: 2px solid var(--cyan);
                padding-left: 20px;
            }
            
            .mobile-menu-btn {
                display: block;
            }
            
            body {
                font-size: 13px;
            }
            
            h1 { font-size: 1.5rem; }
            h2 { font-size: 1.25rem; }
            h3 { font-size: 1.1rem; }
        }
        
        /* Utility Classes */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .mb-1 { margin-bottom: 10px; }
        .mb-2 { margin-bottom: 20px; }
        .mb-3 { margin-bottom: 30px; }
        .mt-1 { margin-top: 10px; }
        .mt-2 { margin-top: 20px; }
        .mt-3 { margin-top: 30px; }
        
        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .animate-fade-in {
            animation: fadeIn 0.3s ease forwards;
        }
        
        /* Scroll to top */
        .scroll-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, var(--cyan), var(--magenta));
            border: none;
            border-radius: 50%;
            cursor: pointer;
            display: none;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
            z-index: 99;
        }
        
        .scroll-top:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-glow);
        }
        
        .scroll-top.visible {
            display: flex;
        }
    </style>
    
    <?php View::yield('styles'); ?>
</head>
<body>
    <?php include BASE_PATH . '/views/layouts/navbar.php'; ?>
    
    <main class="main">
        <?php View::yield('content'); ?>
    </main>
    
    <footer class="footer">
        <p>&copy; <?= date('Y') ?> <?= APP_NAME ?>. All rights reserved.</p>
    </footer>
    
    <button class="scroll-top" id="scrollTop" aria-label="Scroll to top">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M18 15l-6-6-6 6"/>
        </svg>
    </button>
    
    <script>
        // Scroll to top button
        const scrollBtn = document.getElementById('scrollTop');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 300) {
                scrollBtn.classList.add('visible');
            } else {
                scrollBtn.classList.remove('visible');
            }
        });
        scrollBtn.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    </script>
    
    <?php View::yield('scripts'); ?>
</body>
</html>
