<?php
/**
 * DevZone Project Configuration
 * 
 * @package MMB\Projects\DevZone
 */

return [
    'name' => 'DevZone',
    'version' => '1.0.0',
    'description' => 'Developer collaboration and project management',
    
    'database' => [
        'host' => 'localhost',
        'port' => '3306',
        'database' => 'mmb_devzone',
        'username' => 'root',
        'password' => '',
    ],
    
    'features' => [
        'project_boards' => true,
        'team_chat' => true,
        'code_review' => true,
        'ci_cd' => true,
    ]
];
