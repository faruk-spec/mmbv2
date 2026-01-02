<?php
/**
 * QR Generator Project Configuration
 * 
 * @package MMB\Projects\QR
 */

return [
    'name' => 'QR Generator',
    'version' => '1.0.0',
    'description' => 'QR code generation and management',
    
    'database' => [
        'host' => 'localhost',
        'port' => '3306',
        'database' => 'mmb_qr',
        'username' => 'root',
        'password' => '',
    ],
    
    'features' => [
        'dynamic_qr' => true,
        'analytics' => true,
        'custom_design' => true,
        'bulk_generation' => true,
    ]
];
