<?php use Core\View; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= View::e($title ?? 'Subscriber Dashboard') ?> - Mail Server</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">
    
    <style>
        :root {
            --primary-color: #00f0ff;
            --secondary-color: #ff2ec4;
            --success-color: #00ff88;
            --warning-color: #ffaa00;
            --danger-color: #ff6b6b;
            --bg-dark: #06060a;
            --bg-card: #0f0f18;
            --text-primary: #e8eefc;
            --text-secondary: #8892a6;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg-dark);
            color: var(--text-primary);
        }
        
        .content-wrapper {
            background: var(--bg-dark);
        }
        
        .card {
            background: var(--bg-card);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
        }
        
        .card-header {
            background: rgba(0, 240, 255, 0.05);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-primary);
        }
        
        .small-box {
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0, 240, 255, 0.2);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 240, 255, 0.4);
        }
    </style>
</head>
<body class="hold-transition sidebar-mini">
    <div class="wrapper">
        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-dark">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="/projects/mail/subscriber/dashboard">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/projects/mail/subscriber/users">
                        <i class="fas fa-users"></i> Users
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/projects/mail/subscriber/domains">
                        <i class="fas fa-globe"></i> Domains
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/projects/mail/subscriber/billing">
                        <i class="fas fa-credit-card"></i> Billing
                    </a>
                </li>
            </ul>
            
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="/logout">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
        </nav>
        
        <!-- Content -->
        <div class="content-wrapper">
            <section class="content pt-3">
                <?= $content ?? '' ?>
            </section>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>
</body>
</html>
