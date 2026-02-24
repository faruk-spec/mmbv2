<?php
/**
 * ConvertX Project Configuration
 *
 * AI-powered document conversion and automation platform.
 *
 * @package MMB\Projects\ConvertX
 */

return [
    'name'        => 'ConvertX',
    'version'     => '1.0.0',
    'description' => 'AI-powered document conversion and automation platform',

    'database' => [
        'host'     => 'localhost',
        'port'     => '3306',
        'database' => 'mmb_convertx',
        'username' => 'root',
        'password' => '',
    ],

    // Supported input/output formats grouped by category
    'formats' => [
        'document' => ['pdf', 'docx', 'doc', 'odt', 'rtf', 'txt', 'html', 'md'],
        'spreadsheet' => ['xlsx', 'xls', 'ods', 'csv'],
        'presentation' => ['pptx', 'ppt', 'odp'],
        'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'tiff', 'svg'],
    ],

    // Maximum file size per plan tier (bytes)
    'upload_limits' => [
        'free'       => 10 * 1024 * 1024,   // 10 MB
        'pro'        => 100 * 1024 * 1024,  // 100 MB
        'enterprise' => 500 * 1024 * 1024,  // 500 MB
    ],

    // Async job settings
    'queue' => [
        'max_retries'      => 3,
        'retry_delay_secs' => 30,
        'job_ttl_secs'     => 3600,
    ],

    // AI capabilities
    'ai' => [
        'ocr_enabled'            => true,
        'summarization_enabled'  => true,
        'translation_enabled'    => true,
        'classification_enabled' => true,
    ],

    // File auto-deletion policy (seconds after conversion completes)
    'file_ttl' => [
        'free'       => 3600,      // 1 hour
        'pro'        => 86400,     // 24 hours
        'enterprise' => 604800,    // 7 days
    ],

    'features' => [
        'batch_conversion'    => true,
        'webhook_callbacks'   => true,
        'api_access'          => true,
        'ai_ocr'              => true,
        'ai_summarization'    => true,
        'ai_translation'      => true,
        'virus_scan'          => false, // enable when ClamAV is available
        'job_chaining'        => true,
    ],
];
