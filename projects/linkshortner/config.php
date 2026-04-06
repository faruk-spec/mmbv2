<?php
/**
 * LinkShortner Project Configuration
 *
 * @package MMB\Projects\LinkShortner
 */

$mainConfig = require dirname(dirname(__DIR__)) . '/config/database.php';

return [
    'name'    => 'LinkShortner',
    'version' => '1.0.0',
    'description' => 'URL shortener with click analytics, QR codes, and branded links',

    'database' => [
        'host'     => $mainConfig['host']     ?? 'localhost',
        'port'     => $mainConfig['port']     ?? '3306',
        'database' => $mainConfig['database'] ?? 'testuser',
        'username' => $mainConfig['username'] ?? 'testuser',
        'password' => $mainConfig['password'] ?? 'testuser',
    ],

    'features' => [
        'password_protection' => true,
        'link_expiry'         => true,
        'click_limit'         => true,
        'qr_code'             => true,
        'analytics'           => true,
    ],
];
