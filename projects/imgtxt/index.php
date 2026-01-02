<?php
/**
 * ImgTxt Project - Entry Point
 * 
 * @package MMB\Projects\ImgTxt
 */

// Define project path
define('PROJECT_PATH', __DIR__);
define('BASE_PATH', dirname(dirname(__DIR__)));

// Load core autoloader
require_once BASE_PATH . '/core/Autoloader.php';

// Load main app config
require_once BASE_PATH . '/config/app.php';

// Validate SSO access
use Core\SSO;

// Check if user has access to this project
if (!SSO::validateProjectRequest('imgtxt')) {
    SSO::redirectToLogin($_SERVER['REQUEST_URI']);
}

// Load project config
$projectConfig = require PROJECT_PATH . '/config.php';

// Load project routes
require_once PROJECT_PATH . '/routes/web.php';
