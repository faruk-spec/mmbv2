<?php

return [
    // 'passive' = log failed/infected scans but still allow the upload
    // 'enforce' = block the upload entirely when scan fails or detects malware
    // Recommended: start with 'passive' and switch to 'enforce' once ClamAV is confirmed working.
    'mode' => getenv('UPLOAD_SCAN_MODE') ?: 'passive',

    'clamav' => [
        // Set CLAMAV_ENABLED=1 in your environment (or enable via Admin → Security settings)
        // once you have ClamAV/clamdscan installed and confirmed working.
        // Leaving this disabled still enforces MIME-type, magic-byte, and extension validation.
        'enabled' => getenv('CLAMAV_ENABLED') === '1',
        // clamscan (standalone) reloads its database on every call — SLOW on large DBs.
        // Switch to 'clamdscan --no-summary --stdout' when running the clamd daemon for
        // near-instant scans (strongly recommended for production).
        'command' => getenv('CLAMAV_SCAN_COMMAND') ?: 'clamscan --no-summary --stdout',
    ],

    'max_file_size' => 500 * 1024 * 1024, // 500 MB

    'blocked_extensions' => [
        'php', 'php3', 'php4', 'php5', 'phtml', 'phar',
        'js', 'mjs', 'cjs', 'html', 'htm', 'shtml',
        'exe', 'dll', 'bat', 'cmd', 'com', 'msi', 'sh', 'bash', 'zsh',
        'ps1', 'vbs', 'jar', 'jsp', 'asp', 'aspx', 'cgi', 'pl', 'py',
    ],

    'blocked_mime_types' => [
        'application/x-httpd-php',
        'application/x-php',
        'text/x-php',
        'application/x-sh',
        'application/x-msdownload',
        'application/x-dosexec',
        'text/javascript',
        'application/javascript',
        'application/x-javascript',
        'text/html',
    ],
];
