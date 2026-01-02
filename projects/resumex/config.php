<?php
/**
 * ResumeX Project Configuration
 * 
 * @package MMB\Projects\ResumeX
 */

return [
    'name' => 'ResumeX',
    'version' => '1.0.0',
    'description' => 'Professional resume builder',
    
    'database' => [
        'host' => 'localhost',
        'port' => '3306',
        'database' => 'mmb_resumex',
        'username' => 'root',
        'password' => '',
    ],
    
    'features' => [
        'templates' => 20,
        'pdf_export' => true,
        'linkedin_import' => true,
        'ai_suggestions' => true,
    ]
];
