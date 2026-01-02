<?php
/**
 * CodeXPro Project Configuration
 * 
 * @package MMB\Projects\CodeXPro
 */

// Load main database configuration
$mainConfig = require dirname(dirname(__DIR__)) . '/config/database.php';

return [
    'name' => 'CodeXPro',
    'version' => '1.0.0',
    'description' => 'Advanced code editor and IDE platform',
    
    'database' => [
        'host' => $mainConfig['host'] ?? 'localhost',
        'port' => $mainConfig['port'] ?? '3306',
        'database' => 'codexpro',
        'username' => $mainConfig['username'] ?? 'testuser',
        'password' => $mainConfig['password'] ?? 'testuser',
    ],
    
    'features' => [
        'syntax_highlighting' => true,
        'code_completion' => true,
        'live_preview' => true,
        'git_integration' => true,
    ]
];
