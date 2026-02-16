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

// Cache busting version - update this when making UI changes
// Format: YYYYMMDDHHMMSS
$uiVersion = '20260214040500';

// Prevent browser caching of this page
header("Cache-Control: no-cache, no-store, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
?>
<html lang="en" data-theme="<?= htmlspecialchars($defaultTheme) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title><?= htmlspecialchars($title ?? 'QR Generator') ?> - MyMultiBranch</title>
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Universal Theme CSS with cache busting -->
    <link rel="stylesheet" href="/css/universal-theme.css?v=<?= $uiVersion ?>">
    
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
            --sidebar-width: 15rem; /* 240px converted to rem */
            
            /* Compact spacing scale using rem - reduced for professional compact design */
            --space-xs: 0.25rem;  /* 4px - keep */
            --space-sm: 0.375rem; /* 6px - reduced from 8px */
            --space-md: 0.75rem;  /* 12px - reduced from 16px */
            --space-lg: 1rem;     /* 16px - reduced from 24px */
            --space-xl: 1.5rem;   /* 24px - reduced from 32px */
            --space-2xl: 2rem;    /* 32px - reduced from 48px */
            
            /* Font sizes using rem */
            --font-xs: 0.75rem;   /* 12px */
            --font-sm: 0.875rem;  /* 14px */
            --font-md: 1rem;      /* 16px */
            --font-lg: 1.125rem;  /* 18px */
            --font-xl: 1.25rem;   /* 20px */
            --font-2xl: 1.5rem;   /* 24px */
        }
        
        [data-theme="light"] {
            --bg-primary: #f8f9fa;
            --bg-secondary: #ffffff;
            --bg-card: #ffffff;
            --text-primary: #1a1a1a;
            --text-secondary: #666666;
            --border-color: rgba(0, 0, 0, 0.1);
        }
        
        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
        }
        
        html {
            /* Smooth scrolling with better performance */
            scroll-behavior: smooth;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            /* Prevent horizontal overflow on mobile */
            overflow-x: hidden;
            max-width: 100vw;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            font-size: var(--font-md);
            line-height: 1.5;
            /* Performance optimization */
            will-change: scroll-position;
            -webkit-overflow-scrolling: touch;
            /* Prevent horizontal overflow on mobile */
            overflow-x: hidden;
            max-width: 100vw;
        }
        
        /* Layout Structure - optimized with rem */
        .qr-dashboard {
            display: flex;
            min-height: calc(100vh - 3.75rem); /* 60px navbar */
            margin-top: 3.75rem;
        }
        
        /* Sidebar - optimized with performance in mind */
        .qr-sidebar {
            width: var(--sidebar-width);
            background: var(--bg-card);
            border-right: 1px solid var(--border-color);
            padding: var(--space-lg) 0;
            position: fixed;
            left: 0;
            top: 3.75rem;
            bottom: 0;
            overflow-y: auto;
            overflow-x: hidden;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 100;
            /* Performance optimization */
            will-change: transform;
            contain: layout style paint;
            /* Better scrolling on touch devices */
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
            scrollbar-color: var(--border-color) transparent;
        }
        
        .qr-sidebar::-webkit-scrollbar {
            width: 0.375rem; /* 6px */
        }
        
        .qr-sidebar::-webkit-scrollbar-track {
            background: transparent;
        }
        
        .qr-sidebar::-webkit-scrollbar-thumb {
            background: var(--border-color);
            border-radius: 0.1875rem; /* 3px */
        }
        
        .qr-sidebar.closed {
            transform: translateX(-100%);
        }
        
        .sidebar-section {
            padding: var(--space-sm) var(--space-lg);
            margin-bottom: var(--space-lg);
        }
        
        .sidebar-title {
            font-size: 0.6875rem; /* 11px */
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.0625rem; /* 1px */
            margin-bottom: var(--space-sm);
            padding: 0 var(--space-sm);
        }
        
        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: var(--space-md);
            padding: 0.75rem var(--space-md);
            color: var(--text-secondary);
            text-decoration: none;
            border-radius: 0.5rem; /* 8px */
            margin-bottom: var(--space-xs);
            transition: all 0.2s ease;
            font-size: var(--font-sm);
        }
        
        .sidebar-nav a:hover {
            background: var(--bg-secondary);
            color: var(--text-primary);
            transform: translateX(0.125rem); /* 2px */
        }
        
        .sidebar-nav a.active {
            background: linear-gradient(135deg, var(--purple), var(--magenta));
            color: white;
        }
        
        .sidebar-nav svg {
            width: 1.25rem; /* 20px */
            height: 1.25rem;
            flex-shrink: 0;
        }
        
        /* Main Content - optimized */
        .qr-main {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: var(--space-lg);
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            min-height: calc(100vh - 3.75rem);
            /* Performance optimization */
            contain: layout style;
        }
        
        .qr-main.expanded {
            margin-left: 0;
        }
        
        /* Mobile Toggle Button - optimized */
        .sidebar-toggle {
            position: fixed;
            bottom: var(--space-lg);
            right: var(--space-lg);
            width: 3.5rem; /* 56px */
            height: 3.5rem;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--purple), var(--magenta));
            border: none;
            color: white;
            cursor: pointer;
            display: none;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0.25rem 0.75rem rgba(153, 69, 255, 0.4);
            z-index: 101;
            transition: transform 0.2s ease;
            /* Performance */
            will-change: transform;
        }
        
        .sidebar-toggle:active {
            transform: scale(0.95);
        }
        
        .sidebar-toggle svg {
            width: 1.5rem; /* 24px */
            height: 1.5rem;
        }
        
        /* Card Styles - optimized with rem and compact padding */
        .card, .glass-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 0.625rem; /* 10px - reduced from 12px */
            padding: var(--space-md); /* 12px - reduced from 24px */
            transition: all 0.3s ease;
            /* Performance */
            will-change: transform, box-shadow;
        }
        
        .card:hover, .glass-card:hover {
            border-color: rgba(0, 240, 255, 0.3);
            box-shadow: 0 0.25rem 1.25rem rgba(0, 0, 0, 0.2);
            transform: translateY(-0.125rem); /* -2px */
        }
        
        /* Button Styles - compact professional design */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.375rem; /* 6px - reduced from 8px */
            padding: 0.5rem 1rem; /* 8px 16px - reduced for compact design */
            border: none;
            border-radius: 0.5rem; /* 8px - reduced from 10px */
            font-family: inherit;
            font-size: var(--font-xs); /* 12px - smaller for compact */
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            box-shadow: 0 0.125rem 0.375rem rgba(0, 0, 0, 0.1);
            white-space: nowrap;
            /* Performance */
            will-change: transform;
        }
        
        /* Better button appearance on desktop - still compact */
        @media (min-width: 48rem) { /* 768px */
            .btn {
                padding: 0.625rem 1.25rem; /* 10px 20px - reduced from 14px 28px */
                font-size: var(--font-sm); /* 14px - reduced from 16px */
                border-radius: 0.625rem; /* 10px - reduced from 12px */
                box-shadow: 0 0.1875rem 0.625rem rgba(0, 0, 0, 0.12);
            }
            
            .btn-sm {
                padding: 0.5rem 0.875rem; /* 8px 14px - reduced */
                font-size: var(--font-xs); /* 12px */
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
            /* Compact button styles */
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.375rem;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-family: inherit;
            font-size: var(--font-xs);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            box-shadow: 0 0.125rem 0.375rem rgba(0, 0, 0, 0.1);
            white-space: nowrap;
        }
        
        .btn-primary:hover:not(:disabled) {
            box-shadow: 0 0.375rem 1.5rem rgba(153, 69, 255, 0.5);
            transform: translateY(-0.125rem);
        }
        
        .btn-secondary {
            background: var(--bg-secondary);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
            /* Compact button styles */
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.375rem;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-family: inherit;
            font-size: var(--font-xs);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            box-shadow: 0 0.125rem 0.375rem rgba(0, 0, 0, 0.1);
            white-space: nowrap;
        }
        
        .btn-secondary:hover:not(:disabled) {
            background: var(--bg-tertiary);
            transform: translateY(-0.125rem);
            box-shadow: 0 0.375rem 1.25rem rgba(0, 0, 0, 0.15);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #ff4757, #ff6b6b);
            color: white;
            border: none;
            /* Compact button styles */
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.375rem;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-family: inherit;
            font-size: var(--font-xs);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            box-shadow: 0 0.125rem 0.375rem rgba(0, 0, 0, 0.1);
            white-space: nowrap;
        }
        
        .btn-danger:hover:not(:disabled) {
            box-shadow: 0 0.375rem 1.5rem rgba(255, 71, 87, 0.5);
            transform: translateY(-0.125rem);
        }
        
        .btn-primary:active,
        .btn-secondary:active,
        .btn-danger:active {
            transform: translateY(0);
        }
        
        .btn-primary:disabled,
        .btn-secondary:disabled,
        .btn-danger:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        /* Desktop improvements for all button variants - still compact */
        @media (min-width: 48rem) {
            .btn-primary,
            .btn-secondary,
            .btn-danger {
                padding: 0.625rem 1.25rem; /* 10px 20px - reduced */
                font-size: var(--font-sm); /* 14px */
                border-radius: 0.625rem; /* 10px */
                box-shadow: 0 0.1875rem 0.625rem rgba(0, 0, 0, 0.12);
            }
        }
        
        .btn-sm {
            padding: 0.375rem 0.75rem; /* 6px 12px - reduced */
            font-size: var(--font-xs); /* 12px */
        }
        
        .form-group {
            margin-bottom: var(--space-lg);
        }
        
        .form-label {
            display: block;
            margin-bottom: var(--space-sm);
            color: var(--text-secondary);
            font-weight: 500;
            font-size: var(--font-sm);
        }
        
        .form-input, .form-select, .form-textarea, .form-control {
            width: 100%;
            padding: 0.75rem 1rem; /* 12px 16px */
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 0.5rem; /* 8px */
            color: var(--text-primary);
            font-family: inherit;
            font-size: var(--font-sm);
            transition: all 0.3s ease;
            line-height: 1.5;
        }
        
        .form-input:focus, .form-select:focus, .form-textarea:focus, .form-control:focus {
            outline: none;
            border-color: var(--purple);
            box-shadow: 0 0 0 0.1875rem rgba(153, 69, 255, 0.1); /* 3px */
        }
        
        .form-actions {
            display: flex;
            gap: 0.75rem; /* 12px */
            align-items: center;
            flex-wrap: wrap;
        }
        
        .empty-state {
            text-align: center;
            padding: 3.75rem 1.25rem; /* 60px 20px */
        }
        
        .empty-icon {
            font-size: 4rem; /* 64px */
            color: var(--purple);
            margin-bottom: var(--space-lg);
            opacity: 0.7;
        }
        
        .alert {
            padding: 0.9375rem 1.25rem; /* 15px 20px */
            border-radius: 0.5rem; /* 8px */
            margin-bottom: var(--space-lg);
            font-size: var(--font-sm);
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
        
        /* Grid System with rem units */
        .grid {
            display: grid;
            gap: var(--space-lg);
        }
        
        .grid-2 { grid-template-columns: repeat(2, 1fr); }
        .grid-3 { grid-template-columns: repeat(3, 1fr); }
        .grid-4 { grid-template-columns: repeat(4, 1fr); }
        
        .stat-card {
            text-align: center;
            padding: var(--space-xl);
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
            margin-top: var(--space-xs);
            font-size: var(--font-sm);
        }
        
        .section-title {
            font-size: var(--font-xl);
            margin-bottom: var(--space-lg);
            display: flex;
            align-items: center;
            gap: var(--space-sm);
        }
        
        .qr-preview {
            background: white;
            padding: var(--space-lg);
            border-radius: 0.75rem; /* 12px */
            display: inline-block;
        }
        
        .qr-preview img {
            display: block;
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: var(--space-sm);
            color: var(--text-secondary);
            text-decoration: none;
            margin-bottom: var(--space-lg);
            transition: color 0.2s;
            font-size: var(--font-sm);
        }
        
        .back-link:hover {
            color: var(--purple);
        }
        
        /* Responsive Design - Mobile First */
        @media (max-width: 64rem) { /* 1024px */
            .grid-3, .grid-4 {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 48rem) { /* 768px - Mobile */
            .qr-sidebar {
                transform: translateX(-100%);
            }
            
            .qr-sidebar.open {
                transform: translateX(0);
            }
            
            .qr-main {
                margin-left: 0;
                padding: var(--space-lg) 0.9375rem; /* 24px 15px */
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
                padding: var(--space-lg);
            }
            
            .section-title {
                font-size: var(--font-lg);
            }
            
            .form-input, .form-select, .form-textarea, .form-control {
                font-size: var(--font-sm);
            }
            
            .empty-state {
                padding: var(--space-xl) var(--space-md);
            }
        }
        
        @media (max-width: 30rem) { /* 480px - Small Mobile */
            .qr-main {
                padding: 0.9375rem 0.625rem; /* 15px 10px */
            }
            
            .btn:not(.btn-sm) {
                width: 100%;
                justify-content: center;
                padding: 0.75rem 1rem;
            }
            
            .stat-value {
                font-size: 1.8rem;
            }
            
            .glass-card, .card {
                padding: 0.9375rem; /* 15px */
            }
            
            .section-title {
                font-size: 1.125rem; /* 18px */
            }
            
            .empty-icon {
                font-size: 3rem; /* 48px */
            }
            
            .form-actions {
                flex-direction: column;
                width: 100%;
            }
            
            .form-actions .btn {
                width: 100%;
            }
        }
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
