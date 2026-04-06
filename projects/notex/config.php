<?php
/**
 * NoteX Project Configuration
 *
 * @package MMB\Projects\NoteX
 */

$mainConfig = require dirname(dirname(__DIR__)) . '/config/database.php';

return [
    'name'    => 'NoteX',
    'version' => '1.0.0',
    'description' => 'Private cloud notes with rich text, folders, tags and secure sharing',

    'database' => [
        'host'     => $mainConfig['host']     ?? 'localhost',
        'port'     => $mainConfig['port']     ?? '3306',
        'database' => $mainConfig['database'] ?? 'testuser',
        'username' => $mainConfig['username'] ?? 'testuser',
        'password' => $mainConfig['password'] ?? 'testuser',
    ],

    'features' => [
        'rich_text'      => true,
        'folders'        => true,
        'tags'           => true,
        'sharing'        => true,
        'version_history'=> true,
        'pin_notes'      => true,
    ],
];
