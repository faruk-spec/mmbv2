<?php
/**
 * Application Configuration
 * 
 * @package MMB\Config
 */

// Application settings
define('APP_NAME', 'MyMultiBranch');
define('APP_VERSION', '1.0.0');

// Dynamic APP_URL detection - use environment variable or auto-detect from request
if (getenv('APP_URL')) {
    define('APP_URL', rtrim(getenv('APP_URL'), '/'));
} else {
    // Auto-detect from current request
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
                (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
                (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    define('APP_URL', $protocol . '://' . $host);
}

define('APP_DEBUG', true);

// Security keys - IMPORTANT: These are default values for development only!
// In production, these MUST be replaced with randomly generated unique keys.
// Keys will be automatically generated during installation.
// You can generate keys using: bin2hex(random_bytes(16))
define('APP_KEY', getenv('APP_KEY') ?: 'change_this_in_production_32_chars');
define('SSO_SECRET_KEY', getenv('SSO_SECRET_KEY') ?: 'sso_secret_key_change_in_production');

// Session settings
define('SESSION_LIFETIME', 120); // minutes
define('SESSION_NAME', 'mmb_session');

// Email verification requirement
define('REQUIRE_EMAIL_VERIFICATION', false);

// Timezone
date_default_timezone_set('UTC');

// Error reporting
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}
