<?php
/**
 * CodeXPro Routes
 * 
 * @package MMB\Projects\CodeXPro
 */

use Core\Router;

$router = new Router(false);

// Dashboard
$router->get('/projects/codexpro', 'Projects\CodeXPro\Controllers\DashboardController@index');
$router->get('/projects/codexpro/dashboard', 'Projects\CodeXPro\Controllers\DashboardController@index');

// Editor - Live Preview
$router->get('/projects/codexpro/editor', 'Projects\CodeXPro\Controllers\EditorController@index');
$router->get('/projects/codexpro/editor/new', 'Projects\CodeXPro\Controllers\EditorController@create');
$router->get('/projects/codexpro/editor/{id}', 'Projects\CodeXPro\Controllers\EditorController@edit');
$router->post('/projects/codexpro/editor/save', 'Projects\CodeXPro\Controllers\EditorController@save');
$router->post('/projects/codexpro/editor/autosave', 'Projects\CodeXPro\Controllers\EditorController@autosave');

// Projects Management
$router->get('/projects/codexpro/projects', 'Projects\CodeXPro\Controllers\ProjectController@index');
$router->post('/projects/codexpro/projects', 'Projects\CodeXPro\Controllers\ProjectController@store');
$router->get('/projects/codexpro/projects/{id}', 'Projects\CodeXPro\Controllers\ProjectController@show');
$router->put('/projects/codexpro/projects/{id}', 'Projects\CodeXPro\Controllers\ProjectController@update');
$router->patch('/projects/codexpro/projects/{id}/quick-update', 'Projects\CodeXPro\Controllers\ProjectController@quickUpdate');
$router->delete('/projects/codexpro/projects/{id}', 'Projects\CodeXPro\Controllers\ProjectController@delete');

// Snippets
$router->get('/projects/codexpro/snippets', 'Projects\CodeXPro\Controllers\SnippetController@index');
$router->post('/projects/codexpro/snippets', 'Projects\CodeXPro\Controllers\SnippetController@store');
$router->get('/projects/codexpro/snippets/{id}', 'Projects\CodeXPro\Controllers\SnippetController@show');
$router->get('/projects/codexpro/snippets/{id}/edit', 'Projects\CodeXPro\Controllers\SnippetController@edit');
$router->put('/projects/codexpro/snippets/{id}', 'Projects\CodeXPro\Controllers\SnippetController@update');
$router->patch('/projects/codexpro/snippets/{id}/quick-update', 'Projects\CodeXPro\Controllers\SnippetController@quickUpdate');
$router->delete('/projects/codexpro/snippets/{id}', 'Projects\CodeXPro\Controllers\SnippetController@delete');

// Templates
$router->get('/projects/codexpro/templates', 'Projects\CodeXPro\Controllers\TemplateController@index');
$router->get('/projects/codexpro/templates/{id}', 'Projects\CodeXPro\Controllers\TemplateController@load');

// Settings
$router->get('/projects/codexpro/settings', 'Projects\CodeXPro\Controllers\SettingsController@index');
$router->post('/projects/codexpro/settings', 'Projects\CodeXPro\Controllers\SettingsController@update');

// API Endpoints for Phase 5 Features
$router->post('/projects/codexpro/api/format', 'Projects\CodeXPro\Controllers\ApiController@format');
$router->post('/projects/codexpro/api/validate', 'Projects\CodeXPro\Controllers\ApiController@validate');
$router->post('/projects/codexpro/api/minify', 'Projects\CodeXPro\Controllers\ApiController@minify');
$router->get('/projects/codexpro/api/starter-templates', 'Projects\CodeXPro\Controllers\ApiController@getStarterTemplates');
$router->get('/projects/codexpro/api/snippets', 'Projects\CodeXPro\Controllers\ApiController@getSnippets');
$router->get('/projects/codexpro/api/snippets/search', 'Projects\CodeXPro\Controllers\ApiController@searchSnippets');
$router->get('/projects/codexpro/api/export/{id}', 'Projects\CodeXPro\Controllers\ApiController@exportProject');
$router->post('/projects/codexpro/api/create-from-template', 'Projects\CodeXPro\Controllers\ApiController@createFromTemplate');

// Dispatch the route
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
