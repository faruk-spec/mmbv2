<?php use Core\View; use Core\Security; use Core\Auth; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= View::e($title ?? 'SheetDocs') ?> - MyMultiBranch</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script>
        // Sync theme with universal navbar (before page renders to avoid flash)
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'dark';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>
    
    <style>
        :root {
            --bg-primary: #06060a;
            --bg-secondary: #0c0c12;
            --bg-card: #0f0f18;
            --cyan: #00d4aa;
            --text-primary: #e8eefc;
            --text-secondary: #8892a6;
            --border-color: rgba(255, 255, 255, 0.1);
            --sidebar-width: 280px;
        }
        
        /* Light theme variables */
        [data-theme="light"] {
            --bg-primary: #f5f7fa;
            --bg-secondary: #ffffff;
            --bg-card: #ffffff;
            --text-primary: #1a1a1a;
            --text-secondary: #6b7280;
            --border-color: rgba(0, 0, 0, 0.1);
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
        }
        
        .container {
            display: flex;
            min-height: calc(100vh - 60px);
            margin-top: 60px;
        }
        
        .sidebar {
            width: var(--sidebar-width);
            background: rgba(12, 12, 18, 0.95);
            border-right: 1px solid var(--border-color);
            position: fixed;
            height: calc(100vh - 60px);
            overflow-y: auto;
            padding: 20px;
            top: 60px;
        }
        
        [data-theme="light"] .sidebar {
            background: var(--bg-secondary);
        }
        
        .logo {
            font-size: 24px;
            font-weight: 700;
            color: var(--cyan);
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .nav-item {
            padding: 12px 20px;
            margin-bottom: 8px;
            border-radius: 8px;
            color: var(--text-secondary);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.3s;
        }
        
        .nav-item:hover, .nav-item.active {
            background: rgba(0, 212, 170, 0.1);
            color: var(--cyan);
        }
        
        .main-content {
            margin-left: var(--sidebar-width);
            flex: 1;
            padding: 40px;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }
        
        .header h1 {
            font-size: 32px;
            font-weight: 600;
        }
        
        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            border: none;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--cyan), #00a88a);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 212, 170, 0.3);
        }
        
        .alert {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: rgba(0, 255, 136, 0.1);
            border: 1px solid #00ff88;
            color: #00ff88;
        }
        
        .alert-error {
            background: rgba(255, 107, 107, 0.1);
            border: 1px solid #ff6b6b;
            color: #ff6b6b;
        }
    </style>
</head>
<body>
    <?php include BASE_PATH . '/views/layouts/navbar.php'; ?>
    
    <div class="container">
        <aside class="sidebar">
            <div class="logo">
                <i class="fas fa-file-alt"></i>
                SheetDocs
            </div>
            
            <nav>
                <a href="/projects/sheetdocs/dashboard" class="nav-item">
                    <i class="fas fa-th-large"></i>
                    Dashboard
                </a>
                <a href="/projects/sheetdocs/documents/new" class="nav-item">
                    <i class="fas fa-file-alt"></i>
                    New Document
                </a>
                <a href="/projects/sheetdocs/sheets/new" class="nav-item">
                    <i class="fas fa-table"></i>
                    New Spreadsheet
                </a>
                <a href="/projects/sheetdocs/documents" class="nav-item">
                    <i class="fas fa-folder"></i>
                    My Documents
                </a>
                <a href="/projects/sheetdocs/sheets" class="nav-item">
                    <i class="fas fa-th"></i>
                    My Sheets
                </a>
                <a href="/projects/sheetdocs/templates" class="nav-item">
                    <i class="fas fa-clone"></i>
                    Templates
                </a>
                <a href="/projects/sheetdocs/pricing" class="nav-item">
                    <i class="fas fa-crown"></i>
                    Upgrade
                </a>
            </nav>
        </aside>
        
        <main class="main-content">
            <?php View::yield('content'); ?>
        </main>
    </div>
    
    <script>
        // Listen for theme changes from universal navbar
        document.addEventListener('themeChanged', function(e) {
            document.documentElement.setAttribute('data-theme', e.detail.theme);
        });
        
        // Highlight active nav item
        document.addEventListener('DOMContentLoaded', function() {
            const currentPath = window.location.pathname;
            document.querySelectorAll('.nav-item').forEach(item => {
                if (item.getAttribute('href') === currentPath) {
                    item.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>
