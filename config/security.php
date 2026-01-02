<?php
/**
 * Security Configuration
 * 
 * @package MMB\Config
 */

return [
    // Password hashing
    'password' => [
        'algorithm' => PASSWORD_ARGON2ID,
        'options' => [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 3
        ]
    ],
    
    // Rate limiting
    'rate_limiting' => [
        'login' => [
            'max_attempts' => 5,
            'decay_minutes' => 15
        ],
        'api' => [
            'max_attempts' => 60,
            'decay_minutes' => 1
        ]
    ],
    
    // Session security
    'session' => [
        'lifetime' => 120, // minutes
        'expire_on_close' => false,
        'encrypt' => false,
        'same_site' => 'Lax'
    ],
    
    // Cookie settings
    'cookies' => [
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Lax'
    ],
    
    // 2FA settings
    'two_factor' => [
        'enabled' => true,
        'issuer' => 'MyMultiBranch',
        'digits' => 6,
        'period' => 30
    ],
    
    // IP blocking
    'ip_blocking' => [
        'enabled' => true,
        'whitelist' => [
            '127.0.0.1',
            '::1'
        ],
        'blacklist' => []
    ],
    
    // CORS settings
    'cors' => [
        'allowed_origins' => ['*'],
        'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE'],
        'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With', 'X-CSRF-Token'],
        'max_age' => 86400
    ]
];
