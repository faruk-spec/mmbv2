<?php
/**
 * WhatsApp API Automation - Entry Point
 * 
 * @package MMB\Projects\WhatsApp
 */

// Load core application
require_once __DIR__ . '/../../core/Autoloader.php';
require_once __DIR__ . '/../../config/app.php';

use Core\Auth;
use Core\Database;

// Check authentication
$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header('Location: /login?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

// Get current user
$user = $auth->getUser();

// Load project configuration
$projectConfig = require __DIR__ . '/config.php';

// Initialize project database connection
$db = new Database();
$db->connect($projectConfig['database']);

// Load routes
require_once __DIR__ . '/routes/web.php';
