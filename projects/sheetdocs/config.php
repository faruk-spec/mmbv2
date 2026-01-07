<?php
/**
 * SheetDocs Project Configuration
 * 
 * @package MMB\Projects\SheetDocs
 */

// Load main database configuration
$mainConfig = require dirname(dirname(__DIR__)) . '/config/database.php';

return [
    'name' => 'SheetDocs',
    'version' => '1.0.0',
    'description' => 'Collaborative spreadsheet and document editor like Google Sheets & Docs',
    
    'database' => [
        'host' => $mainConfig['host'] ?? 'localhost',
        'port' => $mainConfig['port'] ?? '3306',
        'database' => 'sheetdocs',
        'username' => $mainConfig['username'] ?? 'testuser',
        'password' => $mainConfig['password'] ?? 'testuser',
    ],
    
    'features' => [
        'free' => [
            'max_documents' => 5,
            'max_sheets' => 5,
            'max_collaborators' => 2,
            'storage_limit' => 10 * 1024 * 1024, // 10MB
            'version_history' => false,
            'export_formats' => ['pdf'],
            'advanced_formulas' => false,
            'templates' => 'basic',
        ],
        'paid' => [
            'max_documents' => -1, // unlimited
            'max_sheets' => -1, // unlimited
            'max_collaborators' => -1, // unlimited
            'storage_limit' => 1024 * 1024 * 1024, // 1GB
            'version_history' => true,
            'export_formats' => ['pdf', 'docx', 'xlsx', 'csv'],
            'advanced_formulas' => true,
            'templates' => 'all',
            'priority_support' => true,
            'api_access' => true,
        ]
    ],
    
    'subscription' => [
        'monthly_price' => 9.99,
        'annual_price' => 99.99,
        'trial_days' => 14,
    ]
];
