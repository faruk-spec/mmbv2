<?php
/**
 * ProShare Project Configuration
 * 
 * @package MMB\Projects\ProShare
 */

// Load main database configuration
$mainConfig = require dirname(dirname(__DIR__)) . '/config/database.php';

return [
    'name' => 'ProShare',
    'version' => '1.0.0',
    'description' => 'Secure file sharing platform',
    
    'database' => [
        'host' => $mainConfig['host'] ?? 'localhost',
        'port' => $mainConfig['port'] ?? '3306',
        'database' => 'proshare',
        'username' => $mainConfig['username'] ?? 'testuser',
        'password' => $mainConfig['password'] ?? 'testuser',
    ],
    
    'features' => [
        'max_file_size' => '100MB',
        'encryption' => true,
        'expiry_links' => true,
        'password_protection' => true,
    ]
];
