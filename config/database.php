<?php
/**
 * Database Configuration
 * 
 * @package MMB\Config
 */

return [
    'host'      => getenv('DB_HOST')     ?: 'localhost',
    'port'      => getenv('DB_PORT')     ?: '3306',
    'database'  => getenv('DB_DATABASE') ?: 'testuser',
    'username'  => getenv('DB_USERNAME') ?: 'testuser',
    'password'  => getenv('DB_PASSWORD') ?: '',
    'charset'   => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix'    => '',
];
