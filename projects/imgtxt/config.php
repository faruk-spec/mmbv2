<?php
/**
 * ImgTxt Project Configuration
 * 
 * @package MMB\Projects\ImgTxt
 */

// Load main database configuration
$mainConfig = require dirname(dirname(__DIR__)) . '/config/database.php';

return [
    'name' => 'ImgTxt',
    'version' => '1.0.0',
    'description' => 'Image to text converter and OCR tool',
    
    'database' => [
        'host' => $mainConfig['host'] ?? 'localhost',
        'port' => $mainConfig['port'] ?? '3306',
        'database' => 'imgtxt',
        'username' => $mainConfig['username'] ?? 'testuser',
        'password' => $mainConfig['password'] ?? 'testuser',
    ],
    
    'features' => [
        'ocr_engine' => 'tesseract',
        'supported_formats' => ['jpg', 'png', 'gif', 'pdf'],
        'batch_processing' => true,
    ]
];
