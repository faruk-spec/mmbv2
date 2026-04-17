<?php use Core\View; use Core\Security; use Core\Auth; ?>
<?php
// Set theme: prefer user's own preference, fall back to global navbar_settings
$defaultTheme = 'dark';
try {
    $db = \Core\Database::getInstance();
    if (\Core\Auth::check()) {
        $userTheme = $db->fetch(
            "SELECT theme_preference FROM user_profiles WHERE user_id = ?",
            [\Core\Auth::id()]
        );
        if ($userTheme && !empty($userTheme['theme_preference'])) {
            $defaultTheme = $userTheme['theme_preference'];
        } else {
            $navbarSettings = $db->fetch("SELECT default_theme FROM navbar_settings WHERE id = 1");
            if ($navbarSettings && !empty($navbarSettings['default_theme'])) {
                $defaultTheme = $navbarSettings['default_theme'];
            }
        }
    } else {
        $navbarSettings = $db->fetch("SELECT default_theme FROM navbar_settings WHERE id = 1");
        if ($navbarSettings && !empty($navbarSettings['default_theme'])) {
            $defaultTheme = $navbarSettings['default_theme'];
        }
    }
} catch (\Exception $e) {
    // Use default if query fails
}
// Apply user display settings (date format etc.)
try {
    if (\Core\Auth::check()) {
        $userSettingsProfile = isset($userProfile) ? $userProfile : 
            $db->fetch("SELECT display_settings FROM user_profiles WHERE user_id = ?", [\Core\Auth::id()]);
        if ($userSettingsProfile && !empty($userSettingsProfile['display_settings'])) {
            $userDisplaySettings = json_decode($userSettingsProfile['display_settings'], true);
            if (!empty($userDisplaySettings['date_format'])) {
                \Core\Helpers::$userDateFormat = $userDisplaySettings['date_format'];
            }
        }
    }
} catch (\Exception $e) {
    // ignore
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?= htmlspecialchars($defaultTheme) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="MyMultiBranch - Multi-Project Platform">
    <meta name="csrf-token" content="<?= Security::generateCsrfToken() ?>">
    <?php if (Auth::check()): ?>
    <meta name="user-id" content="<?= htmlspecialchars($_SESSION['user_unique_id'] ?? (string) Auth::id(), ENT_QUOTES) ?>">
    <?php endif; ?>
    <title><?= View::e($title ?? 'MyMultiBranch') ?> - <?= APP_NAME ?></title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons - No external dependencies needed, using inline SVG -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --bg-primary: #06060a;
            --bg-secondary: #0c0c12;
            --bg-card: #0f0f18;
            --card-inner-bg: #0f0f18;
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
            --bg-primary: #f0f4ff;
            --bg-secondary: #ffffff;
            --bg-card: #ffffff;
            --card-inner-bg: rgba(255, 255, 255, 0.90);
            --cyan: #0369a1;
            --magenta: #c026d3;
            --green: #059669;
            --orange: #d97706;
            --purple: #7c3aed;
            --red: #dc2626;
            --text-primary: #1a1a1a;
            --text-secondary: #555555;
            --border-color: rgba(0, 0, 0, 0.1);
            --shadow-glow: 0 0 20px rgba(124, 58, 237, 0.12);
            --hover-bg: rgba(124, 58, 237, 0.08);
            --shadow: 0 4px 20px rgba(0, 0, 0, 0.12);
        }

        /* Animated light-mode background */
        [data-theme="light"] body {
            background: #f0f4ff;
        }

        [data-theme="light"] body::before {
            content: '';
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: -1;
            animation: lightBgFlow 16s ease-in-out infinite alternate;
        }

        [data-theme="light"] body::after {
            display: none;
        }

        @keyframes lightBgFlow {
            0% {
                background:
                    radial-gradient(ellipse at 10% 10%, rgba(124, 58, 237, 0.14) 0%, transparent 50%),
                    radial-gradient(ellipse at 90% 90%, rgba(0, 153, 204, 0.12) 0%, transparent 50%),
                    radial-gradient(ellipse at 60% 50%, rgba(0, 245, 255, 0.06) 0%, transparent 40%);
            }
            33% {
                background:
                    radial-gradient(ellipse at 85% 15%, rgba(124, 58, 237, 0.12) 0%, transparent 50%),
                    radial-gradient(ellipse at 15% 85%, rgba(0, 153, 204, 0.14) 0%, transparent 50%),
                    radial-gradient(ellipse at 40% 20%, rgba(0, 245, 255, 0.07) 0%, transparent 40%);
            }
            66% {
                background:
                    radial-gradient(ellipse at 50% 90%, rgba(0, 153, 204, 0.10) 0%, transparent 50%),
                    radial-gradient(ellipse at 60% 10%, rgba(124, 58, 237, 0.14) 0%, transparent 50%),
                    radial-gradient(ellipse at 20% 50%, rgba(0, 245, 255, 0.06) 0%, transparent 40%);
            }
            100% {
                background:
                    radial-gradient(ellipse at 10% 10%, rgba(124, 58, 237, 0.14) 0%, transparent 50%),
                    radial-gradient(ellipse at 90% 90%, rgba(0, 153, 204, 0.12) 0%, transparent 50%),
                    radial-gradient(ellipse at 60% 50%, rgba(0, 245, 255, 0.06) 0%, transparent 40%);
            }
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html {
            width: 100%;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            line-height: 1.6;
            font-size: 14px;
            overflow-x: hidden;
            position: relative;
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
            text-align: center;
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
            /* Required by rotating border animation */
            position: relative;
            overflow: hidden;
        }
        
        .card:hover {
            border-color: rgba(0, 240, 255, 0.3);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }

        /* === Professional rotating border-light animation === */
        @keyframes card-border-spin {
            from { transform: translate(-50%, -50%) rotate(0deg); }
            to   { transform: translate(-50%, -50%) rotate(360deg); }
        }

        /* Rotating spotlight sweep — visible only at the card edge */
        .card::before {
            content: '';
            position: absolute;
            width: 180%;
            height: 180%;
            top: 50%;
            left: 50%;
            background: conic-gradient(
                from 0deg,
                transparent 0deg 100deg,
                rgba(124, 58, 237, 0.55) 100deg 160deg,
                rgba(0, 245, 255, 0.45) 160deg 210deg,
                rgba(255, 46, 196, 0.35) 210deg 250deg,
                transparent 250deg 360deg
            );
            animation: card-border-spin 8s linear infinite;
            z-index: 0;
            pointer-events: none;
        }

        /* Inner fill — hides the centre so only the 1 px border edge glows */
        .card::after {
            content: '';
            position: absolute;
            inset: 1px;
            border-radius: 9px;
            background: var(--card-inner-bg);
            z-index: 1;
            pointer-events: none;
        }

        /* Raise all direct children above the animated border layers */
        .card > * {
            position: relative;
            z-index: 2;
        }

        /* Light-mode: softer, accessible palette for the sweep */
        [data-theme="light"] .card::before {
            background: conic-gradient(
                from 0deg,
                transparent 0deg 100deg,
                rgba(124, 58, 237, 0.35) 100deg 160deg,
                rgba(3, 105, 161, 0.30) 160deg 210deg,
                rgba(255, 46, 196, 0.20) 210deg 250deg,
                transparent 250deg 360deg
            );
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
            padding: 0;
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
        
        /* ===== Light Mode Component Overrides ===== */
        [data-theme="light"] .header {
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        }

        [data-theme="light"] .nav a:hover,
        [data-theme="light"] .nav a.active,
        [data-theme="light"] .nav-link:hover {
            background: rgba(124, 58, 237, 0.08);
            color: var(--purple);
        }

        [data-theme="light"] .theme-toggle:hover {
            background: rgba(124, 58, 237, 0.08);
            border-color: var(--purple);
        }

        [data-theme="light"] .dropdown-item:hover {
            background: rgba(124, 58, 237, 0.08);
            color: var(--purple);
        }

        [data-theme="light"] .user-menu-trigger:hover {
            border-color: var(--purple);
        }

        [data-theme="light"] .user-menu-item:hover {
            background: rgba(124, 58, 237, 0.06);
            color: var(--purple);
        }

        [data-theme="light"] .user-menu-dropdown {
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        }

        [data-theme="light"] .table tr:hover td {
            background: rgba(0, 0, 0, 0.03);
        }

        [data-theme="light"] .form-input:focus {
            box-shadow: 0 0 0 3px rgba(3, 105, 161, 0.15);
        }

        [data-theme="light"] .badge-info {
            background: rgba(3, 105, 161, 0.10);
        }

        [data-theme="light"] .btn-secondary:hover {
            box-shadow: 0 0 15px rgba(124, 58, 237, 0.15);
        }

        @media (max-width: 768px) {
            [data-theme="light"] .nav {
                background: rgba(255, 255, 255, 0.98);
                box-shadow: -5px 0 20px rgba(0, 0, 0, 0.12);
            }
        }

        /* Dashboard Layout Styles */
        .full-dashboard-layout {
            display: grid;
            grid-template-columns: 250px 1fr 300px;
            gap: 0;
            min-height: calc(100vh - 60px);
            max-width: 100vw;
            overflow-x: hidden;
        }
        
        .left-sidebar {
            background: var(--bg-card);
            border-right: 1px solid var(--border-color);
            height: calc(100vh - 60px);
            overflow-y: auto;
            overflow-x: hidden;
            transition: width 0.3s ease, left 0.3s ease;
            min-width: 0;
        }
        
        .dashboard-main-content {
            padding: 20px;
            overflow-x: hidden;
            min-width: 0;
        }
        
        .dashboard-sidebar {
            background: var(--bg-primary);
            height: calc(100vh - 60px);
            overflow-y: auto;
            overflow-x: hidden;
            padding: 20px;
            min-width: 0;
        }
    </style>
    
    <?php View::yield('styles'); ?>
</head>
<body>
    <?php if (!empty($_SESSION['_concurrent_session_warning'])): ?>
    <?php $sessionCount = (int)$_SESSION['_concurrent_session_warning']; unset($_SESSION['_concurrent_session_warning']); ?>
    <div id="concurrent-session-banner" style="position:fixed;top:0;left:0;right:0;z-index:99999;background:linear-gradient(135deg,#ff6b6b,#ffaa00);color:#fff;padding:14px 20px;display:flex;align-items:center;justify-content:space-between;box-shadow:0 4px 20px rgba(0,0,0,0.4);font-family:'Poppins',sans-serif;font-size:14px;">
        <div style="display:flex;align-items:center;gap:10px;">
            <i class="fas fa-exclamation-triangle"></i>
            <span>You are already signed in on <strong><?= $sessionCount ?> other device<?= $sessionCount > 1 ? 's' : '' ?></strong>. Want to revoke those sessions?</span>
        </div>
        <div style="display:flex;gap:10px;">
            <form method="POST" action="/security/revoke-sessions-bulk" style="display:inline;">
                <input type="hidden" name="_csrf_token" value="<?= \Core\Security::generateCsrfToken() ?>">
                <input type="hidden" name="revoke_type" value="other">
                <button type="submit" style="background:rgba(255,255,255,0.3);border:1px solid rgba(255,255,255,0.6);color:#fff;padding:6px 14px;border-radius:6px;cursor:pointer;font-size:13px;font-family:inherit;">Revoke Other Sessions</button>
            </form>
            <button onclick="document.getElementById('concurrent-session-banner').style.display='none'" style="background:rgba(0,0,0,0.2);border:1px solid rgba(255,255,255,0.4);color:#fff;padding:6px 14px;border-radius:6px;cursor:pointer;font-size:13px;font-family:inherit;">Dismiss</button>
        </div>
    </div>
    <div style="height:56px;"></div>
    <?php endif; ?>
    <?php
    // Check if a new login happened on another device — runs on every page load so existing
    // sessions always get notified even after the first load post-login.
    if (\Core\Auth::check()) {
        try {
            $dbNL = \Core\Database::getInstance();
            $nlNotify = $dbNL->fetch(
                "SELECT `value` FROM settings WHERE `key` = ?",
                ['new_login_notify_' . \Core\Auth::id()]
            );
            if ($nlNotify) {
                $nlData = json_decode($nlNotify['value'], true);
                $sessionCreatedAt = $_SESSION['_login_time'] ?? 0;
                // Only show if notification is newer than this session (with 5-second buffer)
                if (!empty($nlData['time']) && $nlData['time'] > $sessionCreatedAt + 5) {
                    $_SESSION['_show_new_login_alert'] = $nlData;
                    // Delete so it shows exactly once per notification
                    $dbNL->delete('settings', '`key` = ?', ['new_login_notify_' . \Core\Auth::id()]);
                }
            }
        } catch (\Exception $e) { /* non-fatal */ }
    }
    ?>
    <?php if (!empty($_SESSION['_show_new_login_alert'])): ?>
    <?php $nlAlert = $_SESSION['_show_new_login_alert']; unset($_SESSION['_show_new_login_alert']); ?>
    <div id="new-login-alert-banner" style="position:fixed;top:<?= !empty($_SESSION['_concurrent_session_warning']) ? '56px' : '0' ?>;left:0;right:0;z-index:99998;background:linear-gradient(135deg,#9945ff,#0099cc);color:#fff;padding:12px 20px;display:flex;align-items:center;justify-content:space-between;box-shadow:0 4px 20px rgba(0,0,0,0.4);font-family:'Poppins',sans-serif;font-size:14px;">
        <div style="display:flex;align-items:center;gap:10px;">
            <i class="fas fa-sign-in-alt"></i>
            <span>⚠️ New login detected on your account from IP <strong><?= htmlspecialchars($nlAlert['ip'] ?? 'unknown', ENT_QUOTES) ?></strong>. Not you? <a href="/security" style="color:#fff;text-decoration:underline;">Review sessions</a></span>
        </div>
        <button onclick="this.parentElement.style.display='none'" style="background:rgba(0,0,0,0.2);border:1px solid rgba(255,255,255,0.4);color:#fff;padding:5px 12px;border-radius:6px;cursor:pointer;font-size:13px;font-family:inherit;">✕</button>
    </div>
    <?php endif; ?>
    <?php if (\Core\Auth::check()): ?>
    <script>
    // Live session-alert poller — checks every 30 s for new-login notifications
    (function () {
        function showNewLoginBanner(ip) {
            var existing = document.getElementById('new-login-alert-banner');
            if (existing) return; // already visible
            var banner = document.createElement('div');
            banner.id = 'new-login-alert-banner';
            banner.style.cssText = 'position:fixed;top:0;left:0;right:0;z-index:99998;background:linear-gradient(135deg,#9945ff,#0099cc);color:#fff;padding:12px 20px;display:flex;align-items:center;justify-content:space-between;box-shadow:0 4px 20px rgba(0,0,0,0.4);font-family:\'Poppins\',sans-serif;font-size:14px;';
            banner.innerHTML = '<div style="display:flex;align-items:center;gap:10px;"><i class="fas fa-sign-in-alt"></i><span>⚠️ New login detected on your account from IP <strong>' + (ip || 'unknown') + '</strong>. Not you? <a href="/security" style="color:#fff;text-decoration:underline;">Review sessions</a></span></div>'
                + '<button onclick="this.parentElement.style.display=\'none\'" style="background:rgba(0,0,0,0.2);border:1px solid rgba(255,255,255,0.4);color:#fff;padding:5px 12px;border-radius:6px;cursor:pointer;font-size:13px;font-family:inherit;">✕</button>';
            document.body.insertBefore(banner, document.body.firstChild);
        }
        function poll() {
            // Skip polling when tab is hidden (Page Visibility API)
            if (document.visibilityState && document.visibilityState === 'hidden') return;
            fetch('/api/session-alerts', { credentials: 'same-origin' })
                .then(function(r) { return r.json(); })
                .then(function(d) { if (d && d.alert) showNewLoginBanner(d.alert.ip); })
                .catch(function() {});
        }
        // Poll after 5 s (catches logins that just happened), then every 30 s
        setTimeout(poll, 5000);
        setInterval(poll, 30000);
    })();
    </script>
    <?php endif; ?>
    <?php
    // Initialise user timezone for all date displays in dashboard pages.
    if (\Core\Auth::check()) {
        \Core\Timezone::init(\Core\Auth::id());
    }
    include BASE_PATH . '/views/layouts/navbar.php';
    ?>
    
    <main class="main">
        <?php 
        // Check if this is a dashboard page that needs sidebar
        $isDashboardPage = isset($title) && in_array($title, ['Dashboard', 'Profile', 'Security Settings', 'Activity Log', 'Settings', 'My Plans', 'Subscribe to Plan', 'All Notifications', 'Two-Factor Authentication', 'Backup Codes']);
        ?>
        
        <?php if ($isDashboardPage): ?>
            <!-- Dashboard Layout with Left and Right Sidebars -->
            <div class="full-dashboard-layout">
                <!-- Left Navigation Sidebar -->
                <aside class="left-sidebar" id="leftSidebar">
                    <!-- Toggle Button -->
                    <div style="padding: 16px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--border-color);">
                        <span class="sidebar-title" style="font-size: 0.9rem; font-weight: 700; color: var(--text-primary);">Navigation</span>
                        <button id="sidebarToggle" style="background: none; border: none; color: var(--text-secondary); cursor: pointer; padding: 4px; border-radius: 4px; transition: all 0.3s;" onmouseover="this.style.color='var(--cyan)'" onmouseout="this.style.color='var(--text-secondary)'">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="3" y1="12" x2="21" y2="12"></line>
                                <line x1="3" y1="6" x2="21" y2="6"></line>
                                <line x1="3" y1="18" x2="21" y2="18"></line>
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Navigation Menu -->
                    <nav style="padding: 12px 0;">
                        <!-- Dashboard Section -->
                        <div class="nav-section" style="margin-bottom: 8px;">
                            <a href="/dashboard" class="nav-item" style="display: flex; align-items: center; gap: 12px; padding: 10px 16px; color: var(--text-primary); text-decoration: none; transition: all 0.3s; font-size: 0.85rem;" onmouseover="this.style.background='rgba(0,240,255,0.1)'; this.style.color='var(--cyan)'" onmouseout="this.style.background='transparent'; this.style.color='var(--text-primary)'">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="3" width="7" height="7"></rect>
                                    <rect x="14" y="3" width="7" height="7"></rect>
                                    <rect x="14" y="14" width="7" height="7"></rect>
                                    <rect x="3" y="14" width="7" height="7"></rect>
                                </svg>
                                <span class="nav-text">Dashboard</span>
                            </a>
                        </div>
                        
                        <!-- Account Information Compact Card -->
                        <div class="nav-section" style="margin: 8px 16px 16px 16px; padding: 12px; background: var(--bg-secondary); border-radius: 8px; border: 1px solid var(--border-color);">
                            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 10px;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--cyan)" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>
                                </svg>
                                <span class="nav-text" style="font-size: 0.75rem; font-weight: 700; color: var(--text-secondary); text-transform: uppercase;">Account Info</span>
                            </div>
                            <div class="nav-text" style="margin-bottom: 8px;">
                                <div style="font-size: 0.7rem; color: var(--text-secondary); margin-bottom: 3px;">Email</div>
                                <div style="font-size: 0.75rem; font-weight: 500; color: var(--text-primary); overflow-wrap: break-word;"><?= htmlspecialchars(Auth::user()['email'] ?? 'N/A') ?></div>
                            </div>
                            <?php if (in_array(Auth::user()['role'] ?? '', ['admin', 'super_admin'])): ?>
                            <div class="nav-text">
                                <div style="font-size: 0.7rem; color: var(--text-secondary); margin-bottom: 3px;">Role</div>
                                <div style="display: inline-block; font-size: 0.7rem; padding: 3px 8px; background: rgba(0, 240, 255, 0.1); color: var(--cyan); border-radius: 4px; font-weight: 600; text-transform: capitalize;"><?= htmlspecialchars(Auth::user()['role'] ?? 'User') ?></div>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Account Section -->
                        <div class="nav-section" style="margin-bottom: 8px;">
                            <div class="nav-group">
                                <div class="nav-group-header" style="display: flex; align-items: center; justify-content: space-between; padding: 8px 16px; cursor: pointer; user-select: none;">
                                    <div style="display: flex; align-items: center; gap: 12px; color: var(--text-secondary); font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                            <circle cx="12" cy="7" r="4"></circle>
                                        </svg>
                                        <span class="nav-text">Account</span>
                                    </div>
                                    <svg class="group-chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="var(--text-secondary)" stroke-width="2" style="transition: transform 0.3s;">
                                        <polyline points="6 9 12 15 18 9"></polyline>
                                    </svg>
                                </div>
                                <div class="nav-group-content" style="max-height: 0; overflow: hidden; transition: max-height 0.3s ease;">
                                    <a href="/profile" class="nav-sub-item" style="display: flex; align-items: center; gap: 12px; padding: 8px 16px 8px 44px; color: var(--text-primary); text-decoration: none; transition: all 0.3s; font-size: 0.8rem;" onmouseover="this.style.background='rgba(0,240,255,0.1)'; this.style.color='var(--cyan)'" onmouseout="this.style.background='transparent'; this.style.color='var(--text-primary)'">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                            <circle cx="12" cy="7" r="4"></circle>
                                        </svg>
                                        <span class="nav-text">Profile</span>
                                    </a>
                                    <a href="/security" class="nav-sub-item" style="display: flex; align-items: center; gap: 12px; padding: 8px 16px 8px 44px; color: var(--text-primary); text-decoration: none; transition: all 0.3s; font-size: 0.8rem;" onmouseover="this.style.background='rgba(0,240,255,0.1)'; this.style.color='var(--cyan)'" onmouseout="this.style.background='transparent'; this.style.color='var(--text-primary)'">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                        </svg>
                                        <span class="nav-text">Security Settings</span>
                                    </a>
                                    <a href="/settings" class="nav-sub-item" style="display: flex; align-items: center; gap: 12px; padding: 8px 16px 8px 44px; color: var(--text-primary); text-decoration: none; transition: all 0.3s; font-size: 0.8rem;" onmouseover="this.style.background='rgba(0,240,255,0.1)'; this.style.color='var(--cyan)'" onmouseout="this.style.background='transparent'; this.style.color='var(--text-primary)'">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="3"></circle>
                                            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                                        </svg>
                                        <span class="nav-text">Settings</span>
                                    </a>
                                    <a href="/2fa/setup" class="nav-sub-item" style="display: flex; align-items: center; gap: 12px; padding: 8px 16px 8px 44px; color: var(--text-primary); text-decoration: none; transition: all 0.3s; font-size: 0.8rem;" onmouseover="this.style.background='rgba(0,240,255,0.1)'; this.style.color='var(--cyan)'" onmouseout="this.style.background='transparent'; this.style.color='var(--text-primary)'">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                                            <path d="M9 12l2 2 4-4"></path>
                                        </svg>
                                        <span class="nav-text">Two-Factor Authentication</span>
                                    </a>
                                    <a href="/activity" class="nav-sub-item" style="display: flex; align-items: center; gap: 12px; padding: 8px 16px 8px 44px; color: var(--text-primary); text-decoration: none; transition: all 0.3s; font-size: 0.8rem;" onmouseover="this.style.background='rgba(0,240,255,0.1)'; this.style.color='var(--cyan)'" onmouseout="this.style.background='transparent'; this.style.color='var(--text-primary)'">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                                        </svg>
                                        <span class="nav-text">Activity Log</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Applications Section -->
                        <?php
                        // Load enabled projects dynamically from DB, falling back to config
                        $sidebarProjects = [];
                        try {
                            $_sidebarDb = \Core\Database::getInstance();
                            $sidebarProjects = $_sidebarDb->fetchAll(
                                "SELECT project_key, name, color, url, logo_url, icon FROM home_projects WHERE is_enabled = 1 ORDER BY sort_order ASC"
                            );
                        } catch (\Exception $_e) {}
                        if (empty($sidebarProjects)) {
                            $_cfgProjects = require BASE_PATH . '/config/projects.php';
                            foreach ($_cfgProjects as $_k => $_c) {
                                if (!empty($_c['enabled'])) {
                                    $sidebarProjects[] = [
                                        'project_key' => $_k,
                                        'name'        => $_c['name'] ?? $_k,
                                        'color'       => $_c['color'] ?? '#00f0ff',
                                        'url'         => $_c['url']  ?? '/projects/' . $_k,
                                        'logo_url'    => '',
                                        'icon'        => $_c['icon'] ?? '',
                                    ];
                                }
                            }
                        }
                        // Map of known project keys to SVG path snippets
                        $_iconMap = [
                            'proshare'    => '<path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>',
                            'codexpro'    => '<polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline>',
                            'devzone'     => '<circle cx="12" cy="12" r="10"></circle><polygon points="10 8 16 12 10 16 10 8"></polygon>',
                            'qr'          => '<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>',
                            'resumex'     => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline>',
                            'billx'       => '<rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/>',
                            'notex'       => '<path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>',
                            'whatsapp'    => '<path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>',
                            'convertx'    => '<polyline points="17 1 21 5 17 9"></polyline><path d="M3 11V9a4 4 0 0 1 4-4h14"></path><polyline points="7 23 3 19 7 15"></polyline><path d="M21 13v2a4 4 0 0 1-4 4H3"></path>',
                            'linkshortner'=> '<path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path>',
                            'idcard'      => '<rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>',
                            'formx'       => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><line x1="9" y1="13" x2="15" y2="13"></line><line x1="9" y1="17" x2="15" y2="17"></line>',
                        ];
                        // Map project keys to user-facing sub-routes
                        $_userRoutes = [
                            'proshare'    => ['Dashboard' => '', 'My Files' => '/files', 'Upload' => '/upload', 'Text Share' => '/text'],
                            'codexpro'    => ['Dashboard' => '', 'Editor' => '/editor', 'Projects' => '/projects', 'Snippets' => '/snippets'],
                            'resumex'     => ['Dashboard' => '', 'My Resumes' => '/resumes', 'Templates' => '/templates'],
                            'billx'       => ['Dashboard' => '', 'Bills' => '/bills', 'Create Bill' => '/create'],
                            'convertx'    => ['Dashboard' => '', 'Convert File' => '/convert'],
                            'idcard'      => ['Dashboard' => '', 'My Cards' => '/cards', 'Generate' => '/generate'],
                            'linkshortner'=> ['Dashboard' => '', 'My Links' => '/links', 'Analytics' => '/analytics', 'Settings' => '/settings'],
                            'notex'       => ['Dashboard' => '', 'My Notes' => '/notes', 'Folders' => '/folders', 'Settings' => '/settings'],
                            'whatsapp'    => ['Dashboard' => '', 'Sessions' => '/sessions', 'Messages' => '/messages'],
                            'qr'          => ['Dashboard' => ''],
                            'devzone'     => ['Dashboard' => ''],
                            'formx'       => ['Dashboard' => ''],
                        ];
                        ?>
                        <div class="nav-section" style="margin-bottom: 8px;">
                            <a href="/dashboard" class="nav-item" style="display: flex; align-items: center; gap: 12px; padding: 10px 16px; color: var(--text-primary); text-decoration: none; transition: all 0.3s; font-size: 0.85rem;" onmouseover="this.style.background='rgba(0,240,255,0.1)'; this.style.color='var(--cyan)'" onmouseout="this.style.background='transparent'; this.style.color='var(--text-primary)'">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
                                </svg>
                                <span class="nav-text">Browse Applications</span>
                            </a>

                            <!-- One collapsible group per enabled application -->
                            <?php foreach ($sidebarProjects as $_sp):
                                $_key   = $_sp['project_key'] ?? '';
                                $_color = htmlspecialchars($_sp['color'] ?? '#00f0ff');
                                $_svgIcon = $_iconMap[$_key] ?? '<rect x="3" y="3" width="18" height="18" rx="2"/>';
                                $_userSubs = $_userRoutes[$_key] ?? [];
                                $_baseUrl  = rtrim($_sp['url'] ?? '/projects/' . $_key, '/');
                            ?>
                            <div class="nav-group">
                                <div class="nav-group-header" style="display: flex; align-items: center; justify-content: space-between; padding: 8px 16px; cursor: pointer; user-select: none;">
                                    <div style="display: flex; align-items: center; gap: 10px; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.5px;">
                                        <?php if (!empty($_sp['logo_url'])): ?>
                                            <img src="<?= htmlspecialchars($_sp['logo_url']) ?>" alt="" style="width:16px;height:16px;border-radius:3px;object-fit:cover;flex-shrink:0;">
                                        <?php else: ?>
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="<?= $_color ?>" stroke-width="2"><?= $_svgIcon ?></svg>
                                        <?php endif; ?>
                                        <span class="nav-text"><?= htmlspecialchars($_sp['name'] ?? '') ?></span>
                                    </div>
                                    <svg class="group-chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="var(--text-secondary)" stroke-width="2" style="transition: transform 0.3s;">
                                        <polyline points="6 9 12 15 18 9"></polyline>
                                    </svg>
                                </div>
                                <div class="nav-group-content" style="max-height: 0; overflow: hidden; transition: max-height 0.3s ease;">
                                    <?php if (!empty($_userSubs)):
                                        foreach ($_userSubs as $_label => $_sub):
                                            $_href = $_baseUrl . $_sub;
                                    ?>
                                    <a href="<?= htmlspecialchars($_href) ?>" class="nav-sub-item" style="display: flex; align-items: center; gap: 10px; padding: 7px 16px 7px 44px; color: var(--text-secondary); text-decoration: none; transition: all 0.3s; font-size: 0.8rem;" onmouseover="this.style.background='rgba(0,240,255,0.08)'; this.style.color='var(--cyan)'" onmouseout="this.style.background='transparent'; this.style.color='var(--text-secondary)'">
                                        <?php if ($_label === 'Dashboard' || $_label === 'Overview'): ?>
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                                        <?php elseif (stripos($_label, 'file') !== false || stripos($_label, 'upload') !== false): ?>
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path><polyline points="13 2 13 9 20 9"></polyline></svg>
                                        <?php elseif (stripos($_label, 'analytic') !== false): ?>
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                                        <?php elseif (stripos($_label, 'setting') !== false): ?>
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                                        <?php elseif (stripos($_label, 'create') !== false || stripos($_label, 'generate') !== false || stripos($_label, 'new') !== false): ?>
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                        <?php elseif (stripos($_label, 'message') !== false || stripos($_label, 'text') !== false): ?>
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                                        <?php else: ?>
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 8 12 12 14 14"/></svg>
                                        <?php endif; ?>
                                        <span class="nav-text"><?= htmlspecialchars($_label) ?></span>
                                    </a>
                                    <?php endforeach;
                                    else: ?>
                                    <a href="<?= htmlspecialchars($_baseUrl) ?>" class="nav-sub-item" style="display: flex; align-items: center; gap: 10px; padding: 7px 16px 7px 44px; color: var(--text-secondary); text-decoration: none; transition: all 0.3s; font-size: 0.8rem;" onmouseover="this.style.background='rgba(0,240,255,0.08)'; this.style.color='var(--cyan)'" onmouseout="this.style.background='transparent'; this.style.color='var(--text-secondary)'">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                                        <span class="nav-text">Dashboard</span>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Settings Section -->
                        <div class="nav-section" style="margin-bottom: 8px;">
                            <a href="/settings" class="nav-item" style="display: flex; align-items: center; gap: 12px; padding: 10px 16px; color: var(--text-primary); text-decoration: none; transition: all 0.3s; font-size: 0.85rem;" onmouseover="this.style.background='rgba(0,240,255,0.1)'; this.style.color='var(--cyan)'" onmouseout="this.style.background='transparent'; this.style.color='var(--text-primary)'">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="3"></circle>
                                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                                </svg>
                                <span class="nav-text">Settings</span>
                            </a>
                        </div>

                        <!-- Plans & Subscriptions -->
                        <div class="nav-section" style="margin-bottom: 8px;">
                            <a href="/plans" class="nav-item" style="display: flex; align-items: center; gap: 12px; padding: 10px 16px; color: var(--text-primary); text-decoration: none; transition: all 0.3s; font-size: 0.85rem;<?= strpos($_SERVER['REQUEST_URI'] ?? '', '/plans') !== false ? ' background: rgba(153,69,255,0.15); color: var(--purple);' : '' ?>" onmouseover="this.style.background='rgba(153,69,255,0.15)'; this.style.color='var(--purple)'" onmouseout="this.style.background='<?= strpos($_SERVER['REQUEST_URI'] ?? '', '/plans') !== false ? 'rgba(153,69,255,0.15)' : 'transparent' ?>'; this.style.color='<?= strpos($_SERVER['REQUEST_URI'] ?? '', '/plans') !== false ? 'var(--purple)' : 'var(--text-primary)' ?>'">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/>
                                    <line x1="1" y1="10" x2="23" y2="10"/>
                                </svg>
                                <span class="nav-text">Plans &amp; Subscriptions</span>
                            </a>
                        </div>
                        
                        <!-- Help & Support -->
                        <?php if (\Core\Auth::check()): ?>
                        <div class="nav-section" style="margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--border-color);">
                            <a href="/support/help" class="nav-item" style="display: flex; align-items: center; gap: 12px; padding: 10px 16px; color: var(--text-primary); text-decoration: none; transition: all 0.3s; font-size: 0.85rem;" onmouseover="this.style.background='rgba(0,240,255,0.1)'; this.style.color='var(--cyan)'" onmouseout="this.style.background='transparent'; this.style.color='var(--text-primary)'">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2z"></path>
                                    <path d="m9 12 2 2 4-4"></path>
                                </svg>
                                <span class="nav-text">My Tickets</span>
                            </a>
                            <a href="/support" class="nav-item" style="display: flex; align-items: center; gap: 12px; padding: 10px 16px; color: var(--text-primary); text-decoration: none; transition: all 0.3s; font-size: 0.85rem;" onmouseover="this.style.background='rgba(0,240,255,0.1)'; this.style.color='var(--cyan)'" onmouseout="this.style.background='transparent'; this.style.color='var(--text-primary)'">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                                </svg>
                                <span class="nav-text">Help Center</span>
                            </a>
                        </div>
                        <?php endif; ?>
                    </nav>
                </aside>
                
                <!-- Main Content Area -->
                <div class="dashboard-main-content">
                    <?php View::yield('content'); ?>
                </div>
                
                <!-- Right Sidebar -->
                <aside class="dashboard-sidebar">
                    <!-- User Stats Card -->
                    <div class="sidebar-card" style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 10px; padding: 16px; margin-bottom: 16px;">
                        <h3 style="font-size: 0.9rem; font-weight: 600; margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--cyan)" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                            Account Overview
                        </h3>
                        <div style="space-y: 8px;">
                            <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid var(--border-color); font-size: 0.8rem;">
                                <span style="color: var(--text-secondary);">Status</span>
                                <span style="color: var(--green); font-weight: 600;">Active</span>
                            </div>
                            <?php if (in_array(Auth::user()['role'] ?? '', ['admin', 'super_admin'])): ?>
                            <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid var(--border-color); font-size: 0.8rem;">
                                <span style="color: var(--text-secondary);">Role</span>
                                <span style="color: var(--text-primary); font-weight: 600;"><?= ucfirst(Auth::user()['role'] ?? 'User') ?></span>
                            </div>
                            <?php endif; ?>
                            <div style="display: flex; justify-content: space-between; padding: 8px 0; font-size: 0.8rem;">
                                <span style="color: var(--text-secondary);">Member Since</span>
                                <span style="color: var(--text-primary); font-weight: 600;"><?= date('M Y', strtotime(Auth::user()['created_at'] ?? 'now')) ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Actions Card with Dropdown -->
                    <div class="sidebar-card sidebar-dropdown" style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 10px; padding: 16px; margin-bottom: 16px;">
                        <h3 class="sidebar-dropdown-trigger" style="font-size: 0.9rem; font-weight: 600; margin-bottom: 12px; display: flex; align-items: center; gap: 6px; cursor: pointer; user-select: none;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--magenta)" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                            </svg>
                            Quick Actions
                            <svg class="dropdown-chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="var(--text-secondary)" stroke-width="2" style="margin-left: auto; transition: transform 0.3s;">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </h3>
                        <div class="sidebar-dropdown-content" style="display: flex; flex-direction: column; gap: 8px; max-height: 0; overflow: hidden; transition: max-height 0.3s ease, opacity 0.3s ease; opacity: 0;">
                            <a href="/profile" style="display: flex; align-items: center; gap: 8px; padding: 8px 10px; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 6px; text-decoration: none; color: var(--text-primary); font-size: 0.8rem; transition: all 0.3s;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--cyan)" stroke-width="2">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                                Edit Profile
                            </a>
                            <a href="/security" style="display: flex; align-items: center; gap: 8px; padding: 8px 10px; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 6px; text-decoration: none; color: var(--text-primary); font-size: 0.8rem; transition: all 0.3s;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--magenta)" stroke-width="2">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                </svg>
                                Security
                            </a>
                            <a href="/settings" style="display: flex; align-items: center; gap: 8px; padding: 8px 10px; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 6px; text-decoration: none; color: var(--text-primary); font-size: 0.8rem; transition: all 0.3s;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--orange)" stroke-width="2">
                                    <circle cx="12" cy="12" r="3"></circle>
                                    <path d="M12 1v6m0 6v6m5.2-13.2l-4.2 4.2m-2 2l-4.2 4.2m13.2-5.2l-4.2-4.2m-2 2l-4.2-4.2"></path>
                                </svg>
                                Settings
                            </a>
                            <a href="/activity" style="display: flex; align-items: center; gap: 8px; padding: 8px 10px; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 6px; text-decoration: none; color: var(--text-primary); font-size: 0.8rem; transition: all 0.3s;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2">
                                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                                </svg>
                                Activity Log
                            </a>
                        </div>
                    </div>
                    
                    <!-- Recent Activity Card with Dropdown -->
                    <div class="sidebar-card sidebar-dropdown" style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 10px; padding: 12px; margin-bottom: 16px;">
                        <h3 class="sidebar-dropdown-trigger" style="font-size: 0.85rem; font-weight: 600; margin-bottom: 10px; display: flex; align-items: center; gap: 6px; cursor: pointer; user-select: none;">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="var(--cyan)" stroke-width="2">
                                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                            </svg>
                            Recent Activity
                            <svg class="dropdown-chevron" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="var(--text-secondary)" stroke-width="2" style="margin-left: auto; transition: transform 0.3s;">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </h3>
                        <div class="sidebar-dropdown-content" style="display: flex; flex-direction: column; gap: 4px; max-height: 0; overflow: hidden; transition: max-height 0.3s ease, opacity 0.3s ease; opacity: 0;">
                            <?php
                            try {
                                $db = \Core\Database::getInstance();
                                $recentActivities = $db->fetchAll(
                                    "SELECT * FROM activity_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 5",
                                    [\Core\Auth::id()]
                                );
                                
                                if (empty($recentActivities)):
                            ?>
                                <div style="padding: 6px; text-align: center; color: var(--text-secondary); font-size: 0.7rem;">
                                    No recent activity
                                </div>
                            <?php else: ?>
                                <?php foreach ($recentActivities as $activity): ?>
                                    <div style="padding: 5px 6px; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.7rem;">
                                        <div style="display: flex; align-items: center; gap: 4px; margin-bottom: 2px;">
                                            <span style="font-weight: 600; color: var(--text-primary); text-transform: capitalize; font-size: 0.7rem;"><?= htmlspecialchars($activity['action']) ?></span>
                                        </div>
                                        <div style="color: var(--text-secondary); font-size: 0.65rem;">
                                            <?= \Core\Helpers::timeAgo($activity['created_at']) ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                <a href="/activity" style="display: block; text-align: center; padding: 4px; color: var(--cyan); font-size: 0.7rem; text-decoration: none; margin-top: 2px;">
                                    View All Activity →
                                </a>
                            <?php endif; ?>
                            <?php } catch (\Exception $e) { ?>
                                <div style="padding: 6px; text-align: center; color: var(--text-secondary); font-size: 0.7rem;">
                                    Unable to load activities
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                    
                    <!-- System Status Card -->
                    <div class="sidebar-card" style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 10px; padding: 16px; margin-bottom: 16px;">
                        <h3 style="font-size: 0.9rem; font-weight: 600; margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <polyline points="12 6 12 12 16 14"/>
                            </svg>
                            System Status
                        </h3>
                        <?php
                        // Real system status checks
                        $dbStatus = false;
                        $dbLabel = 'Error';
                        try {
                            $statusDbCheck = \Core\Database::getInstance();
                            $statusDbCheck->fetch("SELECT 1");
                            $dbStatus = true;
                            $dbLabel = 'Connected';
                        } catch (\Exception $_) {
                            $dbLabel = 'Error';
                        }
                        $phpVersion = PHP_VERSION;
                        $memUsage = round(memory_get_usage(true) / 1024 / 1024, 1) . ' MB';
                        ?>
                        <div style="space-y: 8px;">
                            <div style="display: flex; align-items: center; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid var(--border-color); font-size: 0.8rem;">
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <div style="width: 6px; height: 6px; background: var(--green); border-radius: 50%;"></div>
                                    <span style="color: var(--text-secondary);">All Systems</span>
                                </div>
                                <span style="color: var(--green); font-weight: 600; font-size: 0.75rem;">Operational</span>
                            </div>
                            <div style="display: flex; align-items: center; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid var(--border-color); font-size: 0.8rem;">
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <div style="width: 6px; height: 6px; background: <?= $dbStatus ? 'var(--green)' : 'var(--red)' ?>; border-radius: 50%;"></div>
                                    <span style="color: var(--text-secondary);">Database</span>
                                </div>
                                <span style="color: <?= $dbStatus ? 'var(--green)' : 'var(--red)' ?>; font-weight: 600; font-size: 0.75rem;"><?= $dbLabel ?></span>
                            </div>
                            <div style="display: flex; align-items: center; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid var(--border-color); font-size: 0.8rem;">
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <div style="width: 6px; height: 6px; background: var(--cyan); border-radius: 50%;"></div>
                                    <span style="color: var(--text-secondary);">PHP</span>
                                </div>
                                <span style="color: var(--cyan); font-weight: 600; font-size: 0.75rem;"><?= htmlspecialchars($phpVersion) ?></span>
                            </div>
                            <div style="display: flex; align-items: center; justify-content: space-between; padding: 8px 0; font-size: 0.8rem;">
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <div style="width: 6px; height: 6px; background: var(--purple); border-radius: 50%;"></div>
                                    <span style="color: var(--text-secondary);">Memory</span>
                                </div>
                                <span style="color: var(--purple); font-weight: 600; font-size: 0.75rem;"><?= htmlspecialchars($memUsage) ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Help & Support Card -->
                    <div class="sidebar-card" style="background: linear-gradient(135deg, rgba(0, 240, 255, 0.1) 0%, rgba(255, 46, 196, 0.1) 100%); border: 1px solid var(--border-color); border-radius: 10px; padding: 16px;">
                        <h3 style="font-size: 0.9rem; font-weight: 600; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--purple)" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                                <line x1="12" y1="17" x2="12.01" y2="17"></line>
                            </svg>
                            Need Help?
                        </h3>
                        <p style="font-size: 0.75rem; color: var(--text-secondary); margin-bottom: 10px; line-height: 1.4;">Raise a ticket or view your support requests.</p>
                        <a href="/support" style="display: inline-block; padding: 6px 12px; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 6px; text-decoration: none; color: var(--text-primary); font-size: 0.75rem; font-weight: 600; transition: all 0.3s;">
                            My Support Tickets
                        </a>
                    </div>
                </aside>
            </div>
            
            <!-- Mobile Sidebar Toggle Button (Outside Grid) -->
            <button class="mobile-sidebar-toggle" id="mobileSidebarToggle" style="display: none;" aria-label="Toggle sidebar">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <line x1="3" y1="12" x2="21" y2="12"></line>
                    <line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
            </button>
            
            <!-- Sidebar Backdrop for Mobile (Outside Grid) -->
            <div class="sidebar-backdrop" id="sidebarBackdrop"></div>
            
            <style>
                .sidebar-card a:hover {
                    background: rgba(0, 240, 255, 0.1);
                    border-color: var(--cyan);
                    transform: translateX(2px);
                }
                
                .sidebar-dropdown.open .sidebar-dropdown-content {
                    max-height: 500px;
                    opacity: 1;
                    margin-top: 12px;
                }
                
                .sidebar-dropdown.open .dropdown-chevron {
                    transform: rotate(180deg);
                }
                
                .sidebar-dropdown-trigger:hover {
                    color: var(--cyan);
                }
                
                @media (max-width: 1200px) {
                    .full-dashboard-layout {
                        grid-template-columns: 250px 1fr !important;
                    }
                    
                    .dashboard-sidebar {
                        display: none !important;
                    }
                    
                    .dashboard-main-content {
                        max-width: 100%;
                        overflow-x: hidden;
                    }
                }
                
                @media (max-width: 1024px) {
                    .full-dashboard-layout {
                        grid-template-columns: 1fr !important;
                    }
                    
                    .left-sidebar {
                        position: fixed !important;
                        left: -280px !important;
                        top: 60px !important;
                        width: 280px !important;
                        height: calc(100vh - 60px) !important;
                        z-index: 9998 !important;
                        transition: left 0.3s ease !important;
                        box-shadow: 2px 0 10px rgba(0, 0, 0, 0.5);
                    }
                    
                    .left-sidebar .nav-text,
                    .left-sidebar .sidebar-title,
                    .left-sidebar .group-chevron {
                        display: block !important;
                    }
                    
                    .left-sidebar.mobile-open {
                        left: 0 !important;
                    }
                    
                    /* Backdrop overlay */
                    .sidebar-backdrop {
                        display: none;
                        position: fixed;
                        top: 60px;
                        left: 0;
                        right: 0;
                        bottom: 0;
                        background: rgba(0, 0, 0, 0.5);
                        z-index: 9997;
                        opacity: 0;
                        transition: opacity 0.3s ease;
                    }
                    
                    .sidebar-backdrop.active {
                        display: block;
                        opacity: 1;
                    }
                    
                    /* Show FAB toggle button — placed on the LEFT to avoid overlapping the chat widget */
                    .mobile-sidebar-toggle {
                        display: flex !important;
                        position: fixed;
                        bottom: 20px;
                        left: 20px;
                        right: auto;
                        width: 56px;
                        height: 56px;
                        background: linear-gradient(135deg, var(--cyan), var(--magenta));
                        border: none;
                        border-radius: 50%;
                        align-items: center;
                        justify-content: center;
                        cursor: pointer;
                        box-shadow: 0 4px 20px rgba(0, 240, 255, 0.4);
                        z-index: 9999;
                        transition: all 0.3s ease;
                    }
                    
                    .mobile-sidebar-toggle:hover {
                        transform: scale(1.1);
                        box-shadow: 0 6px 25px rgba(0, 240, 255, 0.6);
                    }
                    
                    .mobile-sidebar-toggle svg {
                        color: var(--bg-primary);
                    }
                    
                    .dashboard-sidebar {
                        display: none !important;
                    }
                    
                    /* Ensure main content doesn't overflow */
                    .dashboard-main-content {
                        max-width: 100%;
                        overflow-x: hidden;
                    }
                }
                
                @media (max-width: 768px) {
                    /* Mobile-specific adjustments (smaller padding, etc.) */
                    .dashboard-main-content {
                        padding: 12px !important;
                        max-width: 100vw;
                        overflow-x: hidden;
                    }
                    
                    .card {
                        border-radius: 8px !important;
                    }
                    
                    .card-header {
                        padding: 10px !important;
                    }
                    
                    .card-title {
                        font-size: 0.9rem !important;
                    }
                }
                
                /* Left Sidebar Scrollbar */
                .left-sidebar::-webkit-scrollbar {
                    width: 6px;
                }
                
                .left-sidebar::-webkit-scrollbar-track {
                    background: transparent;
                }
                
                .left-sidebar::-webkit-scrollbar-thumb {
                    background: var(--border-color);
                    border-radius: 3px;
                }
                
                .left-sidebar::-webkit-scrollbar-thumb:hover {
                    background: var(--text-secondary);
                }
            </style>
            
            <script>
            // Sidebar dropdown functionality (Right Sidebar)
            (function() {
                document.addEventListener('DOMContentLoaded', function() {
                    const dropdownTriggers = document.querySelectorAll('.sidebar-dropdown-trigger');
                    
                    dropdownTriggers.forEach(trigger => {
                        trigger.addEventListener('click', function() {
                            const dropdown = this.closest('.sidebar-dropdown');
                            const isOpen = dropdown.classList.contains('open');
                            
                            // Close all other dropdowns
                            document.querySelectorAll('.sidebar-dropdown.open').forEach(d => {
                                if (d !== dropdown) {
                                    d.classList.remove('open');
                                }
                            });
                            
                            // Toggle current dropdown
                            dropdown.classList.toggle('open');
                        });
                    });
                    
                    // Open Quick Actions and Recent Activity by default
                    document.querySelectorAll('.sidebar-dropdown').forEach(dropdown => {
                        dropdown.classList.add('open');
                    });
                    
                    // Left Sidebar Navigation Groups
                    const navGroups = document.querySelectorAll('.nav-group');
                    const currentPath = window.location.pathname;
                    
                    navGroups.forEach(group => {
                        const header = group.querySelector('.nav-group-header');
                        const content = group.querySelector('.nav-group-content');
                        const chevron = group.querySelector('.group-chevron');
                        
                        // Check if this group contains a link matching the current path
                        const links = content ? content.querySelectorAll('a[href]') : [];
                        let hasActiveLink = false;
                        links.forEach(link => {
                            const href = link.getAttribute('href');
                            if (href && (currentPath === href || currentPath.startsWith(href + '/'))) {
                                link.style.color = 'var(--cyan)';
                                link.style.background = 'rgba(0,240,255,0.08)';
                                hasActiveLink = true;
                            }
                        });
                        
                        // Expand the group if it contains the active page
                        if (hasActiveLink) {
                            group.classList.add('open');
                            if (content) content.style.maxHeight = content.scrollHeight + 'px';
                            if (chevron) chevron.style.transform = 'rotate(180deg)';
                        } else {
                            group.classList.remove('open');
                            if (content) content.style.maxHeight = '0';
                            if (chevron) chevron.style.transform = 'rotate(0deg)';
                        }
                        
                        // Add click handler
                        if (header) {
                            header.addEventListener('click', function(e) {
                                e.stopPropagation();
                                const isOpen = group.classList.contains('open');
                                
                                if (isOpen) {
                                    // Close this group
                                    group.classList.remove('open');
                                    if (content) content.style.maxHeight = '0';
                                    if (chevron) chevron.style.transform = 'rotate(0deg)';
                                } else {
                                    // Close all other groups first (accordion)
                                    navGroups.forEach(otherGroup => {
                                        if (otherGroup !== group && otherGroup.classList.contains('open')) {
                                            otherGroup.classList.remove('open');
                                            const otherContent = otherGroup.querySelector('.nav-group-content');
                                            const otherChevron = otherGroup.querySelector('.group-chevron');
                                            if (otherContent) otherContent.style.maxHeight = '0';
                                            if (otherChevron) otherChevron.style.transform = 'rotate(0deg)';
                                        }
                                    });
                                    // Open this group
                                    group.classList.add('open');
                                    if (content) content.style.maxHeight = content.scrollHeight + 'px';
                                    if (chevron) chevron.style.transform = 'rotate(180deg)';
                                }
                            });
                        }
                    });
                    
                    // Left Sidebar Toggle
                    const sidebar = document.getElementById('leftSidebar');
                    const toggleBtn = document.getElementById('sidebarToggle');
                    const dashboardLayout = document.querySelector('.full-dashboard-layout');
                    const mobileSidebarToggle = document.getElementById('mobileSidebarToggle');
                    const sidebarBackdrop = document.getElementById('sidebarBackdrop');
                    
                    // Mobile sidebar toggle
                    if (mobileSidebarToggle && sidebar && sidebarBackdrop) {
                        mobileSidebarToggle.addEventListener('click', function() {
                            sidebar.classList.toggle('mobile-open');
                            sidebarBackdrop.classList.toggle('active');
                        });
                        
                        // Close sidebar when clicking backdrop
                        sidebarBackdrop.addEventListener('click', function() {
                            sidebar.classList.remove('mobile-open');
                            sidebarBackdrop.classList.remove('active');
                        });
                        
                        // Close sidebar when clicking a link on mobile
                        const sidebarLinks = sidebar.querySelectorAll('a');
                        sidebarLinks.forEach(link => {
                            link.addEventListener('click', function() {
                                if (window.innerWidth <= 768) {
                                    sidebar.classList.remove('mobile-open');
                                    sidebarBackdrop.classList.remove('active');
                                }
                            });
                        });
                    }
                    
                    // Desktop sidebar toggle
                    if (toggleBtn && sidebar) {
                        toggleBtn.addEventListener('click', function() {
                            const isCollapsed = sidebar.classList.contains('collapsed');
                            
                            if (isCollapsed) {
                                sidebar.classList.remove('collapsed');
                                sidebar.style.width = '250px';
                                dashboardLayout.style.gridTemplateColumns = '250px 1fr 300px';
                                document.querySelectorAll('.nav-text, .sidebar-title').forEach(el => {
                                    el.style.display = 'inline';
                                });
                                document.querySelectorAll('.group-chevron').forEach(el => {
                                    el.style.display = 'block';
                                });
                            } else {
                                sidebar.classList.add('collapsed');
                                sidebar.style.width = '60px';
                                dashboardLayout.style.gridTemplateColumns = '60px 1fr 300px';
                                document.querySelectorAll('.nav-text, .sidebar-title').forEach(el => {
                                    el.style.display = 'none';
                                });
                                document.querySelectorAll('.group-chevron').forEach(el => {
                                    el.style.display = 'none';
                                });
                                // Close all nav groups when collapsed
                                navGroups.forEach(group => {
                                    group.classList.remove('open');
                                    const content = group.querySelector('.nav-group-content');
                                    if (content) content.style.maxHeight = '0';
                                });
                            }
                        });
                    }
                });
            })();
            </script>
        <?php else: ?>
            <!-- Regular Content Layout -->
            <?php View::yield('content'); ?>
        <?php endif; ?>
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
    
    <!-- Toast Notification System -->
    <script src="/public/assets/js/toast.js"></script>
    <script src="/public/assets/js/qrcode.js"></script>

    <!-- ── Post-logout Login Suggestion Popup ───────────────────────────────── -->
    <?php if (isset($_GET['logged_out']) && !(\Core\Auth::check())): ?>
    <div id="loggedOutPopup" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;z-index:99998;align-items:center;justify-content:center;background:rgba(0,0,0,0.5);backdrop-filter:blur(4px);">
        <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:16px;padding:20px 22px;box-shadow:0 12px 48px rgba(0,0,0,0.45);transform:translateY(20px);opacity:0;transition:transform 0.4s cubic-bezier(.34,1.56,.64,1),opacity 0.3s ease;max-width:340px;width:calc(100% - 40px);position:relative;" id="loggedOutCard">
            <button onclick="closeLoggedOutPopup()" style="position:absolute;top:12px;right:14px;background:none;border:none;color:var(--text-secondary);cursor:pointer;font-size:1.1rem;line-height:1;" aria-label="Close">&times;</button>
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;">
                <div style="width:40px;height:40px;border-radius:10px;background:linear-gradient(135deg,var(--cyan),var(--purple));display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#06060a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </div>
                <div>
                    <div style="font-size:0.95rem;font-weight:700;color:var(--text-primary);">You've been signed out</div>
                    <div style="font-size:0.8rem;color:var(--text-secondary);margin-top:2px;">See you next time!</div>
                </div>
            </div>
            <p style="font-size:0.82rem;color:var(--text-secondary);margin:0 0 14px;line-height:1.5;">Sign back in to pick up right where you left off — your work is safe and waiting for you.</p>
            <div style="display:flex;gap:8px;">
                <a href="/login" style="flex:1;padding:9px 14px;border-radius:8px;background:linear-gradient(135deg,var(--cyan),var(--purple));color:#06060a;font-size:0.85rem;font-weight:700;text-decoration:none;text-align:center;">Sign In</a>
                <a href="/register" style="flex:1;padding:9px 14px;border-radius:8px;border:1px solid var(--border-color);background:transparent;color:var(--text-primary);font-size:0.85rem;font-weight:600;text-decoration:none;text-align:center;">Register</a>
            </div>
        </div>
    </div>
    <script>
    (function () {
        var popup = document.getElementById('loggedOutPopup');
        var card  = document.getElementById('loggedOutCard');
        if (!popup) return;
        // Show after short delay
        setTimeout(function () {
            popup.style.display = 'flex';
            requestAnimationFrame(function () {
                requestAnimationFrame(function () {
                    card.style.transform = 'translateY(0)';
                    card.style.opacity   = '1';
                });
            });
        }, 600);
        // Auto-dismiss after 12 seconds
        setTimeout(function () { closeLoggedOutPopup(); }, 12600);

        function closeLoggedOutPopup() {
            card.style.transform = 'translateY(20px)';
            card.style.opacity   = '0';
            setTimeout(function () { popup.style.display = 'none'; }, 350);
        }
        window.closeLoggedOutPopup = closeLoggedOutPopup;
    })();
    </script>
    <?php endif; ?>

    <?php
    // ── 2FA Setup Reminder Popup ──────────────────────────────────────────────
    $currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
    if (\Core\Auth::check() && $currentPath !== '/2fa/setup') {
        $u2fa = \Core\Auth::user();
        $has2fa = !empty($u2fa['two_factor_enabled']) && (int)$u2fa['two_factor_enabled'] === 1;
        if (!$has2fa) {
            $createdAt   = strtotime($u2fa['created_at'] ?? 'now');
            $isNewUser   = (time() - $createdAt) < 86400; // account < 24 h old
            $today       = date('Y-m-d');
            $lastPrompt  = $_SESSION['2fa_prompt_shown_date'] ?? '';
            $showPopup   = $lastPrompt !== $today;
            if ($showPopup) {
                $_SESSION['2fa_prompt_shown_date'] = $today;
            }
        } else {
            $showPopup = false;
            $isNewUser = false;
        }
    } else {
        $showPopup = false;
        $isNewUser = false;
    }
    ?>
    <?php if (!empty($showPopup)): ?>
    <div id="twoFaPopup" style="display:flex;position:fixed;top:0;left:0;width:100%;height:100%;z-index:99999;align-items:center;justify-content:center;background:rgba(0,0,0,0.65);backdrop-filter:blur(5px);">
        <div id="twoFaCard" style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:18px;padding:28px 26px 24px;box-shadow:0 16px 56px rgba(0,0,0,0.55);max-width:360px;width:calc(100% - 40px);position:relative;transform:translateY(24px);opacity:0;transition:transform 0.4s cubic-bezier(.34,1.56,.64,1),opacity 0.3s ease;">
            <button onclick="closeTwoFaPopup()" style="position:absolute;top:12px;right:14px;background:none;border:none;color:var(--text-secondary);cursor:pointer;font-size:1.2rem;line-height:1;" aria-label="Close">&times;</button>
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:14px;">
                <div style="width:42px;height:42px;border-radius:50%;background:linear-gradient(135deg,var(--cyan),var(--purple));display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="fas fa-shield-alt" style="color:#06060a;font-size:1.1rem;"></i>
                </div>
                <div>
                    <div style="font-weight:700;font-size:1rem;color:var(--text-primary);">
                        <?php if (!empty($isNewUser)): ?>Secure your account<?php else: ?>Enable Two-Factor Auth<?php endif; ?>
                    </div>
                    <div style="font-size:0.75rem;color:var(--text-secondary);">Protect your account with 2FA</div>
                </div>
            </div>
            <p style="color:var(--text-secondary);font-size:0.85rem;margin-bottom:18px;line-height:1.5;">
                <?php if (!empty($isNewUser)): ?>
                    Welcome! Please set up two-factor authentication to keep your account safe.
                <?php else: ?>
                    Your account doesn't have two-factor authentication enabled. Enable it now to add an extra layer of security.
                <?php endif; ?>
            </p>
            <div style="display:flex;gap:8px;flex-direction:column;">
                <a href="/2fa/setup" style="display:block;padding:10px 14px;border-radius:9px;background:linear-gradient(135deg,var(--cyan),var(--purple));color:#06060a;font-size:0.9rem;font-weight:700;text-decoration:none;text-align:center;">
                    <i class="fas fa-qrcode" style="margin-right:6px;"></i>Set up 2FA now
                </a>
                <button onclick="closeTwoFaPopup()" style="padding:9px 14px;border-radius:9px;border:1px solid var(--border-color);background:transparent;color:var(--text-secondary);font-size:0.85rem;font-weight:500;cursor:pointer;width:100%;">
                    Remind me later
                </button>
            </div>
        </div>
    </div>
    <script>
    (function () {
        var popup = document.getElementById('twoFaPopup');
        var card  = document.getElementById('twoFaCard');
        if (!popup) return;
        setTimeout(function () {
            requestAnimationFrame(function () {
                requestAnimationFrame(function () {
                    card.style.transform = 'translateY(0)';
                    card.style.opacity   = '1';
                });
            });
        }, 400);
        function closeTwoFaPopup() {
            card.style.transform = 'translateY(20px)';
            card.style.opacity   = '0';
            setTimeout(function () { popup.style.display = 'none'; }, 350);
        }
        window.closeTwoFaPopup = closeTwoFaPopup;
    })();
    </script>
    <?php endif; ?>

    <?php View::yield('scripts'); ?>
<script>
(function() {
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('form').forEach(function(form) {
            form.addEventListener('invalid', function(e) {
                e.preventDefault();
                var field = e.target;
                var existing = field.parentElement.querySelector('.client-form-error');
                var msg = field.validationMessage || 'This field is required.';
                if (!existing) {
                    var errEl = document.createElement('div');
                    errEl.className = 'client-form-error';
                    errEl.style.cssText = 'color:#ff6b6b;font-size:12px;margin-top:4px;';
                    errEl.textContent = msg;
                    field.parentElement.appendChild(errEl);
                } else {
                    existing.textContent = msg;
                }
                field.style.borderColor = '#ff6b6b';
            }, true);
            form.addEventListener('input', function(e) {
                var field = e.target;
                if (field.checkValidity && field.checkValidity()) {
                    var existing = field.parentElement.querySelector('.client-form-error');
                    if (existing) existing.remove();
                    field.style.borderColor = '';
                }
            }, true);
        });
    });
})();
</script>

<!-- SSE real-time notifications (user layout) -->
<script>
(function () {
    // Only run for logged-in users
    if (!document.querySelector('meta[name="user-id"]')) return;
    if (typeof EventSource === 'undefined') return;

    var lastId = 0;
    var sseRetryDelay = 5000;  // start at 5 s after an error
    var sseMaxDelay   = 60000; // cap at 60 s
    var es;

    function showNotifToast(notif) {
        if (!notif || !notif.message) return;
        var toast = document.createElement('div');
        toast.style.cssText = 'position:fixed;bottom:20px;right:20px;z-index:9999;' +
            'background:var(--bg-secondary,#1a1a2e);border:1px solid var(--border-color,#333);' +
            'border-radius:10px;padding:14px 18px;max-width:320px;' +
            'box-shadow:0 4px 20px rgba(0,0,0,0.35);font-family:inherit;' +
            'animation:sseSlideIn .3s ease;';

        var titleEl = document.createElement('div');
        titleEl.style.cssText = 'font-weight:600;color:var(--text-primary,#eee);font-size:.9rem;margin-bottom:4px;display:flex;align-items:center;gap:6px;';
        titleEl.innerHTML = '<span style="width:8px;height:8px;border-radius:50%;background:var(--cyan,#00f0ff);flex-shrink:0;display:inline-block;"></span>New Notification';

        var msgEl = document.createElement('div');
        msgEl.style.cssText = 'color:var(--text-secondary,#aaa);font-size:.85rem;';
        msgEl.textContent = notif.message;

        toast.appendChild(titleEl);
        toast.appendChild(msgEl);
        document.body.appendChild(toast);
        setTimeout(function () { if (toast.parentNode) toast.parentNode.removeChild(toast); }, 6000);
    }

    function updateBadge(count) {
        // The notification bell badge is in navbar.php with id="notifBadge"
        var badge = document.getElementById('notifBadge');
        if (!badge) return;
        badge.textContent = Math.min(count, 99);
        if (count > 0) {
            badge.style.display = 'flex';
            badge.classList.add('has-unread');
        } else {
            badge.style.display = 'none';
            badge.classList.remove('has-unread');
        }
    }

    function connectSSE() {
        if (es) { try { es.close(); } catch(e) {} }
        var url = '/notifications/stream' + (lastId ? '?last_id=' + lastId : '');
        es = new EventSource(url);

        es.onmessage = function (e) {
            sseRetryDelay = 5000; // reset back-off on success
            try {
                var data = JSON.parse(e.data);
                if (data.type === 'notification') {
                    updateBadge(data.unread_count || 0);
                    if (data.notification && data.notification.id) {
                        lastId = data.notification.id;
                        showNotifToast(data.notification);
                    }
                }
            } catch (err) { /* ignore malformed event */ }
        };

        // EventSource auto-reconnects using the server's retry: value.
        // onerror only fires on hard errors (e.g. 401, network down).
        es.onerror = function () {
            es.close();
            setTimeout(connectSSE, sseRetryDelay);
            sseRetryDelay = Math.min(sseRetryDelay * 2, sseMaxDelay);
        };
    }

    // Small delay so the page settles; then start polling
    setTimeout(connectSSE, 2000);

    // Also re-connect when the tab becomes visible again after being hidden
    document.addEventListener('visibilitychange', function () {
        if (!document.hidden) connectSSE();
    });
})();
</script>
<style>@keyframes sseSlideIn{from{transform:translateY(10px);opacity:0}to{transform:translateY(0);opacity:1}}</style>
<style>
#support-chat-widget { position:fixed; bottom:24px; right:24px; z-index:10000; }
#support-chat-panel  { display:none; position:absolute; bottom:70px; right:0; width:340px; background:var(--bg-card,#0f0f18); border:1px solid var(--border-color,rgba(255,255,255,.1)); border-radius:16px; box-shadow:0 8px 32px rgba(0,0,0,.5); overflow:hidden; flex-direction:column; }
@media (max-width:480px) {
    #support-chat-widget { bottom:16px; right:12px; }
    #support-chat-panel  { position:fixed; bottom:80px; right:8px; left:8px; width:auto; border-radius:14px; }
}
</style>
<!-- Support Live Chat Widget -->
<div id="support-chat-widget">
    <!-- Chat bubble button -->
    <button id="support-chat-btn" onclick="toggleSupportChat()" style="width:54px;height:54px;border-radius:50%;background:linear-gradient(135deg,#00f0ff,#ff2ec4);border:none;cursor:pointer;box-shadow:0 4px 20px rgba(0,240,255,0.4);display:flex;align-items:center;justify-content:center;font-size:22px;color:white;transition:transform .2s;" title="Live Support">
        <i class="fas fa-headset"></i>
    </button>
    <!-- Chat panel -->
    <div id="support-chat-panel">
        <!-- Header -->
        <div style="background:linear-gradient(135deg,#00f0ff22,#ff2ec422);padding:14px 16px;display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid var(--border-color,rgba(255,255,255,.08));">
            <div style="display:flex;align-items:center;gap:8px;font-weight:600;color:var(--text-primary,#e8eefc);font-size:.95rem;">
                <i class="fas fa-headset" style="color:#00f0ff;"></i> Live Support
            </div>
            <button onclick="toggleSupportChat()" style="background:none;border:none;color:var(--text-secondary,#8892a6);cursor:pointer;font-size:16px;"><i class="fas fa-times"></i></button>
        </div>
        <!-- Start form (shown when no active chat) -->
        <div id="chat-start-form" style="padding:20px;">
            <p style="color:var(--text-secondary,#8892a6);font-size:.85rem;margin-bottom:16px;">Chat with our support team or AI assistant.</p>
            <div id="chat-start-error" style="display:none;padding:8px 10px;border-radius:8px;background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.28);color:#f87171;font-size:.8rem;margin-bottom:10px;"></div>
            <?php if (!\Core\Auth::check()): ?>
            <input type="text" id="chat-guest-name" placeholder="Your name" style="width:100%;padding:10px;border:1px solid var(--border-color,rgba(255,255,255,.1));border-radius:8px;background:var(--bg-secondary,#0c0c12);color:var(--text-primary,#e8eefc);margin-bottom:10px;font-size:.85rem;box-sizing:border-box;">
            <input type="email" id="chat-guest-email" placeholder="Your email" style="width:100%;padding:10px;border:1px solid var(--border-color,rgba(255,255,255,.1));border-radius:8px;background:var(--bg-secondary,#0c0c12);color:var(--text-primary,#e8eefc);margin-bottom:10px;font-size:.85rem;box-sizing:border-box;">
            <?php endif; ?>
            <button onclick="startSupportChat()" style="width:100%;padding:10px;background:linear-gradient(135deg,#00f0ff,#ff2ec4);border:none;border-radius:8px;color:white;font-weight:600;cursor:pointer;font-size:.9rem;">Start Chat</button>
        </div>
        <!-- Messages area -->
        <div id="chat-messages-area" style="display:none;flex-direction:column;height:300px;">
            <div id="chat-messages" style="flex:1;overflow-y:auto;padding:12px;display:flex;flex-direction:column;gap:8px;height:240px;"></div>
            <!-- Message input -->
            <div style="padding:10px;border-top:1px solid var(--border-color,rgba(255,255,255,.08));display:flex;gap:8px;">
                <input type="text" id="chat-input" placeholder="Type a message..." style="flex:1;padding:8px 12px;border:1px solid var(--border-color,rgba(255,255,255,.1));border-radius:20px;background:var(--bg-secondary,#0c0c12);color:var(--text-primary,#e8eefc);font-size:.85rem;" onkeydown="if(event.key==='Enter')sendChatMessage()">
                <button onclick="sendChatMessage()" style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#00f0ff,#ff2ec4);border:none;cursor:pointer;color:white;font-size:14px;display:flex;align-items:center;justify-content:center;"><i class="fas fa-paper-plane"></i></button>
            </div>
            <!-- Close chat button -->
            <div style="padding:6px 10px;border-top:1px solid var(--border-color,rgba(255,255,255,.08));text-align:center;">
                <button onclick="closeSupportChat()" style="background:none;border:none;color:#ff6b6b;font-size:.78rem;cursor:pointer;">End this chat session</button>
            </div>
        </div>
    </div>
</div>
<script>
(function(){
    var chatKey = null;
    var chatActive = false;
    var pollTimer = null;
    var lastMsgId = 0;

    window.toggleSupportChat = function() {
        var panel = document.getElementById('support-chat-panel');
        if (panel.style.display === 'none' || !panel.style.display) {
            panel.style.display = 'flex';
            panel.style.flexDirection = 'column';
            // Check for existing session
            var stored = sessionStorage.getItem('support_chat_key');
            if (stored) { chatKey = stored; resumeChat(); }
        } else {
            panel.style.display = 'none';
        }
    };

    window.startSupportChat = function() {
        var name = (document.getElementById('chat-guest-name') || {value:''}).value.trim();
        var email = (document.getElementById('chat-guest-email') || {value:''}).value.trim();
        var errorBox = document.getElementById('chat-start-error');
        if (errorBox) { errorBox.style.display = 'none'; errorBox.textContent = ''; }
        var csrfMeta = document.querySelector('meta[name="csrf-token"]');
        var csrf = csrfMeta ? csrfMeta.getAttribute('content') : '';
        fetch('/support/live/start', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: '_csrf_token=' + encodeURIComponent(csrf) + '&guest_name=' + encodeURIComponent(name) + '&guest_email=' + encodeURIComponent(email)
        }).then(function(r){return r.json();}).then(function(d){
            if (d.success) {
                chatKey = d.session_key;
                sessionStorage.setItem('support_chat_key', chatKey);
                showChatMessages();
                if (d.messages) renderMessages(d.messages);
                startPolling();
            } else if (errorBox) {
                errorBox.textContent = d.error || 'Unable to start chat right now.';
                errorBox.style.display = 'block';
            }
        }).catch(function(){
            if (errorBox) {
                errorBox.textContent = 'Unable to start chat right now. Please try again.';
                errorBox.style.display = 'block';
            }
        });
    };

    function resumeChat() {
        fetch('/support/live/messages?session_key=' + encodeURIComponent(chatKey))
        .then(function(r){return r.json();}).then(function(d){
            if (d.status === 'closed') { chatKey = null; sessionStorage.removeItem('support_chat_key'); return; }
            showChatMessages();
            renderMessages(d.messages || []);
            startPolling();
        }).catch(function(){});
    }

    function showChatMessages() {
        document.getElementById('chat-start-form').style.display = 'none';
        document.getElementById('chat-messages-area').style.display = 'flex';
        document.getElementById('chat-messages-area').style.flexDirection = 'column';
        chatActive = true;
    }

    window.sendChatMessage = function() {
        var input = document.getElementById('chat-input');
        var msg = input.value.trim();
        if (!msg || !chatKey) return;
        input.value = '';
        var csrf = (document.querySelector('meta[name="csrf-token"]') || {getAttribute:function(){return '';}}).getAttribute('content');
        fetch('/support/live/send', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: '_csrf_token=' + encodeURIComponent(csrf) + '&session_key=' + encodeURIComponent(chatKey) + '&message=' + encodeURIComponent(msg)
        }).then(function(r){return r.json();}).then(function(d){
            if (d.success) pollMessages();
        }).catch(function(){});
    };

    window.closeSupportChat = function() {
        if (!chatKey) return;
        var csrf = (document.querySelector('meta[name="csrf-token"]') || {getAttribute:function(){return '';}}).getAttribute('content');
        fetch('/support/live/close', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: '_csrf_token=' + encodeURIComponent(csrf) + '&session_key=' + encodeURIComponent(chatKey)
        }).then(function(){
            chatKey = null; chatActive = false;
            sessionStorage.removeItem('support_chat_key');
            clearInterval(pollTimer);
            document.getElementById('chat-start-form').style.display = 'block';
            document.getElementById('chat-messages-area').style.display = 'none';
            document.getElementById('chat-messages').innerHTML = '';
        });
    };

    function startPolling() {
        if (pollTimer) clearInterval(pollTimer);
        pollTimer = setInterval(pollMessages, 4000);
    }

    function pollMessages() {
        if (!chatKey) return;
        fetch('/support/live/messages?session_key=' + encodeURIComponent(chatKey) + '&after=' + lastMsgId)
        .then(function(r){return r.json();}).then(function(d){
            if (d.status === 'closed') { chatActive = false; clearInterval(pollTimer); return; }
            if (d.messages && d.messages.length) renderMessages(d.messages);
        }).catch(function(){});
    }

    function renderMessages(msgs) {
        var container = document.getElementById('chat-messages');
        msgs.forEach(function(m) {
            if (m.id && m.id <= lastMsgId) return;
            if (m.id) lastMsgId = Math.max(lastMsgId, m.id);
            var div = document.createElement('div');
            var isUser = (m.sender_type === 'user' || m.sender_type === 'guest');
            div.style.cssText = 'max-width:80%;padding:8px 12px;border-radius:12px;font-size:.82rem;line-height:1.5;word-break:break-word;' +
                (isUser ? 'align-self:flex-end;background:linear-gradient(135deg,rgba(0,240,255,.25),rgba(255,46,196,.2));margin-left:auto;' : 'align-self:flex-start;background:var(--bg-secondary,rgba(255,255,255,.06));border:1px solid var(--border-color,rgba(255,255,255,.08));');
            div.style.color = 'var(--text-primary,#e8eefc)';
            div.textContent = m.message;
            container.appendChild(div);
        });
        container.scrollTop = container.scrollHeight;
    }

    // Hide chat widget entirely on project sub-pages
    if (window.location.pathname.indexOf('/projects/') === 0) {
        var widget = document.getElementById('support-chat-widget');
        if (widget) widget.style.display = 'none';
    }
})();
</script>
</body>
</html>
