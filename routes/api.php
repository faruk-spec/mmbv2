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

// Session token — dedicated endpoint for CSRF token and identity metadata.
// Browsers fetch this once on page-load; tokens appear in this response instead
// of being embedded in every HTML page's <meta> tags.
$router->get('/api/auth/token', 'Api\\UserController@sessionToken', ['auth']);

// Projects API
$router->get('/api/projects', 'Api\\ProjectController@list', ['auth']);
$router->get('/api/projects/{name}', 'Api\\ProjectController@show', ['auth']);

// Admin API
$router->get('/api/admin/stats', 'Api\\AdminController@stats', ['auth', 'admin']);
$router->get('/api/admin/users', 'Api\\AdminController@users', ['auth', 'admin']);
$router->get('/api/admin/activity', 'Api\\AdminController@activity', ['auth', 'admin']);

// QR Code API
// Supports Bearer token (api_keys table) and session authentication.
// The api_access feature flag must be enabled on the user's plan.
$router->get('/api/qr', 'Api\\QRController@list');
$router->post('/api/qr', 'Api\\QRController@create');
$router->get('/api/qr/{code}', 'Api\\QRController@show');
$router->delete('/api/qr/{code}', 'Api\\QRController@delete');

// ── Dynamic Support Ticket API ────────────────────────────────────────────────
// Public (auth required) — used by the React ticket-creation wizard
$router->get('/api/support/groups',     'Api\\SupportApiController@getGroups',    ['auth']);
$router->get('/api/support/categories', 'Api\\SupportApiController@getCategories', ['auth']);
$router->get('/api/support/template',   'Api\\SupportApiController@getTemplate',   ['auth']);
$router->post('/api/support/tickets',   'Api\\SupportApiController@submitTicket',  ['auth']);

// Admin — template group management
$router->get('/api/admin/support/groups',      'Api\\SupportApiController@adminGetGroups',    ['auth', 'admin']);
$router->post('/api/admin/support/groups',     'Api\\SupportApiController@adminCreateGroup',  ['auth', 'admin']);
$router->put('/api/admin/support/groups/{id}', 'Api\\SupportApiController@adminUpdateGroup',  ['auth', 'admin']);
$router->delete('/api/admin/support/groups/{id}', 'Api\\SupportApiController@adminDeleteGroup', ['auth', 'admin']);

// Admin — category management
$router->get('/api/admin/support/categories',        'Api\\SupportApiController@adminGetCategories',    ['auth', 'admin']);
$router->post('/api/admin/support/categories',       'Api\\SupportApiController@adminCreateCategory',   ['auth', 'admin']);
$router->put('/api/admin/support/categories/{id}',   'Api\\SupportApiController@adminUpdateCategory',   ['auth', 'admin']);
$router->delete('/api/admin/support/categories/{id}','Api\\SupportApiController@adminDeleteCategory',   ['auth', 'admin']);

// Admin — template versioning
// NOTE: /template/version/{id} must be registered before /template/{category_id}
// to avoid the {category_id} param matching the literal "version" segment.
$router->get('/api/admin/support/template/version/{id}',          'Api\\SupportApiController@adminGetTemplateVersion',  ['auth', 'admin']);
$router->get('/api/admin/support/template/{category_id}/history', 'Api\\SupportApiController@adminGetTemplateHistory',  ['auth', 'admin']);
$router->get('/api/admin/support/template/{category_id}',         'Api\\SupportApiController@adminGetTemplate',         ['auth', 'admin']);
$router->post('/api/admin/support/template/{category_id}',        'Api\\SupportApiController@adminSaveTemplate',        ['auth', 'admin']);
