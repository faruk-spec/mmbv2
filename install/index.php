<?php
/**
 * Installer Entry Point
 * 
 * @package MMB\Install
 */

define('BASE_PATH', dirname(__DIR__));

// Check if already installed
if (file_exists(BASE_PATH . '/config/installed.lock')) {
    header('Location: /');
    exit;
}

session_start();

// Handle installation steps
$step = $_GET['step'] ?? 'requirements';
$error = null;
$success = null;

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($step) {
        case 'database':
            $host = trim($_POST['db_host'] ?? 'localhost');
            $port = trim($_POST['db_port'] ?? '3306');
            $name = trim($_POST['db_name'] ?? '');
            $user = trim($_POST['db_user'] ?? '');
            $pass = $_POST['db_pass'] ?? '';
            
            // Test connection
            try {
                $dsn = "mysql:host={$host};port={$port}";
                $pdo = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                ]);
                
                // Create database if not exists
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                $pdo->exec("USE `{$name}`");
                
                // Save to session for next step
                $_SESSION['install_db'] = [
                    'host' => $host,
                    'port' => $port,
                    'database' => $name,
                    'username' => $user,
                    'password' => $pass
                ];
                
                header('Location: ?step=schema');
                exit;
                
            } catch (PDOException $e) {
                $error = 'Database connection failed: ' . $e->getMessage();
            }
            break;
            
        case 'schema':
            if (!isset($_SESSION['install_db'])) {
                header('Location: ?step=database');
                exit;
            }
            
            try {
                $db = $_SESSION['install_db'];
                $dsn = "mysql:host={$db['host']};port={$db['port']};dbname={$db['database']}";
                $pdo = new PDO($dsn, $db['username'], $db['password'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                ]);
                
                // Run schema
                $schema = file_get_contents(BASE_PATH . '/install/schema.sql');
                $pdo->exec($schema);
                
                header('Location: ?step=admin');
                exit;
                
            } catch (PDOException $e) {
                $error = 'Schema creation failed: ' . $e->getMessage();
            }
            break;
            
        case 'admin':
            if (!isset($_SESSION['install_db'])) {
                header('Location: ?step=database');
                exit;
            }
            
            $name = trim($_POST['admin_name'] ?? '');
            $email = trim($_POST['admin_email'] ?? '');
            $password = $_POST['admin_password'] ?? '';
            
            if (empty($name) || empty($email) || empty($password)) {
                $error = 'All fields are required.';
                break;
            }
            
            if (strlen($password) < 8) {
                $error = 'Password must be at least 8 characters.';
                break;
            }
            
            try {
                $db = $_SESSION['install_db'];
                $dsn = "mysql:host={$db['host']};port={$db['port']};dbname={$db['database']}";
                $pdo = new PDO($dsn, $db['username'], $db['password'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                ]);
                
                // Create admin user
                $hashedPassword = password_hash($password, PASSWORD_ARGON2ID);
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, status, created_at) VALUES (?, ?, ?, 'super_admin', 'active', NOW())");
                $stmt->execute([$name, $email, $hashedPassword]);
                $userId = $pdo->lastInsertId();
                
                // Create profile
                $stmt = $pdo->prepare("INSERT INTO user_profiles (user_id, created_at) VALUES (?, NOW())");
                $stmt->execute([$userId]);
                
                header('Location: ?step=config');
                exit;
                
            } catch (PDOException $e) {
                $error = 'Admin creation failed: ' . $e->getMessage();
            }
            break;
            
        case 'config':
            if (!isset($_SESSION['install_db'])) {
                header('Location: ?step=database');
                exit;
            }
            
            $db = $_SESSION['install_db'];
            
            // Generate config files
            $dbConfig = "<?php\n\nreturn [\n    'host' => '{$db['host']}',\n    'port' => '{$db['port']}',\n    'database' => '{$db['database']}',\n    'username' => '{$db['username']}',\n    'password' => '{$db['password']}',\n    'charset' => 'utf8mb4',\n    'collation' => 'utf8mb4_unicode_ci',\n    'prefix' => '',\n];\n";
            
            file_put_contents(BASE_PATH . '/config/database.php', $dbConfig);
            
            // Generate app key
            $appKey = bin2hex(random_bytes(16));
            $ssoKey = bin2hex(random_bytes(16));
            
            $appConfig = file_get_contents(BASE_PATH . '/config/app.php');
            $appConfig = preg_replace("/define\('APP_KEY', '[^']*'\);/", "define('APP_KEY', '{$appKey}');", $appConfig);
            $appConfig = preg_replace("/define\('SSO_SECRET_KEY', '[^']*'\);/", "define('SSO_SECRET_KEY', '{$ssoKey}');", $appConfig);
            file_put_contents(BASE_PATH . '/config/app.php', $appConfig);
            
            // Create lock file
            file_put_contents(BASE_PATH . '/config/installed.lock', date('Y-m-d H:i:s'));
            
            // Clear session
            unset($_SESSION['install_db']);
            
            header('Location: ?step=complete');
            exit;
            break;
    }
}

// Check requirements
function checkRequirements(): array {
    $requirements = [];
    
    // PHP version
    $requirements['PHP Version (8.0+)'] = version_compare(PHP_VERSION, '8.0.0', '>=');
    
    // Extensions
    $extensions = ['pdo', 'pdo_mysql', 'json', 'mbstring', 'openssl', 'session'];
    foreach ($extensions as $ext) {
        $requirements["Extension: {$ext}"] = extension_loaded($ext);
    }
    
    // Password hashing
    $requirements['Argon2id Support'] = defined('PASSWORD_ARGON2ID');
    
    // Directories
    $dirs = [
        '/storage/logs' => BASE_PATH . '/storage/logs',
        '/storage/cache' => BASE_PATH . '/storage/cache',
        '/storage/uploads' => BASE_PATH . '/storage/uploads',
        '/config' => BASE_PATH . '/config'
    ];
    
    foreach ($dirs as $name => $path) {
        if (!is_dir($path)) {
            @mkdir($path, 0755, true);
        }
        $requirements["Writable: {$name}"] = is_writable($path);
    }
    
    return $requirements;
}

$requirements = checkRequirements();
$allPassed = !in_array(false, $requirements, true);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install - MyMultiBranch</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-primary: #06060a;
            --bg-secondary: #0c0c12;
            --bg-card: #0f0f18;
            --cyan: #00f0ff;
            --magenta: #ff2ec4;
            --green: #00ff88;
            --red: #ff6b6b;
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
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }
        
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
        
        .logo {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 30px;
            background: linear-gradient(135deg, var(--cyan), var(--magenta));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 40px;
            width: 100%;
            max-width: 500px;
        }
        
        .steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            position: relative;
        }
        
        .steps::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 0;
            right: 0;
            height: 2px;
            background: var(--border-color);
            z-index: 0;
        }
        
        .step {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: var(--bg-secondary);
            border: 2px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            z-index: 1;
        }
        
        .step.active {
            border-color: var(--cyan);
            color: var(--cyan);
        }
        
        .step.complete {
            background: var(--green);
            border-color: var(--green);
            color: var(--bg-primary);
        }
        
        h2 { margin-bottom: 20px; }
        
        .form-group { margin-bottom: 20px; }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
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
        }
        
        .form-input:focus {
            outline: none;
            border-color: var(--cyan);
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-family: inherit;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--cyan), var(--magenta));
            color: var(--bg-primary);
        }
        
        .btn-secondary {
            background: var(--bg-secondary);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-error {
            background: rgba(255, 107, 107, 0.1);
            border: 1px solid var(--red);
            color: var(--red);
        }
        
        .alert-success {
            background: rgba(0, 255, 136, 0.1);
            border: 1px solid var(--green);
            color: var(--green);
        }
        
        .requirement {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .requirement:last-child { border: none; }
        
        .status-pass { color: var(--green); }
        .status-fail { color: var(--red); }
    </style>
</head>
<body>
    <div class="logo">MyMultiBranch Installer</div>
    
    <div class="card">
        <div class="steps">
            <div class="step <?= $step === 'requirements' ? 'active' : ($step !== 'requirements' ? 'complete' : '') ?>">1</div>
            <div class="step <?= $step === 'database' ? 'active' : (in_array($step, ['schema', 'admin', 'config', 'complete']) ? 'complete' : '') ?>">2</div>
            <div class="step <?= $step === 'schema' ? 'active' : (in_array($step, ['admin', 'config', 'complete']) ? 'complete' : '') ?>">3</div>
            <div class="step <?= $step === 'admin' ? 'active' : (in_array($step, ['config', 'complete']) ? 'complete' : '') ?>">4</div>
            <div class="step <?= in_array($step, ['config', 'complete']) ? 'complete' : '' ?>">5</div>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($step === 'requirements'): ?>
            <h2>System Requirements</h2>
            
            <?php foreach ($requirements as $name => $passed): ?>
                <div class="requirement">
                    <span><?= $name ?></span>
                    <span class="<?= $passed ? 'status-pass' : 'status-fail' ?>">
                        <?= $passed ? '✓ Pass' : '✗ Fail' ?>
                    </span>
                </div>
            <?php endforeach; ?>
            
            <div style="margin-top: 30px; text-align: right;">
                <?php if ($allPassed): ?>
                    <a href="?step=database" class="btn btn-primary">Continue</a>
                <?php else: ?>
                    <span style="color: var(--red);">Please fix the failed requirements</span>
                <?php endif; ?>
            </div>
            
        <?php elseif ($step === 'database'): ?>
            <h2>Database Configuration</h2>
            
            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Database Host</label>
                    <input type="text" name="db_host" class="form-input" value="localhost" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Database Port</label>
                    <input type="text" name="db_port" class="form-input" value="3306" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Database Name</label>
                    <input type="text" name="db_name" class="form-input" value="mmb_main" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Database User</label>
                    <input type="text" name="db_user" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Database Password</label>
                    <input type="password" name="db_pass" class="form-input">
                </div>
                
                <div style="display: flex; justify-content: space-between; margin-top: 30px;">
                    <a href="?step=requirements" class="btn btn-secondary">Back</a>
                    <button type="submit" class="btn btn-primary">Test & Continue</button>
                </div>
            </form>
            
        <?php elseif ($step === 'schema'): ?>
            <h2>Create Database Schema</h2>
            <p style="color: var(--text-secondary); margin-bottom: 20px;">
                Click below to create the required database tables.
            </p>
            
            <form method="POST">
                <div style="display: flex; justify-content: space-between; margin-top: 30px;">
                    <a href="?step=database" class="btn btn-secondary">Back</a>
                    <button type="submit" class="btn btn-primary">Create Schema</button>
                </div>
            </form>
            
        <?php elseif ($step === 'admin'): ?>
            <h2>Create Admin Account</h2>
            
            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="admin_name" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="admin_email" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="admin_password" class="form-input" minlength="8" required>
                </div>
                
                <div style="display: flex; justify-content: space-between; margin-top: 30px;">
                    <a href="?step=schema" class="btn btn-secondary">Back</a>
                    <button type="submit" class="btn btn-primary">Create Admin</button>
                </div>
            </form>
            
        <?php elseif ($step === 'config'): ?>
            <h2>Generate Configuration</h2>
            <p style="color: var(--text-secondary); margin-bottom: 20px;">
                Click below to generate configuration files and complete the installation.
            </p>
            
            <form method="POST">
                <div style="text-align: right; margin-top: 30px;">
                    <button type="submit" class="btn btn-primary">Complete Installation</button>
                </div>
            </form>
            
        <?php elseif ($step === 'complete'): ?>
            <div style="text-align: center;">
                <div style="width: 80px; height: 80px; background: rgba(0, 255, 136, 0.1); border-radius: 50%; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center;">
                    <span style="font-size: 40px; color: var(--green);">✓</span>
                </div>
                
                <h2>Installation Complete!</h2>
                <p style="color: var(--text-secondary); margin-bottom: 30px;">
                    MyMultiBranch has been successfully installed.
                </p>
                
                <a href="/" class="btn btn-primary">Go to Website</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
