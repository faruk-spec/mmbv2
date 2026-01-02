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
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
        }
        
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(ellipse at 20% 0%, rgba(153, 69, 255, 0.15) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 100%, rgba(0, 240, 255, 0.1) 0%, transparent 50%);
            pointer-events: none;
            z-index: -1;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px;
        }
        
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 24px;
        }
        
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
            transition: all 0.3s;
            text-decoration: none;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--purple), var(--magenta));
            color: var(--bg-primary);
        }
        
        .btn-primary:hover {
            box-shadow: 0 0 20px rgba(153, 69, 255, 0.4);
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: var(--bg-secondary);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-secondary);
            font-weight: 500;
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
        
        @media (max-width: 768px) {
            .grid-2, .grid-3 { grid-template-columns: 1fr; }
            .nav { display: none; }
        }
        
        .stat-card {
            text-align: center;
            padding: 30px;
        }
        
        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--purple);
        }
        
        .stat-label {
            color: var(--text-secondary);
            margin-top: 5px;
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
        }
        
        .back-link:hover {
            color: var(--purple);
        }
    </style>
</head>
<body>
    <?php include BASE_PATH . '/views/layouts/navbar.php'; ?>
    
    <div class="container">
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
    </div>
</body>
</html>
