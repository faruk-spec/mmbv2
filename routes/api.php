<?php
/**
 * API Routes
 * 
 * @package MMB\Routes
 */

use Core\Auth;
use Core\Helpers;

// API health check
$router->get('/api/health', function() {
    Helpers::json(['status' => 'ok', 'version' => APP_VERSION]);
});

// SSO endpoints
$router->get('/api/sso/validate', 'Api\\SsoController@validate');
$router->post('/api/sso/token', 'Api\\SsoController@generateToken');
$router->post('/api/sso/refresh', 'Api\\SsoController@refreshToken');

// User API
$router->get('/api/user', 'Api\\UserController@current', ['auth']);
$router->get('/api/user/profile', 'Api\\UserController@profile', ['auth']);
$router->put('/api/user/profile', 'Api\\UserController@updateProfile', ['auth']);

// Projects API
$router->get('/api/projects', 'Api\\ProjectController@list', ['auth']);
$router->get('/api/projects/{name}', 'Api\\ProjectController@show', ['auth']);

// Admin API
$router->get('/api/admin/stats', 'Api\\AdminController@stats', ['auth', 'admin']);
$router->get('/api/admin/users', 'Api\\AdminController@users', ['auth', 'admin']);
$router->get('/api/admin/activity', 'Api\\AdminController@activity', ['auth', 'admin']);
