<?php
/**
 * Permissions Configuration
 * 
 * @package MMB\Config
 */

return [
    // Role hierarchy
    'roles' => [
        'super_admin' => [
            'label' => 'Super Administrator',
            'level' => 100,
            'permissions' => ['*'] // All permissions
        ],
        'admin' => [
            'label' => 'Administrator',
            'level' => 80,
            'permissions' => [
                'admin.access',
                'admin.users.*',
                'admin.projects.*',
                'admin.settings.view',
                'admin.logs.view'
            ]
        ],
        'project_admin' => [
            'label' => 'Project Administrator',
            'level' => 60,
            'permissions' => [
                'admin.access',
                'admin.projects.view',
                'admin.projects.edit',
                'project.*'
            ]
        ],
        'user' => [
            'label' => 'User',
            'level' => 10,
            'permissions' => [
                'dashboard.access',
                'profile.view',
                'profile.edit',
                'projects.access'
            ]
        ]
    ],
    
    // Permission definitions
    'permissions' => [
        // Admin permissions
        'admin.access' => 'Access admin panel',
        'admin.users.view' => 'View users',
        'admin.users.create' => 'Create users',
        'admin.users.edit' => 'Edit users',
        'admin.users.delete' => 'Delete users',
        'admin.projects.view' => 'View projects',
        'admin.projects.create' => 'Create projects',
        'admin.projects.edit' => 'Edit projects',
        'admin.projects.delete' => 'Delete projects',
        'admin.settings.view' => 'View settings',
        'admin.settings.edit' => 'Edit settings',
        'admin.logs.view' => 'View logs',
        'admin.security.manage' => 'Manage security',
        
        // User permissions
        'dashboard.access' => 'Access dashboard',
        'profile.view' => 'View own profile',
        'profile.edit' => 'Edit own profile',
        'projects.access' => 'Access projects',
        
        // Project permissions
        'project.view' => 'View project',
        'project.edit' => 'Edit project',
        'project.manage' => 'Manage project'
    ]
];
