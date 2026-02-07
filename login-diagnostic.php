<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Diagnostic</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        h1 { color: #333; }
        .section {
            background: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .ok { color: #28a745; }
        .error { color: #dc3545; }
        .warning { color: #ffc107; }
        pre {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            text-align: left;
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        th { background: #f8f9fa; }
        .test-form {
            background: #e3f2fd;
            padding: 20px;
            border-radius: 8px;
        }
        .test-form input {
            padding: 10px;
            margin: 5px 0;
            width: 100%;
            max-width: 400px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .test-form button {
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .test-form button:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <h1>🔍 Login Diagnostic Tool</h1>
    
    <?php
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Define BASE_PATH if not defined
    if (!defined('BASE_PATH')) {
        define('BASE_PATH', __DIR__);
    }
    
    // Load essential classes
    require_once BASE_PATH . '/core/Autoloader.php';
    
    ?>
    
    <!-- Session Information -->
    <div class="section">
        <h2>📋 Session Status</h2>
        <?php
        echo "<table>";
        echo "<tr><th>Parameter</th><th>Value</th><th>Status</th></tr>";
        
        $sessionStatus = session_status();
        $statusText = [
            PHP_SESSION_DISABLED => 'DISABLED',
            PHP_SESSION_NONE => 'NONE (not started)',
            PHP_SESSION_ACTIVE => 'ACTIVE'
        ];
        $statusClass = $sessionStatus === PHP_SESSION_ACTIVE ? 'ok' : 'error';
        echo "<tr><td>Session Status</td><td class='$statusClass'>{$statusText[$sessionStatus]}</td><td class='$statusClass'>". ($sessionStatus === PHP_SESSION_ACTIVE ? '✓ OK' : '✗ ERROR') ."</td></tr>";
        
        if ($sessionStatus === PHP_SESSION_ACTIVE) {
            echo "<tr><td>Session ID</td><td>" . session_id() . "</td><td class='ok'>✓ OK</td></tr>";
            echo "<tr><td>Session Name</td><td>" . session_name() . "</td><td class='ok'>✓ OK</td></tr>";
            echo "<tr><td>CSRF Token</td><td>" . (isset($_SESSION['csrf_token']) ? 'Present' : 'Missing') . "</td><td class='" . (isset($_SESSION['csrf_token']) ? 'ok' : 'warning') . "'>" . (isset($_SESSION['csrf_token']) ? '✓ OK' : '⚠ Missing') . "</td></tr>";
            echo "<tr><td>User Logged In</td><td>" . (isset($_SESSION['user_id']) ? 'Yes (ID: ' . $_SESSION['user_id'] . ')' : 'No') . "</td><td class='" . (isset($_SESSION['user_id']) ? 'ok' : 'warning') . "'>" . (isset($_SESSION['user_id']) ? '✓ Logged In' : 'ℹ Not Logged In') . "</td></tr>";
        }
        echo "</table>";
        ?>
    </div>
    
    <!-- Cookie Information -->
    <div class="section">
        <h2>🍪 Cookie Status</h2>
        <?php
        echo "<table>";
        echo "<tr><th>Parameter</th><th>Value</th><th>Status</th></tr>";
        
        $cookies = [
            'session_cookie_httponly' => ini_get('session.cookie_httponly'),
            'session_cookie_secure' => ini_get('session.cookie_secure'),
            'session_cookie_samesite' => ini_get('session.cookie_samesite'),
            'session_cookie_path' => ini_get('session.cookie_path'),
        ];
        
        foreach ($cookies as $key => $value) {
            $displayValue = $value === '' ? '(empty)' : $value;
            echo "<tr><td>$key</td><td>$displayValue</td><td class='ok'>ℹ Info</td></tr>";
        }
        
        echo "<tr><td colspan='3'><strong>Active Cookies:</strong></td></tr>";
        if (!empty($_COOKIE)) {
            foreach ($_COOKIE as $name => $value) {
                $displayValue = strlen($value) > 50 ? substr($value, 0, 50) . '...' : $value;
                echo "<tr><td>$name</td><td>$displayValue</td><td class='ok'>✓ Present</td></tr>";
            }
        } else {
            echo "<tr><td colspan='3' class='warning'>No cookies found</td></tr>";
        }
        echo "</table>";
        ?>
    </div>
    
    <!-- Database Connection -->
    <div class="section">
        <h2>🗄️ Database Connection</h2>
        <?php
        try {
            if (file_exists(BASE_PATH . '/config/database.php')) {
                require_once BASE_PATH . '/config/app.php';
                $db = Core\Database::getInstance();
                echo "<p class='ok'>✓ Database connection successful</p>";
                
                // Test users table
                $userCount = $db->fetch("SELECT COUNT(*) as count FROM users");
                echo "<p>Total users: <strong>" . $userCount['count'] . "</strong></p>";
            } else {
                echo "<p class='error'>✗ Database config file not found</p>";
            }
        } catch (Exception $e) {
            echo "<p class='error'>✗ Database connection failed: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
        ?>
    </div>
    
    <!-- CSRF Test -->
    <div class="section">
        <h2>🔐 CSRF Token Test</h2>
        <?php
        if (class_exists('Core\Security')) {
            $csrfToken = Core\Security::generateCsrfToken();
            echo "<p>Generated CSRF Token: <code>" . htmlspecialchars($csrfToken) . "</code></p>";
            echo "<p class='ok'>✓ CSRF token generation working</p>";
            
            // Verify the token immediately
            $verified = Core\Security::verifyCsrfToken($csrfToken);
            echo "<p>Token verification: <span class='" . ($verified ? 'ok' : 'error') . "'>" . ($verified ? '✓ PASS' : '✗ FAIL') . "</span></p>";
        } else {
            echo "<p class='error'>✗ Security class not loaded</p>";
        }
        ?>
    </div>
    
    <!-- Server Information -->
    <div class="section">
        <h2>⚙️ Server Configuration</h2>
        <?php
        echo "<table>";
        echo "<tr><th>Parameter</th><th>Value</th></tr>";
        echo "<tr><td>PHP Version</td><td>" . phpversion() . "</td></tr>";
        echo "<tr><td>Server Software</td><td>" . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "</td></tr>";
        echo "<tr><td>Document Root</td><td>" . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . "</td></tr>";
        echo "<tr><td>Script Filename</td><td>" . ($_SERVER['SCRIPT_FILENAME'] ?? 'Unknown') . "</td></tr>";
        echo "<tr><td>Request Method</td><td>" . ($_SERVER['REQUEST_METHOD'] ?? 'Unknown') . "</td></tr>";
        echo "<tr><td>Request URI</td><td>" . ($_SERVER['REQUEST_URI'] ?? 'Unknown') . "</td></tr>";
        echo "<tr><td>HTTPS</td><td>" . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'Yes' : 'No') . "</td></tr>";
        echo "<tr><td>HTTP Host</td><td>" . ($_SERVER['HTTP_HOST'] ?? 'Unknown') . "</td></tr>";
        echo "</table>";
    </div>
    
    <!-- Extensions Check -->
    <div class="section">
        <h2>📦 PHP Extensions</h2>
        <?php
        $requiredExtensions = ['pdo', 'pdo_mysql', 'mbstring', 'json', 'openssl', 'session', 'gd'];
        echo "<table>";
        echo "<tr><th>Extension</th><th>Status</th></tr>";
        foreach ($requiredExtensions as $ext) {
            $loaded = extension_loaded($ext);
            $class = $loaded ? 'ok' : 'error';
            $status = $loaded ? '✓ Loaded' : '✗ Not Loaded';
            echo "<tr><td>$ext</td><td class='$class'>$status</td></tr>";
        }
        echo "</table>";
        ?>
    </div>
    
    <!-- Test Login Form -->
    <div class="section">
        <h2>🧪 Test Login Form</h2>
        <div class="test-form">
            <p><strong>Test the login process:</strong></p>
            <form method="POST" action="/login" onsubmit="return confirmTest();">
                <?php
                if (class_exists('Core\Security')) {
                    echo Core\Security::csrfField();
                }
                ?>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <label>
                    <input type="checkbox" name="remember"> Remember me
                </label>
                <br><br>
                <button type="submit">Test Login</button>
            </form>
            <p style="margin-top: 15px; color: #666; font-size: 14px;">
                ℹ️ This form will attempt to log you in using the credentials you provide.
            </p>
        </div>
    </div>
    
    <!-- Debug Session Data -->
    <div class="section">
        <h2>🔍 Debug Session Data</h2>
        <pre><?php print_r($_SESSION); ?></pre>
    </div>
    
    <!-- Debug POST Data -->
    <?php if (!empty($_POST)): ?>
    <div class="section">
        <h2>📨 Last POST Data</h2>
        <pre><?php print_r($_POST); ?></pre>
    </div>
    <?php endif; ?>
    
    <!-- Routes Check -->
    <div class="section">
        <h2>🛣️ Quick Route Tests</h2>
        <p>Test these routes to verify routing is working:</p>
        <ul>
            <li><a href="/" target="_blank">Home (/)</a></li>
            <li><a href="/login" target="_blank">Login (/login)</a></li>
            <li><a href="/register" target="_blank">Register (/register)</a></li>
            <li><a href="/dashboard" target="_blank">Dashboard (/dashboard)</a></li>
        </ul>
    </div>
    
    <script>
    function confirmTest() {
        return confirm('This will submit a real login request. Do you want to continue?');
    }
    </script>
</body>
</html>
