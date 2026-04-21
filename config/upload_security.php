<?php

return [
    'mode' => getenv('UPLOAD_SCAN_MODE') ?: 'enforce', // passive|enforce

    'clamav' => [
        'enabled' => getenv('CLAMAV_ENABLED') !== '0',
        // clamscan (standalone) works without the ClamAV daemon.
        // Set CLAMAV_SCAN_COMMAND=clamdscan --no-summary --stdout to use the faster daemon instead.
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
