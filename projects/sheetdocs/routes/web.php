<?php
/**
 * SheetDocs Routes
 * 
 * @package MMB\Projects\SheetDocs
 */

use Core\Router;

$router = new Router(false);

// Dashboard
$router->get('/projects/sheetdocs', 'Projects\SheetDocs\Controllers\DashboardController@index');
$router->get('/projects/sheetdocs/dashboard', 'Projects\SheetDocs\Controllers\DashboardController@index');

// Documents
$router->get('/projects/sheetdocs/documents', 'Projects\SheetDocs\Controllers\DocumentController@index');
$router->get('/projects/sheetdocs/documents/new', 'Projects\SheetDocs\Controllers\DocumentController@create');
$router->post('/projects/sheetdocs/documents/store', 'Projects\SheetDocs\Controllers\DocumentController@store');
$router->get('/projects/sheetdocs/documents/{id}', 'Projects\SheetDocs\Controllers\DocumentController@show');
$router->get('/projects/sheetdocs/documents/{id}/edit', 'Projects\SheetDocs\Controllers\DocumentController@edit');
$router->post('/projects/sheetdocs/documents/{id}/update', 'Projects\SheetDocs\Controllers\DocumentController@update');
$router->post('/projects/sheetdocs/documents/{id}/delete', 'Projects\SheetDocs\Controllers\DocumentController@delete');

// Sheets
$router->get('/projects/sheetdocs/sheets', 'Projects\SheetDocs\Controllers\SheetController@index');
$router->get('/projects/sheetdocs/sheets/new', 'Projects\SheetDocs\Controllers\SheetController@create');
$router->post('/projects/sheetdocs/sheets/store', 'Projects\SheetDocs\Controllers\SheetController@store');
$router->get('/projects/sheetdocs/sheets/{id}', 'Projects\SheetDocs\Controllers\SheetController@show');
$router->get('/projects/sheetdocs/sheets/{id}/edit', 'Projects\SheetDocs\Controllers\SheetController@edit');
$router->post('/projects/sheetdocs/sheets/{id}/update', 'Projects\SheetDocs\Controllers\SheetController@update');
$router->post('/projects/sheetdocs/sheets/{id}/delete', 'Projects\SheetDocs\Controllers\SheetController@delete');

// API endpoints for real-time editing
$router->post('/projects/sheetdocs/api/cells/update', 'Projects\SheetDocs\Controllers\ApiController@updateCell');
$router->post('/projects/sheetdocs/api/documents/autosave', 'Projects\SheetDocs\Controllers\ApiController@autosave');

// Sharing
$router->get('/projects/sheetdocs/share/{id}', 'Projects\SheetDocs\Controllers\ShareController@show');
$router->post('/projects/sheetdocs/share/{id}/create', 'Projects\SheetDocs\Controllers\ShareController@create');
$router->post('/projects/sheetdocs/share/{id}/revoke', 'Projects\SheetDocs\Controllers\ShareController@revoke');

// Public access (shared documents)
$router->get('/sd/{token}', 'Projects\SheetDocs\Controllers\PublicController@view');

// Templates
$router->get('/projects/sheetdocs/templates', 'Projects\SheetDocs\Controllers\TemplateController@index');
$router->get('/projects/sheetdocs/templates/{id}', 'Projects\SheetDocs\Controllers\TemplateController@use');

// Subscription & Pricing
$router->get('/projects/sheetdocs/pricing', 'Projects\SheetDocs\Controllers\SubscriptionController@pricing');
$router->post('/projects/sheetdocs/subscription/upgrade', 'Projects\SheetDocs\Controllers\SubscriptionController@upgrade');
$router->post('/projects/sheetdocs/subscription/cancel', 'Projects\SheetDocs\Controllers\SubscriptionController@cancel');

// Export
$router->get('/projects/sheetdocs/export/{id}/{format}', 'Projects\SheetDocs\Controllers\ExportController@export');

// Settings
$router->get('/projects/sheetdocs/settings', 'Projects\SheetDocs\Controllers\SettingsController@index');
$router->post('/projects/sheetdocs/settings/update', 'Projects\SheetDocs\Controllers\SettingsController@update');
