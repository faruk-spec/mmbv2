<?php
/**
 * Mail Configuration
 * 
 * @package MMB\Config
 */

return [
    'driver' => 'smtp', // smtp, sendmail, mail
    
    'smtp' => [
        'host' => 'smtp.example.com',
        'port' => 587,
        'username' => '',
        'password' => '',
        'encryption' => 'tls', // tls, ssl, null
    ],
    
    'from' => [
        'address' => 'noreply@example.com',
        'name' => 'MyMultiBranch'
    ],
    
    // Email templates
    'templates' => [
        'verify_email' => 'emails/verify',
        'password_reset' => 'emails/password-reset',
        'welcome' => 'emails/welcome'
    ]
];
