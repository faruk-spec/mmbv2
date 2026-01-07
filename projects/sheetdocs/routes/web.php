<?php
/**
 * SheetDocs Routes
 * 
 * @package MMB\Projects\SheetDocs
 */

use Core\Router;

// Dashboard
Router::get('/projects/sheetdocs', 'Projects\SheetDocs\Controllers\DashboardController@index');
Router::get('/projects/sheetdocs/dashboard', 'Projects\SheetDocs\Controllers\DashboardController@index');

// Documents
Router::get('/projects/sheetdocs/documents', 'Projects\SheetDocs\Controllers\DocumentController@index');
Router::get('/projects/sheetdocs/documents/new', 'Projects\SheetDocs\Controllers\DocumentController@create');
Router::post('/projects/sheetdocs/documents/store', 'Projects\SheetDocs\Controllers\DocumentController@store');
Router::get('/projects/sheetdocs/documents/{id}', 'Projects\SheetDocs\Controllers\DocumentController@show');
Router::get('/projects/sheetdocs/documents/{id}/edit', 'Projects\SheetDocs\Controllers\DocumentController@edit');
Router::post('/projects/sheetdocs/documents/{id}/update', 'Projects\SheetDocs\Controllers\DocumentController@update');
Router::post('/projects/sheetdocs/documents/{id}/delete', 'Projects\SheetDocs\Controllers\DocumentController@delete');

// Sheets
Router::get('/projects/sheetdocs/sheets', 'Projects\SheetDocs\Controllers\SheetController@index');
Router::get('/projects/sheetdocs/sheets/new', 'Projects\SheetDocs\Controllers\SheetController@create');
Router::post('/projects/sheetdocs/sheets/store', 'Projects\SheetDocs\Controllers\SheetController@store');
Router::get('/projects/sheetdocs/sheets/{id}', 'Projects\SheetDocs\Controllers\SheetController@show');
Router::get('/projects/sheetdocs/sheets/{id}/edit', 'Projects\SheetDocs\Controllers\SheetController@edit');
Router::post('/projects/sheetdocs/sheets/{id}/update', 'Projects\SheetDocs\Controllers\SheetController@update');
Router::post('/projects/sheetdocs/sheets/{id}/delete', 'Projects\SheetDocs\Controllers\SheetController@delete');

// API endpoints for real-time editing
Router::post('/projects/sheetdocs/api/cells/update', 'Projects\SheetDocs\Controllers\ApiController@updateCell');
Router::post('/projects/sheetdocs/api/documents/autosave', 'Projects\SheetDocs\Controllers\ApiController@autosave');

// Sharing
Router::get('/projects/sheetdocs/share/{id}', 'Projects\SheetDocs\Controllers\ShareController@show');
Router::post('/projects/sheetdocs/share/{id}/create', 'Projects\SheetDocs\Controllers\ShareController@create');
Router::post('/projects/sheetdocs/share/{id}/revoke', 'Projects\SheetDocs\Controllers\ShareController@revoke');

// Public access (shared documents)
Router::get('/sd/{token}', 'Projects\SheetDocs\Controllers\PublicController@view');

// Templates
Router::get('/projects/sheetdocs/templates', 'Projects\SheetDocs\Controllers\TemplateController@index');
Router::get('/projects/sheetdocs/templates/{id}', 'Projects\SheetDocs\Controllers\TemplateController@use');

// Subscription & Pricing
Router::get('/projects/sheetdocs/pricing', 'Projects\SheetDocs\Controllers\SubscriptionController@pricing');
Router::post('/projects/sheetdocs/subscription/upgrade', 'Projects\SheetDocs\Controllers\SubscriptionController@upgrade');
Router::post('/projects/sheetdocs/subscription/cancel', 'Projects\SheetDocs\Controllers\SubscriptionController@cancel');

// Export
Router::get('/projects/sheetdocs/export/{id}/{format}', 'Projects\SheetDocs\Controllers\ExportController@export');

// Settings
Router::get('/projects/sheetdocs/settings', 'Projects\SheetDocs\Controllers\SettingsController@index');
Router::post('/projects/sheetdocs/settings/update', 'Projects\SheetDocs\Controllers\SettingsController@update');
