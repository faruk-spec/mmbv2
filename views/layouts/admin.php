<?php use Core\View; use Core\Security; ?>
<!-- Admin Layout Version: 2.0 - Updated <?= date('Y-m-d H:i:s') ?> -->
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Admin Panel - MyMultiBranch">
    <title><?= View::e($title ?? 'Admin Panel') ?> - <?= APP_NAME ?></title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- AdminLTE 3 CSS for professional components (small-box, info-box, cards, etc.) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">
    
    <!-- Admin Panel Responsive CSS - Optimized for all devices -->
    <link rel="stylesheet" href="/css/admin-responsive.css?v=<?= time() ?>">
    
    <style>
        /* Dark Theme (Default) */
        :root[data-theme="dark"] {
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
            --transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            --sidebar-width: 280px;
            --bg-gradient-start: rgba(0, 240, 255, 0.1);
            --bg-gradient-end: rgba(255, 46, 196, 0.1);
            --overlay-bg: rgba(0, 0, 0, 0.5);
            --dropdown-bg: rgba(0, 0, 0, 0.2);
            --shadow: rgba(0, 0, 0, 0.3);
            --hover-bg: rgba(0, 240, 255, 0.05);
            --active-bg: rgba(0, 240, 255, 0.1);
            --badge-success-bg: rgba(0, 255, 136, 0.2);
            --badge-danger-bg: rgba(255, 107, 107, 0.2);
            --badge-info-bg: rgba(0, 240, 255, 0.2);
            --badge-warning-bg: rgba(255, 170, 0, 0.2);
        }
        
        /* Light Theme */
        :root[data-theme="light"] {
            --bg-primary: #f8f9fa;
            --bg-secondary: #ffffff;
            --bg-card: #ffffff;
            --cyan: #0099cc;
            --magenta: #cc0066;
            --green: #00aa55;
            --orange: #ff8800;
            --purple: #7722cc;
            --red: #dc3545;
            --text-primary: #212529;
            --text-secondary: #6c757d;
            --border-color: rgba(0, 0, 0, 0.1);
            --shadow-glow: 0 0 20px rgba(0, 153, 204, 0.15);
            --transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            --sidebar-width: 280px;
            --bg-gradient-start: rgba(0, 153, 204, 0.05);
            --bg-gradient-end: rgba(204, 0, 102, 0.05);
            --overlay-bg: rgba(0, 0, 0, 0.3);
            --dropdown-bg: rgba(0, 0, 0, 0.05);
            --shadow: rgba(0, 0, 0, 0.15);
            --hover-bg: rgba(0, 153, 204, 0.08);
            --active-bg: rgba(0, 153, 204, 0.15);
            --badge-success-bg: rgba(0, 170, 85, 0.15);
            --badge-danger-bg: rgba(220, 53, 69, 0.15);
            --badge-info-bg: rgba(0, 153, 204, 0.15);
            --badge-warning-bg: rgba(255, 136, 0, 0.15);
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
                radial-gradient(ellipse at 20% 0%, var(--bg-gradient-start) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 100%, var(--bg-gradient-end) 0%, transparent 50%);
            pointer-events: none;
            z-index: -1;
            transition: var(--transition);
        }
        
        /* Admin Layout */
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--bg-secondary);
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
            background: var(--hover-bg);
            color: var(--cyan);
        }
        
        .menu-link.active {
            background: var(--active-bg);
            color: var(--cyan);
            border-left: 3px solid var(--cyan);
        }
        
        .menu-link i {
            width: 20px;
            font-size: 16px;
        }
        
        .menu-link .badge {
            margin-left: auto;
            background: var(--red);
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 600;
        }
        
        /* Dropdown Menu */
        .menu-dropdown {
            position: relative;
        }
        
        .menu-dropdown-toggle {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 20px;
            color: var(--text-secondary);
            text-decoration: none;
            transition: var(--transition);
            cursor: pointer;
            position: relative;
        }
        
        .menu-dropdown-toggle:hover {
            background: var(--hover-bg);
            color: var(--cyan);
        }
        
        .menu-dropdown-toggle.active {
            background: var(--active-bg);
            color: var(--cyan);
        }
        
        .menu-dropdown-toggle .left {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .menu-dropdown-toggle i {
            width: 20px;
            font-size: 16px;
        }
        
        .menu-dropdown-toggle .arrow {
            transition: var(--transition);
            font-size: 12px;
        }
        
        .menu-dropdown.open .menu-dropdown-toggle .arrow {
            transform: rotate(180deg);
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .menu-dropdown-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            background: var(--dropdown-bg);
        }
        
        .menu-dropdown.open .menu-dropdown-content {
            max-height: 800px; /* Increased from 500px to accommodate more menu items */
            overflow-y: auto; /* Add scroll if needed */
        }
        
        .menu-dropdown-content .menu-link {
            padding-left: 52px;
            font-size: 14px;
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
            background: var(--bg-secondary);
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
            gap: 20px;
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
        }
        
        .topbar-btn:hover {
            background: var(--hover-bg);
            border-color: var(--cyan);
            color: var(--cyan);
        }
        
        .user-menu {
            position: relative;
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
        
        .user-menu:hover {
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
        }
        
        /* Theme Toggle Button */
        .theme-toggle {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            padding: 8px 12px;
            border-radius: 8px;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 18px;
        }
        
        .theme-toggle:hover {
            background: var(--hover-bg);
            border-color: var(--cyan);
            color: var(--cyan);
        }
        
        /* Profile Dropdown */
        .profile-dropdown {
            position: absolute;
            top: calc(100% + 10px);
            right: 0;
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            min-width: 250px;
            box-shadow: 0 10px 30px var(--shadow);
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: var(--transition);
            z-index: 1000;
        }
        
        .user-menu.active .profile-dropdown {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .profile-header {
            padding: 20px;
            border-bottom: 1px solid var(--border-color);
            text-align: center;
        }
        
        .profile-header .user-avatar {
            width: 60px;
            height: 60px;
            font-size: 24px;
            margin: 0 auto 10px;
        }
        
        .profile-header h3 {
            font-size: 16px;
            margin-bottom: 5px;
        }
        
        .profile-header p {
            font-size: 13px;
            color: var(--text-secondary);
        }
        
        .profile-menu {
            padding: 10px;
        }
        
        .profile-menu-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 15px;
            color: var(--text-primary);
            text-decoration: none;
            border-radius: 8px;
            transition: var(--transition);
            cursor: pointer;
        }
        
        .profile-menu-item:hover {
            background: var(--hover-bg);
            color: var(--cyan);
        }
        
        .profile-menu-item i {
            width: 20px;
            font-size: 16px;
        }
        
        .profile-menu-divider {
            height: 1px;
            background: var(--border-color);
            margin: 10px 0;
        }
        
        /* Content Area */
        .content {
            flex: 1;
            padding: 30px;
        }
        
        /* Cards */
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            transition: var(--transition);
        }
        
        .card:hover {
            border-color: rgba(0, 240, 255, 0.3);
            box-shadow: 0 4px 20px var(--shadow);
        }
        
        .card-header {
            color: var(--text-primary);
        }
        
        .card-title {
            color: var(--text-primary);
        }
        
        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-family: inherit;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--cyan), var(--magenta));
            color: white;
        }
        
        .btn-primary:hover {
            box-shadow: var(--shadow-glow);
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
        }
        
        .btn-secondary:hover {
            background: var(--bg-secondary);
            border-color: var(--cyan);
        }
        
        .btn-danger {
            background: rgba(255, 107, 107, 0.2);
            color: var(--red);
            border: 1px solid var(--red);
        }
        
        .btn-danger:hover {
            background: var(--red);
            color: white;
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
            padding: 12px 16px;
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            color: var(--text-primary);
            font-family: inherit;
            font-size: 14px;
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
        
        .form-checkbox {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
        }
        
        .form-checkbox input[type="checkbox"],
        .form-checkbox input[type="radio"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        
        /* Alerts */
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
            border: 1px solid var(--red);
            color: var(--red);
        }
        
        /* Grid */
        .grid {
            display: grid;
            gap: 20px;
        }
        
        .grid-2 { grid-template-columns: repeat(2, 1fr); }
        .grid-3 { grid-template-columns: repeat(3, 1fr); }
        .grid-4 { grid-template-columns: repeat(4, 1fr); }
        
        /* Utility */
        .mb-1 { margin-bottom: 10px; }
        .mb-2 { margin-bottom: 20px; }
        .mb-3 { margin-bottom: 30px; }
        
        /* Keyframe Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        
        @keyframes slideInLeft {
            from {
                transform: translateX(-100%);
            }
            to {
                transform: translateX(0);
            }
        }
        
        /* Page Load Animation */
        .content {
            animation: fadeInUp 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        /* Sidebar Menu Item Stagger */
        .menu-link {
            animation: fadeInUp 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .menu-link:nth-child(1) { animation-delay: 0.05s; }
        .menu-link:nth-child(2) { animation-delay: 0.1s; }
        .menu-link:nth-child(3) { animation-delay: 0.15s; }
        .menu-link:nth-child(4) { animation-delay: 0.2s; }
        .menu-link:nth-child(5) { animation-delay: 0.25s; }
        .menu-link:nth-child(6) { animation-delay: 0.3s; }
        .menu-link:nth-child(7) { animation-delay: 0.35s; }
        .menu-link:nth-child(8) { animation-delay: 0.4s; }
        
        /* Enhanced Hover Animations */
        .menu-link:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 12px var(--shadow);
        }
        
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px var(--shadow);
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 240, 255, 0.3);
        }
        
        /* Profile Dropdown Animation */
        .profile-dropdown {
            animation: fadeInUp 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            transform-origin: top right;
        }
        
        /* Mobile Menu Animation */
        .sidebar.active {
            animation: slideInLeft 0.3s ease-out;
        }
        
        /* Status Badge Pulse */
        .badge.active {
            animation: pulse 2s ease-in-out infinite;
        }
        
        /* GPU Acceleration */
        .menu-link, .card, .btn, .profile-dropdown {
            will-change: transform;
        }
        
        .arrow {
            will-change: transform;
        }
        
        /* Tablet/iPad Responsive (768px - 1199px) */
        @media (max-width: 1199px) and (min-width: 768px) {
            .sidebar {
                width: 250px;
            }
            
            .main-content {
                margin-left: 250px;
            }
            
            .grid-3, .grid-4 {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .topbar {
                padding: 15px 20px;
            }
            
            .content {
                padding: 25px 20px;
            }
        }
        
        /* Mobile Responsive (< 768px) */
        @media (max-width: 767px) {
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
            
            .grid-2, .grid-3, .grid-4 {
                grid-template-columns: 1fr;
            }
            
            .topbar {
                padding: 15px;
            }
            
            .topbar-right {
                display: none;
            }
            
            .content {
                padding: 20px 15px;
            }
            
            /* Hide text on small screens */
            .topbar-title h1 {
                font-size: 1.2rem;
            }
            
            .topbar-title p {
                display: none;
            }
            
            .hide-mobile {
                display: none !important;
            }
            
            .topbar-right {
                gap: 10px;
            }
            
            .profile-dropdown {
                right: 0;
                left: auto;
                min-width: 200px;
            }
        }
        
        /* Small Mobile (< 480px) */
        @media (max-width: 479px) {
            .topbar {
                padding: 10px;
            }
            
            .content {
                padding: 15px 10px;
            }
            
            .topbar-title h1 {
                font-size: 1rem;
            }
        }
        
        /* Overlay for mobile */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--overlay-bg);
            z-index: 999;
        }
        
        .sidebar-overlay.active {
            display: block;
        }
        
        /* AdminLTE Component Overrides for Theme Support */
        .small-box {
            background: var(--bg-card) !important;
            color: var(--text-primary) !important;
            border: 1px solid var(--border-color);
        }
        
        .small-box h3, .small-box p {
            color: var(--text-primary) !important;
        }
        
        .small-box .icon {
            color: var(--text-secondary) !important;
            opacity: 0.3;
        }
        
        .info-box {
            background: var(--bg-card) !important;
            color: var(--text-primary) !important;
            border: 1px solid var(--border-color);
        }
        
        .info-box-text, .info-box-number {
            color: var(--text-primary) !important;
        }
        
        .info-box-icon {
            background: var(--cyan) !important;
        }
        
        /* Table overrides */
        .table {
            color: var(--text-primary) !important;
            background: var(--bg-card);
        }
        
        .table thead th {
            background: var(--bg-secondary) !important;
            color: var(--cyan) !important;
            border-color: var(--border-color) !important;
        }
        
        .table tbody tr {
            border-color: var(--border-color) !important;
        }
        
        .table tbody td {
            color: var(--text-primary) !important;
            border-color: var(--border-color) !important;
        }
        
        .table tbody tr:hover {
            background: var(--hover-bg) !important;
        }
        
        /* Badge overrides */
        .badge-success {
            background: var(--badge-success-bg) !important;
            color: var(--green) !important;
        }
        
        .badge-danger {
            background: var(--badge-danger-bg) !important;
            color: var(--red) !important;
        }
        
        .badge-info {
            background: var(--badge-info-bg) !important;
            color: var(--cyan) !important;
        }
        
        .badge-warning {
            background: var(--badge-warning-bg) !important;
            color: var(--orange) !important;
        }
        
        /* Form elements */
        .form-control, .form-select, input, textarea, select {
            background: var(--bg-card) !important;
            color: var(--text-primary) !important;
            border-color: var(--border-color) !important;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--cyan) !important;
            box-shadow: 0 0 0 0.2rem rgba(0, 240, 255, 0.25) !important;
        }
        
        /* Alert overrides */
        .alert {
            background: var(--bg-card) !important;
            color: var(--text-primary) !important;
            border-color: var(--border-color) !important;
        }
        
        .alert-success {
            border-left: 3px solid var(--green);
        }
        
        .alert-danger {
            border-left: 3px solid var(--red);
        }
        
        .alert-info {
            border-left: 3px solid var(--cyan);
        }
        
        .alert-warning {
            border-left: 3px solid var(--orange);
        }
    </style>
    
    <?php View::yield('styles'); ?>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <i class="fas fa-bolt"></i>
                    <?= APP_NAME ?> Admin
                </div>
            </div>
            
            <nav class="sidebar-menu">
                <!-- Dashboard -->
                <div class="menu-section">
                    <div class="menu-item">
                        <a href="/admin/dashboard" class="menu-link <?= ($_SERVER['REQUEST_URI'] ?? '') == '/admin/dashboard' || ($_SERVER['REQUEST_URI'] ?? '') == '/admin' ? 'active' : '' ?>">
                            <i class="fas fa-home"></i>
                            <span>Dashboard</span>
                        </a>
                    </div>
                </div>
                
                <!-- Projects Management -->
                <div class="menu-section">
                    <div class="menu-section-title">Projects</div>
                    
                    <div class="menu-item">
                        <a href="/admin/projects" class="menu-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/projects') === 0 && !strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/projects/') ? 'active' : '' ?>">
                            <i class="fas fa-folder"></i>
                            <span>All Projects</span>
                        </a>
                    </div>
                    
                    <!-- Database Setup -->
                    <div class="menu-item">
                        <a href="/admin/projects/database-setup" class="menu-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/projects/database-setup') === 0 ? 'active' : '' ?>">
                            <i class="fas fa-database"></i>
                            <span>Database Setup</span>
                        </a>
                    </div>
                    
                    <!-- CodeXPro -->
                    <div class="menu-item menu-dropdown">
                        <div class="menu-dropdown-toggle">
                            <div class="left">
                                <i class="fas fa-code"></i>
                                <span>CodeXPro</span>
                            </div>
                            <i class="fas fa-chevron-down arrow"></i>
                        </div>
                        <div class="menu-dropdown-content">
                            <a href="/admin/projects/codexpro" class="menu-link">
                                <i class="fas fa-chart-line"></i>
                                <span>Overview</span>
                            </a>
                            <a href="/admin/projects/codexpro/settings" class="menu-link">
                                <i class="fas fa-cog"></i>
                                <span>Settings</span>
                            </a>
                            <a href="/admin/projects/codexpro/users" class="menu-link">
                                <i class="fas fa-users"></i>
                                <span>Users</span>
                            </a>
                            <a href="/admin/projects/codexpro/templates" class="menu-link">
                                <i class="fas fa-file-code"></i>
                                <span>Templates</span>
                            </a>
                        </div>
                    </div>
                    
                    <!-- ImgTxt -->
                    <div class="menu-item menu-dropdown">
                        <div class="menu-dropdown-toggle">
                            <div class="left">
                                <i class="fas fa-image"></i>
                                <span>ImgTxt</span>
                            </div>
                            <i class="fas fa-chevron-down arrow"></i>
                        </div>
                        <div class="menu-dropdown-content">
                            <a href="/admin/projects/imgtxt" class="menu-link">
                                <i class="fas fa-chart-line"></i>
                                <span>Overview</span>
                            </a>
                            <a href="/projects/imgtxt/dashboard" class="menu-link">
                                <i class="fas fa-tachometer-alt"></i>
                                <span>Dashboard</span>
                            </a>
                            <a href="/projects/imgtxt/upload" class="menu-link">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <span>Upload & Extract</span>
                            </a>
                            <a href="/projects/imgtxt/batch" class="menu-link">
                                <i class="fas fa-layer-group"></i>
                                <span>Batch Processing</span>
                            </a>
                            <a href="/projects/imgtxt/history" class="menu-link">
                                <i class="fas fa-history"></i>
                                <span>History</span>
                            </a>
                            <a href="/admin/projects/imgtxt/jobs" class="menu-link">
                                <i class="fas fa-tasks"></i>
                                <span>All OCR Jobs</span>
                            </a>
                            <a href="/projects/imgtxt/settings" class="menu-link">
                                <i class="fas fa-cog"></i>
                                <span>User Settings</span>
                            </a>
                            <a href="/admin/projects/imgtxt/settings" class="menu-link">
                                <i class="fas fa-wrench"></i>
                                <span>Admin Settings</span>
                            </a>
                            <a href="/admin/projects/imgtxt/languages" class="menu-link">
                                <i class="fas fa-language"></i>
                                <span>Languages Config</span>
                            </a>
                            <a href="/admin/projects/imgtxt/users" class="menu-link">
                                <i class="fas fa-users"></i>
                                <span>User Management</span>
                            </a>
                            <a href="/admin/projects/imgtxt/statistics" class="menu-link">
                                <i class="fas fa-chart-bar"></i>
                                <span>Statistics</span>
                            </a>
                            <a href="/admin/projects/imgtxt/activity" class="menu-link">
                                <i class="fas fa-stream"></i>
                                <span>Activity Logs</span>
                            </a>
                        </div>
                    </div>
                    
                    <!-- ProShare - User Dashboard Features -->
                    <div class="menu-item menu-dropdown">
                        <div class="menu-dropdown-toggle">
                            <div class="left">
                                <i class="fas fa-user-circle"></i>
                                <span>ProShare User Dashboard</span>
                            </div>
                            <i class="fas fa-chevron-down arrow"></i>
                        </div>
                        <div class="menu-dropdown-content">
                            <a href="/admin/projects/proshare/user-dashboard" class="menu-link">
                                <i class="fas fa-tachometer-alt"></i>
                                <span>User Dashboard</span>
                            </a>
                            <a href="/admin/projects/proshare/user-files" class="menu-link">
                                <i class="fas fa-file"></i>
                                <span>User Files</span>
                            </a>
                            <a href="/admin/projects/proshare/user-activity" class="menu-link">
                                <i class="fas fa-history"></i>
                                <span>User Activity</span>
                            </a>
                        </div>
                    </div>

                    <!-- ProShare - Admin Features -->
                    <div class="menu-item menu-dropdown">
                        <div class="menu-dropdown-toggle">
                            <div class="left">
                                <i class="fas fa-share-alt"></i>
                                <span>ProShare Admin</span>
                            </div>
                            <i class="fas fa-chevron-down arrow"></i>
                        </div>
                        <div class="menu-dropdown-content">
                            <a href="/admin/projects/proshare" class="menu-link">
                                <i class="fas fa-chart-line"></i>
                                <span>Overview</span>
                            </a>
                            <a href="/admin/projects/proshare/settings" class="menu-link">
                                <i class="fas fa-cog"></i>
                                <span>Settings</span>
                            </a>
                            
                            <!-- User Activity Logs -->
                            <a href="/admin/projects/proshare/user-logs" class="menu-link">
                                <i class="fas fa-user-clock"></i>
                                <span>User Activity Logs</span>
                            </a>
                            <a href="/admin/projects/proshare/sessions" class="menu-link">
                                <i class="fas fa-desktop"></i>
                                <span>Session History</span>
                            </a>
                            
                            <!-- File & Folder Activity -->
                            <a href="/admin/projects/proshare/files" class="menu-link">
                                <i class="fas fa-file"></i>
                                <span>All Files</span>
                            </a>
                            <a href="/admin/projects/proshare/file-activity" class="menu-link">
                                <i class="fas fa-clipboard-list"></i>
                                <span>File Activity Logs</span>
                            </a>
                            <a href="/admin/projects/proshare/texts" class="menu-link">
                                <i class="fas fa-align-left"></i>
                                <span>Text Shares</span>
                            </a>
                            
                            <!-- Security Monitoring -->
                            <a href="/admin/projects/proshare/security" class="menu-link">
                                <i class="fas fa-shield-alt"></i>
                                <span>Security Monitoring</span>
                            </a>
                            <a href="/admin/projects/proshare/server-health" class="menu-link">
                                <i class="fas fa-heartbeat"></i>
                                <span>Server Health</span>
                            </a>
                            
                            <!-- Storage Monitoring -->
                            <a href="/admin/projects/proshare/storage" class="menu-link">
                                <i class="fas fa-hdd"></i>
                                <span>Storage Monitoring</span>
                            </a>
                            
                            <!-- Audit Trail -->
                            <a href="/admin/projects/proshare/audit-trail" class="menu-link">
                                <i class="fas fa-book"></i>
                                <span>Audit Trail</span>
                            </a>
                            
                            <!-- Notifications & Alerts -->
                            <a href="/admin/projects/proshare/notifications" class="menu-link">
                                <i class="fas fa-bell"></i>
                                <span>Notifications & Alerts</span>
                            </a>
                            
                            <!-- Analytics & Insights -->
                            <a href="/admin/projects/proshare/analytics" class="menu-link">
                                <i class="fas fa-chart-bar"></i>
                                <span>Analytics & Insights</span>
                            </a>
                        </div>
                    </div>
                    
                    <!-- WhatsApp API -->
                    <div class="menu-item menu-dropdown">
                        <div class="menu-dropdown-toggle">
                            <div class="left">
                                <i class="fab fa-whatsapp"></i>
                                <span>WhatsApp API</span>
                            </div>
                            <i class="fas fa-chevron-down arrow"></i>
                        </div>
                        <div class="menu-dropdown-content">
                            <a href="/admin/whatsapp/overview" class="menu-link">
                                <i class="fas fa-chart-line"></i>
                                <span>Overview</span>
                            </a>
                            <a href="/admin/whatsapp/sessions" class="menu-link">
                                <i class="fas fa-mobile-alt"></i>
                                <span>Sessions</span>
                            </a>
                            <a href="/admin/whatsapp/messages" class="menu-link">
                                <i class="fas fa-comments"></i>
                                <span>Messages</span>
                            </a>
                            <a href="/admin/whatsapp/users" class="menu-link">
                                <i class="fas fa-users"></i>
                                <span>Users</span>
                            </a>
                            <a href="/admin/whatsapp/api-logs" class="menu-link">
                                <i class="fas fa-file-alt"></i>
                                <span>API Logs</span>
                            </a>
                            <a href="/admin/whatsapp/subscription-plans" class="menu-link">
                                <i class="fas fa-tags"></i>
                                <span>Subscription Plans</span>
                            </a>
                            <a href="/admin/whatsapp/user-subscriptions" class="menu-link">
                                <i class="fas fa-crown"></i>
                                <span>User Subscriptions</span>
                            </a>
                            <a href="/admin/whatsapp/user-subscriptions/assign" class="menu-link">
                                <i class="fas fa-user-plus"></i>
                                <span>Assign Subscription</span>
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- User Management -->
                <div class="menu-section">
                    <div class="menu-section-title">Management</div>
                    
                    <div class="menu-item">
                        <a href="/admin/users" class="menu-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/users') === 0 ? 'active' : '' ?>">
                            <i class="fas fa-users"></i>
                            <span>Users</span>
                        </a>
                    </div>
                </div>
                
                <!-- Security -->
                <div class="menu-section">
                    <div class="menu-section-title">Security</div>
                    
                    <div class="menu-item menu-dropdown">
                        <div class="menu-dropdown-toggle">
                            <div class="left">
                                <i class="fas fa-shield-alt"></i>
                                <span>Security Center</span>
                            </div>
                            <i class="fas fa-chevron-down arrow"></i>
                        </div>
                        <div class="menu-dropdown-content">
                            <a href="/admin/security" class="menu-link">
                                <i class="fas fa-chart-line"></i>
                                <span>Overview</span>
                            </a>
                            <a href="/admin/security/blocked-ips" class="menu-link">
                                <i class="fas fa-ban"></i>
                                <span>Blocked IPs</span>
                            </a>
                            <a href="/admin/security/failed-logins" class="menu-link">
                                <i class="fas fa-exclamation-triangle"></i>
                                <span>Failed Logins</span>
                            </a>
                        </div>
                    </div>
                    
                    <div class="menu-item menu-dropdown">
                        <div class="menu-dropdown-toggle">
                            <div class="left">
                                <i class="fas fa-key"></i>
                                <span>OAuth & SSO</span>
                            </div>
                            <i class="fas fa-chevron-down arrow"></i>
                        </div>
                        <div class="menu-dropdown-content">
                            <a href="/admin/oauth" class="menu-link">
                                <i class="fas fa-cog"></i>
                                <span>OAuth Providers</span>
                            </a>
                            <a href="/admin/oauth/connections" class="menu-link">
                                <i class="fas fa-link"></i>
                                <span>OAuth Connections</span>
                            </a>
                        </div>
                    </div>
                    
                    <div class="menu-item menu-dropdown">
                        <div class="menu-dropdown-toggle">
                            <div class="left">
                                <i class="fas fa-clock"></i>
                                <span>Session Management</span>
                            </div>
                            <i class="fas fa-chevron-down arrow"></i>
                        </div>
                        <div class="menu-dropdown-content">
                            <a href="/admin/sessions" class="menu-link">
                                <i class="fas fa-users"></i>
                                <span>Active Sessions</span>
                            </a>
                            <a href="/admin/sessions/login-history" class="menu-link">
                                <i class="fas fa-history"></i>
                                <span>Login History</span>
                            </a>
                        </div>
                    </div>
                    
                    <!-- 2FA Management -->
                    <div class="menu-item menu-dropdown">
                        <div class="menu-dropdown-toggle">
                            <div class="left">
                                <i class="fas fa-shield-alt"></i>
                                <span>2FA Management</span>
                            </div>
                            <i class="fas fa-chevron-down arrow"></i>
                        </div>
                        <div class="menu-dropdown-content">
                            <a href="/admin/2fa" class="menu-link">
                                <i class="fas fa-users-cog"></i>
                                <span>User 2FA Status</span>
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Logs -->
                <div class="menu-section">
                    <div class="menu-section-title">Logs</div>
                    
                    <div class="menu-item menu-dropdown">
                        <div class="menu-dropdown-toggle">
                            <div class="left">
                                <i class="fas fa-file-alt"></i>
                                <span>Activity Logs</span>
                            </div>
                            <i class="fas fa-chevron-down arrow"></i>
                        </div>
                        <div class="menu-dropdown-content">
                            <a href="/admin/logs" class="menu-link">
                                <i class="fas fa-list"></i>
                                <span>All Logs</span>
                            </a>
                            <a href="/admin/logs/activity" class="menu-link">
                                <i class="fas fa-user-clock"></i>
                                <span>User Activity</span>
                            </a>
                            <a href="/admin/logs/system" class="menu-link">
                                <i class="fas fa-server"></i>
                                <span>System Logs</span>
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Advanced Features -->
                <div class="menu-section">
                    <div class="menu-section-title">Advanced Features</div>
                    
                    <!-- API Management -->
                    <div class="menu-item menu-dropdown">
                        <div class="menu-dropdown-toggle">
                            <div class="left">
                                <i class="fas fa-plug"></i>
                                <span>API Management</span>
                            </div>
                            <i class="fas fa-chevron-down arrow"></i>
                        </div>
                        <div class="menu-dropdown-content">
                            <a href="/admin/api/keys" class="menu-link">
                                <i class="fas fa-key"></i>
                                <span>API Keys</span>
                            </a>
                            <a href="/admin/api/logs" class="menu-link">
                                <i class="fas fa-history"></i>
                                <span>Request Logs</span>
                            </a>
                            <a href="/admin/api/rate-limits" class="menu-link">
                                <i class="fas fa-tachometer-alt"></i>
                                <span>Rate Limits</span>
                            </a>
                            <a href="/admin/api/documentation" class="menu-link">
                                <i class="fas fa-book"></i>
                                <span>Documentation</span>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Real-time & WebSocket -->
                    <div class="menu-item menu-dropdown">
                        <div class="menu-dropdown-toggle">
                            <div class="left">
                                <i class="fas fa-broadcast-tower"></i>
                                <span>Real-time Features</span>
                            </div>
                            <i class="fas fa-chevron-down arrow"></i>
                        </div>
                        <div class="menu-dropdown-content">
                            <a href="/admin/websocket/status" class="menu-link">
                                <i class="fas fa-signal"></i>
                                <span>WebSocket Status</span>
                            </a>
                            <a href="/admin/websocket/connections" class="menu-link">
                                <i class="fas fa-users"></i>
                                <span>Active Connections</span>
                            </a>
                            <a href="/admin/websocket/rooms" class="menu-link">
                                <i class="fas fa-layer-group"></i>
                                <span>Rooms</span>
                            </a>
                            <a href="/admin/websocket/settings" class="menu-link">
                                <i class="fas fa-cog"></i>
                                <span>Settings</span>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Analytics & Reports -->
                    <div class="menu-item menu-dropdown">
                        <div class="menu-dropdown-toggle">
                            <div class="left">
                                <i class="fas fa-chart-bar"></i>
                                <span>Analytics</span>
                            </div>
                            <i class="fas fa-chevron-down arrow"></i>
                        </div>
                        <div class="menu-dropdown-content">
                            <a href="/admin/analytics/overview" class="menu-link">
                                <i class="fas fa-chart-line"></i>
                                <span>Overview</span>
                            </a>
                            <a href="/admin/analytics/events" class="menu-link">
                                <i class="fas fa-stream"></i>
                                <span>Events</span>
                            </a>
                            <a href="/admin/analytics/reports" class="menu-link">
                                <i class="fas fa-file-alt"></i>
                                <span>Reports</span>
                            </a>
                            <a href="/admin/analytics/export" class="menu-link">
                                <i class="fas fa-download"></i>
                                <span>Export Data</span>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Email & Notifications -->
                    <div class="menu-item menu-dropdown">
                        <div class="menu-dropdown-toggle">
                            <div class="left">
                                <i class="fas fa-envelope"></i>
                                <span>Email & Notifications</span>
                            </div>
                            <i class="fas fa-chevron-down arrow"></i>
                        </div>
                        <div class="menu-dropdown-content">
                            <a href="/admin/email/queue" class="menu-link">
                                <i class="fas fa-inbox"></i>
                                <span>Email Queue</span>
                            </a>
                            <a href="/admin/email/templates" class="menu-link">
                                <i class="fas fa-file-code"></i>
                                <span>Templates</span>
                            </a>
                            <a href="/admin/notifications/all" class="menu-link">
                                <i class="fas fa-bell"></i>
                                <span>All Notifications</span>
                            </a>
                            <a href="/admin/notifications/preferences" class="menu-link">
                                <i class="fas fa-sliders-h"></i>
                                <span>Preferences</span>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Performance & Cache -->
                    <div class="menu-item menu-dropdown">
                        <div class="menu-dropdown-toggle">
                            <div class="left">
                                <i class="fas fa-bolt"></i>
                                <span>Performance</span>
                            </div>
                            <i class="fas fa-chevron-down arrow"></i>
                        </div>
                        <div class="menu-dropdown-content">
                            <a href="/admin/performance/cache" class="menu-link">
                                <i class="fas fa-database"></i>
                                <span>Cache Management</span>
                            </a>
                            <a href="/admin/performance/assets" class="menu-link">
                                <i class="fas fa-file-code"></i>
                                <span>Asset Optimization</span>
                            </a>
                            <a href="/admin/performance/database" class="menu-link">
                                <i class="fas fa-server"></i>
                                <span>Database Optimization</span>
                            </a>
                            <a href="/admin/performance/monitoring" class="menu-link">
                                <i class="fas fa-chart-area"></i>
                                <span>Monitoring</span>
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Settings -->
                <div class="menu-section">
                    <div class="menu-section-title">System</div>
                    
                    <div class="menu-item menu-dropdown">
                        <div class="menu-dropdown-toggle">
                            <div class="left">
                                <i class="fas fa-cog"></i>
                                <span>Settings</span>
                            </div>
                            <i class="fas fa-chevron-down arrow"></i>
                        </div>
                        <div class="menu-dropdown-content">
                            <a href="/admin/settings" class="menu-link">
                                <i class="fas fa-sliders-h"></i>
                                <span>General</span>
                            </a>
                            <a href="/admin/settings/session" class="menu-link">
                                <i class="fas fa-clock"></i>
                                <span>Session & Security</span>
                            </a>
                            <a href="/admin/home-content" class="menu-link">
                                <i class="fas fa-home"></i>
                                <span>Home Page</span>
                            </a>
                            <a href="/admin/navbar" class="menu-link">
                                <i class="fas fa-bars"></i>
                                <span>Navbar</span>
                            </a>
                            <a href="/admin/settings/maintenance" class="menu-link">
                                <i class="fas fa-tools"></i>
                                <span>Maintenance</span>
                            </a>
                            <a href="/admin/settings/features" class="menu-link">
                                <i class="fas fa-toggle-on"></i>
                                <span>Feature Flags</span>
                            </a>
                        </div>
                    </div>
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
                        <h1><?= $title ?? 'Admin Panel' ?></h1>
                        <?php if (isset($subtitle)): ?>
                            <p><?= $subtitle ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="topbar-right">
                    <!-- Theme Toggle Button -->
                    <button class="theme-toggle" id="themeToggle" title="Toggle theme">
                        <i class="fas fa-moon" id="themeIcon"></i>
                    </button>
                    
                    <a href="/" class="topbar-btn">
                        <i class="fas fa-home"></i>
                        <span class="hide-mobile">Visit Site</span>
                    </a>
                    
                    <!-- User Menu with Dropdown -->
                    <div class="user-menu" id="userMenu">
                        <div class="user-avatar">
                            <?= strtoupper(substr($user['name'] ?? 'A', 0, 1)) ?>
                        </div>
                        <span class="hide-mobile"><?= $user['name'] ?? 'Admin' ?></span>
                        <i class="fas fa-chevron-down"></i>
                        
                        <!-- Profile Dropdown -->
                        <div class="profile-dropdown">
                            <div class="profile-header">
                                <div class="user-avatar">
                                    <?= strtoupper(substr($user['name'] ?? 'A', 0, 1)) ?>
                                </div>
                                <h3><?= View::e($user['name'] ?? 'Admin') ?></h3>
                                <p><?= View::e($user['email'] ?? 'admin@example.com') ?></p>
                            </div>
                            
                            <div class="profile-menu">
                                <a href="/admin/profile" class="profile-menu-item">
                                    <i class="fas fa-user"></i>
                                    <span>My Profile</span>
                                </a>
                                <a href="/admin/security" class="profile-menu-item">
                                    <i class="fas fa-shield-alt"></i>
                                    <span>Security Settings</span>
                                </a>
                                <a href="/admin/activity" class="profile-menu-item">
                                    <i class="fas fa-history"></i>
                                    <span>Activity Log</span>
                                </a>
                                
                                <div class="profile-menu-divider"></div>
                                
                                <a href="/admin/settings" class="profile-menu-item">
                                    <i class="fas fa-cog"></i>
                                    <span>Settings</span>
                                </a>
                                
                                <div class="profile-menu-divider"></div>
                                
                                <a href="/auth/logout" class="profile-menu-item" style="color: var(--red);">
                                    <i class="fas fa-sign-out-alt"></i>
                                    <span>Logout</span>
                                </a>
                            </div>
                        </div>
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
        // Theme Management
        const themeToggle = document.getElementById('themeToggle');
        const themeIcon = document.getElementById('themeIcon');
        const html = document.documentElement;
        
        // Load saved theme or default to dark
        const savedTheme = localStorage.getItem('adminTheme') || 'dark';
        html.setAttribute('data-theme', savedTheme);
        updateThemeIcon(savedTheme);
        
        function updateThemeIcon(theme) {
            if (theme === 'dark') {
                themeIcon.className = 'fas fa-moon';
                themeToggle.setAttribute('title', 'Switch to light mode');
            } else {
                themeIcon.className = 'fas fa-sun';
                themeToggle.setAttribute('title', 'Switch to dark mode');
            }
        }
        
        themeToggle.addEventListener('click', () => {
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('adminTheme', newTheme);
            updateThemeIcon(newTheme);
        });
        
        // Profile Dropdown Toggle
        const userMenu = document.getElementById('userMenu');
        
        userMenu.addEventListener('click', (e) => {
            // Don't toggle if clicking on a dropdown link
            if (!e.target.closest('.profile-menu-item')) {
                userMenu.classList.toggle('active');
            }
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!userMenu.contains(e.target)) {
                userMenu.classList.remove('active');
            }
        });
        
        // Mobile menu toggle
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        
        mobileMenuBtn.addEventListener('click', () => {
            sidebar.classList.toggle('active');
            sidebarOverlay.classList.toggle('active');
        });
        
        sidebarOverlay.addEventListener('click', () => {
            sidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
        });
        
        // Dropdown menus
        document.querySelectorAll('.menu-dropdown-toggle').forEach(toggle => {
            toggle.addEventListener('click', function() {
                const dropdown = this.parentElement;
                const wasOpen = dropdown.classList.contains('open');
                
                // Close all dropdowns
                document.querySelectorAll('.menu-dropdown').forEach(d => {
                    d.classList.remove('open');
                });
                
                // Toggle current dropdown
                if (!wasOpen) {
                    dropdown.classList.add('open');
                }
            });
        });
        
        // Auto-open dropdown if current page is in it
        document.querySelectorAll('.menu-dropdown-content .menu-link').forEach(link => {
            if (link.classList.contains('active') || window.location.pathname === link.getAttribute('href')) {
                link.parentElement.parentElement.classList.add('open');
                link.classList.add('active');
            }
        });
    </script>
    
    <?php View::yield('scripts'); ?>
</body>
</html>
